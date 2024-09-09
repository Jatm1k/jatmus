<script setup>
import AudioPlayer from "../AudioPlayer.vue";
import Song from "./Song.vue";
const props = defineProps({
    songs: Array,
});
</script>
<template>
    <div class="playlist" v-if="songs.length > 0">
        <song v-for="song in songs" :song="song" :key="song.id" />
    </div>
    <div class="playlist-empty" v-else>
        <img
            src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Telegram-Animated-Emojis/main/Smileys/Crying%20Face.webp"
            alt="Crying Face"
            class="emoji-image"
        />
        Плейлист пуст
    </div>
</template>

<style scoped>
.playlist {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.playlist-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
</style>
