<script setup>
import AudioPlayer from "../Components/AudioPlayer.vue";
const props = defineProps(["processedSong"]);
</script>
<template>
    <div class="processed-song" v-if="processedSong">
        <h2>Результат:</h2>
        <AudioPlayer :song="processedSong" />
    </div>
</template>

<style scoped>
.processed-song {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
}
</style>
