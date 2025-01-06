<script setup>
import { defineProps, defineEmits } from "vue";
import Icon from "./Icon.vue";
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
            <button class="popup__close" @click="closePopup">
                <Icon name="x" />
            </button>
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
    position: relative; /* Новый стиль */
    flex: 1;
    padding: 16px;
    border-radius: 8px;
    background-color: var(--bg-color-200);
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    text-align: center;
    max-height: 400px;
    overflow-y: auto;
}

.popup__close {
    position: absolute; /* Новый стиль */
    top: 8px; /* Отступ сверху */
    right: 8px; /* Отступ справа */
    cursor: pointer;
    background-color: transparent;
    border: none;
    font-size: 24px;
    color: var(--bg-color-400);
}
</style>
