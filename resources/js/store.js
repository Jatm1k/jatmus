import { reactive } from 'vue'

export const store = reactive({
    currentSong: null,
    isPlaying: false,
    songType: 'remix',
    playbackRate: 1,
    pitchShift: 0,
    bass: 0,
    stereoEffect: false,
})
