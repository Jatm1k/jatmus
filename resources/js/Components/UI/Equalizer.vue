<script setup>
import { onMounted, ref } from "vue";

const bars = ref([
    { height: 30 },
    { height: 50 },
    { height: 70 },
    { height: 40 },
    { height: 60 },
]);

function animateBars() {
    setInterval(() => {
        bars.value = bars.value.map((bar) => ({
            height: Math.floor(Math.random() * 100),
        }));
    }, 200);
}

onMounted(() => {
    animateBars();
});
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
    align-items: flex-end;
    height: 30px;
    width: 100%;
}

.bar {
    width: 3px;
    background-color: var(--title-color);
    transition: height 0.3s ease;
}
</style>
