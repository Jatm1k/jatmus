<script setup>
import { ref } from "vue";
import Icon from "./Icon.vue";
import MiniLogo from "./MiniLogo.vue";
import Equalizer from "./Equalizer.vue";
import { store } from "../../store";
import axios from "axios";

const isDownloadButtonLoad = ref(false);
const tg = window.Telegram.WebApp;

const props = defineProps({
    song: {
        type: Object,
        required: true,
    },
});

function handlePlay() {
    if (store.currentSong?.id == props.song.id) {
        store.isPlaying = !store.isPlaying;
    } else {
        store.currentSong = props.song;
    }
}

const handleDownload = () => {
    isDownloadButtonLoad.value = true;
    axios
        .post("/send-audio", { url: props.song.processed_path })
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
</script>

<template>
    <div class="song" @click="handlePlay">
        <div class="song__image">
            <Equalizer v-if="song.id == store.currentSong?.id" />
            <MiniLogo v-else />
        </div>
        <div class="song__info">
            <span class="song__filename">{{ song.processed_filename }}</span>
            <span class="song__author">{{ song.user.username ?? "" }}</span>
        </div>
        <button
            @click.stop="handleDownload"
            class="song__button"
            :disabled="isDownloadButtonLoad"
        >
            <Icon name="download" />
        </button>
    </div>
</template>

<style scoped>
.song {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    background-color: var(--bg-color-200);
    border-radius: 8px;
}

.song__info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    min-width: 0;
}

.song__filename {
    width: 100%;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--title-color);
}

.song__author {
    font-size: 10px;
}

.song__button {
    cursor: pointer;
    background-color: transparent;
    border: none;
    font-size: 24px;
    color: var(--text-color);
}

.song__image {
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
