<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps(["time"]);
const emit = defineEmits();
const timeRemaining = ref(0);
const timer = ref(null);
const isDisabled = computed(() => {
    return timeRemaining.value > 0;
});
const formattedTime = computed(() => {
    const minutes = Math.floor(timeRemaining.value / 60);
    const seconds = timeRemaining.value % 60;
    return `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
});

function startTimer() {
    const targetTime = Date.parse(props.time) / 1000;
    const now = Math.floor(Date.now() / 1000);
    timeRemaining.value = Math.max(0, targetTime - now);

    if (timeRemaining.value > 0) {
        timer.value = setInterval(() => {
            timeRemaining.value = Math.max(0, timeRemaining.value - 1);
            if (timeRemaining.value === 0) {
                clearInterval(timer.value);
            }
        }, 1000);
    }
}

onMounted(() => {
    startTimer();
});

onBeforeUnmount(() => {
    clearInterval(timer.value);
});

watch(
    () => props.time,
    (newTime) => {
        startTimer();
    }
);
</script>

<template>
    <button class="button ads-button" :disabled="isDisabled">
        <template v-if="isDisabled">
            {{ formattedTime }}
        </template>
        <slot v-else></slot>
    </button>
</template>

<style scoped></style>
