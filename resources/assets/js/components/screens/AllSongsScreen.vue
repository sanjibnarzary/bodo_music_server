<template>
  <section id="songsWrapper">
    <ScreenHeader :layout="headerLayout">
      All Songs
      <ControlsToggle v-model="showingControls"/>

      <template v-slot:thumbnail>
        <ThumbnailStack :thumbnails="thumbnails"/>
      </template>

      <template v-if="totalSongCount" v-slot:meta>
        <span>{{ pluralize(totalSongCount, 'song') }}</span>
        <span>{{ totalDuration }}</span>
      </template>

      <template v-slot:controls>
        <SongListControls
          v-if="totalSongCount && (!isPhone || showingControls)"
          @playAll="playAll"
          @playSelected="playSelected"
        />
      </template>
    </ScreenHeader>

    <SongListSkeleton v-if="showSkeletons"/>
    <SongList
      v-else
      ref="songList"
      @sort="sort"
      @scroll-breakpoint="onScrollBreakpoint"
      @press:enter="onPressEnter"
      @scrolled-to-end="fetchSongs"
    />
  </section>
</template>

<script lang="ts" setup>
import { computed, ref, toRef } from 'vue'
import { logger, pluralize, requireInjection, secondsToHumanReadable } from '@/utils'
import { commonStore, queueStore, songStore } from '@/stores'
import { playbackService } from '@/services'
import { useScreen, useSongList } from '@/composables'
import { MessageToasterKey, RouterKey } from '@/symbols'

import ScreenHeader from '@/components/ui/ScreenHeader.vue'
import SongListSkeleton from '@/components/ui/skeletons/SongListSkeleton.vue'

const totalSongCount = toRef(commonStore.state, 'song_count')
const totalDuration = computed(() => secondsToHumanReadable(commonStore.state.song_length))

const {
  SongList,
  SongListControls,
  ControlsToggle,
  ThumbnailStack,
  headerLayout,
  thumbnails,
  songs,
  songList,
  duration,
  showingControls,
  isPhone,
  onPressEnter,
  playSelected,
  onScrollBreakpoint
} = useSongList(toRef(songStore.state, 'songs'), 'Songs')

const toaster = requireInjection(MessageToasterKey)
const router = requireInjection(RouterKey)

let initialized = false
const loading = ref(false)
let sortField: SongListSortField = 'title' // @todo get from query string
let sortOrder: SortOrder = 'asc'

const page = ref<number | null>(1)
const moreSongsAvailable = computed(() => page.value !== null)
const showSkeletons = computed(() => loading.value && songs.value.length === 0)

const sort = async (field: SongListSortField, order: SortOrder) => {
  page.value = 1
  songStore.state.songs = []
  sortField = field
  sortOrder = order

  await fetchSongs()
}

const fetchSongs = async () => {
  if (!moreSongsAvailable.value || loading.value) return

  loading.value = true

  try {
    page.value = await songStore.paginate(sortField, sortOrder, page.value!)
  } catch (error) {
    logger.error(error)
    toaster.value.error('Failed to load songs.')
  } finally {
    loading.value = false
  }
}

const playAll = async (shuffle: boolean) => {
  if (shuffle) {
    await queueStore.fetchRandom()
  } else {
    await queueStore.fetchInOrder(sortField, sortOrder)
  }

  router.go('queue')
  await playbackService.playFirstInQueue()
}

useScreen('Songs').onScreenActivated(async () => {
  if (!initialized) {
    initialized = true
    await fetchSongs()
  }
})
</script>
