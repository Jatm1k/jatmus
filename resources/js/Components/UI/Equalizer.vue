<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import { store } from "../../store";
const bars = ref([
    { height: 30 },
    { height: 50 },
    { height: 70 },
    { height: 40 },
    { height: 60 },
]);

let intervalId;

function animateBars() {
    intervalId = setInterval(() => {
        if (store.isPlaying) {
            bars.value = bars.value.map((bar) => ({
                height: Math.floor(Math.random() * 100),
            }));
        }
    }, 200);
}

function stopAnimation() {
    clearInterval(intervalId);
    bars.value = [
        { height: 10 },
        { height: 10 },
        { height: 10 },
        { height: 10 },
        { height: 10 },
    ];
}

onMounted(() => {
    animateBars();
});
onUnmounted(() => {
    stopAnimation();
});

watch(
    () => store.isPlaying,
    (newValue) => {
        if (newValue) {
            animateBars();
        } else {
            stopAnimation();
        }
    }
);
</script>
<template>
    <div class="equalizer">
        <div
            class="bar"
            v-for="(bar, index) in bars"
            :key="index"
            :style="{ height: bar.height + '%' }"
        ></div>
    </div>
</template>

<style scoped>
.equalizer {
    display: flex;
    justify-content: space-around;
    align-items: center;
    height: 30px;
    width: 100%;
}

.bar {
    width: 3px;
    background-color: var(--title-color);
    transition: height 0.4s ease;
    border-radius: 8px;
}
</style>
