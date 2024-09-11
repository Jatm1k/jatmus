<script setup>
import { ref, onMounted, watch } from "vue";
import Icon from "./UI/Icon.vue";
import axios from "axios";
import { store } from "../store";
import MiniLogo from "./UI/MiniLogo.vue";

const tg = window.Telegram.WebApp;
const audioPlayer = ref(null);
const isPlaying = ref(false);
const duration = ref(0);
const currentTime = ref(0);
const isDownloadButtonLoad = ref(false);

const sendAudio = () => {
    isDownloadButtonLoad.value = true;
    axios
        .post("/send-audio", { url: store.currentSong.processed_path })
        .then((response) => {
            tg.showAlert(response.data.message);
        })
        .catch((error) => {
            tg.showAlert(error.response.data.message);
        })
        .finally(() => {
            isDownloadButtonLoad.value = false;
        });
};

const togglePlay = () => {
    if (store.isPlaying) {
        audioPlayer.value.pause();
    } else {
        audioPlayer.value.play();
    }
    store.isPlaying = !store.isPlaying;
};

const play = () => {
    store.isPlaying = true;

    // audioPlayer.value.pause();
    audioPlayer.value.load();
    audioPlayer.value.addEventListener(
        "canplay",
        () => {
            audioPlayer.value.play().catch((error) => {
                console.error("Playback error:", error);
            });
        },
        { once: true }
    );
};

const onTimeUpdate = () => {
    currentTime.value = audioPlayer.value.currentTime;
};

const onLoadedMetadata = () => {
    duration.value = audioPlayer.value.duration;
};

const seek = (event) => {
    const progressBar = event.currentTarget;
    const clickPosition =
        event.clientX - progressBar.getBoundingClientRect().left;
    const newTime = (clickPosition / progressBar.clientWidth) * duration.value;
    audioPlayer.value.currentTime = newTime;
};

onMounted(() => {
    audioPlayer.value.addEventListener("timeupdate", onTimeUpdate);
    audioPlayer.value.addEventListener("loadedmetadata", onLoadedMetadata);
    play();
});

watch(
    () => store.currentSong,
    () => {
        play();
    }
);
watch(
    () => store.isPlaying,
    () => {
        if (store.isPlaying) {
            audioPlayer.value.play();
        } else {
            audioPlayer.value.pause();
        }
    }
);
</script>

<template>
    <div class="progress-bar" @click="seek">
        <div
            class="progress-bar__fill"
            :style="{ width: (currentTime / duration) * 100 + '%' }"
        ></div>
    </div>
    <div class="player">
        <div class="player__image">
            <MiniLogo />
        </div>
        <audio
            ref="audioPlayer"
            :src="store.currentSong?.processed_url"
            @timeupdate="onTimeUpdate"
            @loadedmetadata="onLoadedMetadata"
        ></audio>
        <div class="player__info">
            <span class="player__filename">{{
                store.currentSong?.processed_filename
            }}</span>
            <span class="player__author">{{
                store.currentSong?.user.username ?? ""
            }}</span>
        </div>
        <div class="player__controls">
            <button @click="togglePlay" class="player__button">
                <Icon :name="store.isPlaying ? 'pause' : 'play'" />
            </button>

            <button
                @click="sendAudio"
                class="player__button"
                :disabled="isDownloadButtonLoad"
            >
                <Icon name="download" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.player {
    display: flex;
    align-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    border-radius: 5px;
}

.player__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.player__filename {
    width: 100%;
    font-size: 12px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    color: var(--title-color);
}

.player__author {
    font-size: 10px;
}

.player__controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.progress-bar {
    position: relative;
    width: 100%;
    height: 2px;
    background-color: var(--bg-color-300);
    /* border-radius: 5px; */
    cursor: pointer;
}

.progress-bar__fill {
    position: absolute;
    height: 100%;
    background-color: var(--primary-color-100);
    border-radius: 5px;
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

.player__image {
    display: flex;
    align-items: center;
    justify-self: center;
    background-color: var(--bg-color-300);
    padding: 8px;
    border-radius: 8px;
    width: 40px;
    height: 40px;
}
</style>
