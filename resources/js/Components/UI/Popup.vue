<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(["close"]);

// Функция для закрытия popup
const closePopup = () => {
    emit("close");
};
</script>

<template>
    <div class="popup-bg" v-if="show" @click="closePopup">
        <div class="popup" @click.stop>
            <slot />
        </div>
    </div>
</template>

<style scoped>
.popup-bg {
    position: fixed;
    inset: 0;
    /* blur */
    backdrop-filter: blur(5px);
    z-index: 99;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.popup {
    flex: 1;
    padding: 16px;
    border-radius: 8px;
    background-color: var(--bg-color-200);
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    text-align: center;
}
</style>
