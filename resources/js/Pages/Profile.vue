<script setup>
import Playlist from "../Components/UI/Playlist.vue";

const props = defineProps(["songs"]);
</script>
<template>
    <h2>Твои треки</h2>
    <Playlist :songs="songs" />
</template>

<style scoped></style>
