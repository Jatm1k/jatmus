<script setup>
import Playlist from "../Components/UI/Playlist.vue";

const props = defineProps(["songs"]);
</script>
<template>
    <h2>Плейлист дня</h2>
    <Playlist :songs="songs" />
</template>

<style scoped>
.playlist {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
</style>
