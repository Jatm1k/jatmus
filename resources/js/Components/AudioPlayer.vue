<script setup>
import { ref, onMounted, watch } from "vue";
import Icon from "./UI/Icon.vue";
import axios from "axios";

const props = defineProps({
    song: {
        type: Object,
        required: true,
    },
});
const tg = window.Telegram.WebApp;
const audioPlayer = ref(null);
const isPlaying = ref(false);
const duration = ref(0);
const currentTime = ref(0);
const isDownloadButtonLoad = ref(false);
const sendAudio = (url) => {
    isDownloadButtonLoad.value = true;
    axios
        .post("/send-audio", { url })
        .then((response) => {
            tg.showAlert(response.data.message);
        })
        .catch((error) => {
            tg.showAlert(error.response.data.message);
        });
    isDownloadButtonLoad.value = false;
};

const togglePlay = () => {
    if (isPlaying.value) {
        audioPlayer.value.pause();
    } else {
        audioPlayer.value.play();
    }
    isPlaying.value = !isPlaying.value;
};

const onTimeUpdate = () => {
    currentTime.value = audioPlayer.value.currentTime;
};

const onLoadedMetadata = () => {
    duration.value = audioPlayer.value.duration;
};

const seek = (event) => {
    audioPlayer.value.currentTime = event.target.value;
};

const formatTime = (time) => {
    const minutes = Math.floor(time / 60);
    const seconds = Math.floor(time % 60);
    return `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
};

onMounted(() => {
    audioPlayer.value.addEventListener("timeupdate", onTimeUpdate);
    audioPlayer.value.addEventListener("loadedmetadata", onLoadedMetadata);
});

watch(
    () => props.url,
    () => {
        isPlaying.value = false;
        currentTime.value = 0;
        duration.value = 0;
    }
);
</script>

<template>
    <div class="player">
        <audio
            ref="audioPlayer"
            :src="song.processed_url"
            @timeupdate="onTimeUpdate"
            @loadedmetadata="onLoadedMetadata"
        ></audio>
        <div class="player__filename-wrapper">
            <span class="player__filename">{{ song.processed_filename }}</span>
        </div>
        <span class="player__timer"
            >{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</span
        >
        <div class="player__controls">
            <button @click="togglePlay" class="player__button">
                <Icon :name="isPlaying ? 'pause' : 'play'" />
            </button>
            <input
                type="range"
                :min="0"
                :max="duration"
                :value="currentTime"
                @input="seek"
            />
            <button
                @click="sendAudio(song.processed_path)"
                class="player__button"
                :disabled="isDownloadButtonLoad"
            >
                <Icon name="download" />
            </button>
        </div>
    </div>
</template>

<style>
.player {
    width: 100%;
    padding: 20px;
    /* border: 1px solid #ccc; */
    /* background-color: var(--bg-color-300); */
    border-radius: 5px;
}

.player__filename,
.player__timer {
    display: inline-block;
    width: 100%;
    margin-bottom: 10px;
    text-align: center;
    overflow-wrap: break-word;
    word-break: break-all;
    font-size: 10px;
}

.player__controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

input[type="range"] {
    /* removing default appearance */
    -webkit-appearance: none;
    appearance: none;
    /* creating a custom design */
    width: 100%;
    cursor: pointer;
    outline: none;
    /*  slider progress trick  */
    overflow: hidden;
    border-radius: 16px;
}

/* Track: webkit browsers */
input[type="range"]::-webkit-slider-runnable-track {
    height: 15px;
    background: #ccc;
    border-radius: 16px;
}

/* Track: Mozilla Firefox */
input[type="range"]::-moz-range-track {
    height: 15px;
    background: #ccc;
    border-radius: 16px;
}

/* Thumb: webkit */
input[type="range"]::-webkit-slider-thumb {
    /* removing default appearance */
    -webkit-appearance: none;
    appearance: none;
    /* creating a custom design */
    height: 15px;
    width: 15px;
    background-color: #fff;
    border-radius: 50%;
    border: 2px solid var(--primary-color-100);
    /*  slider progress trick  */
    box-shadow: -407px 0 0 400px var(--primary-color-100);
}

/* Thumb: Firefox */
input[type="range"]::-moz-range-thumb {
    height: 15px;
    width: 15px;
    background-color: #fff;
    border-radius: 50%;
    border: 1px solid var(--primary-color-100);
    /*  slider progress trick  */
    box-shadow: -407px 0 0 400px var(--primary-color-100);
}

.player__button {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text-color);
    background-color: transparent;
    border: none;
}
</style>
