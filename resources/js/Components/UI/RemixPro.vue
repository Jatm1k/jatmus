<script setup>
import { ref, watch, inject } from "vue";
import { store } from "../../store";
import BalanceIcon from "./BalanceIcon.vue";
import TimerButton from "./TimerButton.vue";
import Checkbox from "./Checkbox.vue";
import axios from "axios";

const user = inject("user");
const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    song: {
        type: Object,
        default: null,
    },
    showAdPopupHandler: {
        type: Function,
        required: true,
    },
});

const playbackRate = ref(1);
const pitchShift = ref(0);
const bass = ref(0);
const stereoEffect = ref(false);
const isLinked = ref(false); // Новое состояние для чекбокса

function updatePlaybackRate() {
    if (isLinked.value) {
        // При изменении темпа, если чекбокс активен, меняем и тональность
        pitchShift.value = (playbackRate.value - 1) * 12;
        store.pitchShift = pitchShift.value;
    }
    store.playbackRate = playbackRate.value;
}

function updatePitch() {
    if (isLinked.value) {
        // При изменении тональности, если чекбокс активен, меняем и темп
        playbackRate.value = 1 + pitchShift.value / 12;
        store.playbackRate = playbackRate.value;
    }
    store.pitchShift = pitchShift.value;
}

function updateBass() {
    store.bass = bass.value;
}

function updateStereoEffect() {
    store.stereoEffect = stereoEffect.value;
}
</script>

<template>
    <div class="remix-form">
        <div class="remix-parametr">
            <label>Темп: {{ playbackRate }}</label>
            <input
                type="range"
                min="0.5"
                max="2"
                step="0.01"
                v-model="playbackRate"
                @input="updatePlaybackRate"
            />
        </div>
        <div class="remix-parametr">
            <label>Тональность: {{ pitchShift }}</label>
            <input
                type="range"
                min="-12"
                max="12"
                step="1"
                v-model="pitchShift"
                @input="updatePitch"
            />
        </div>
        <div class="remix-parametr">
            <label>Басы: {{ bass }}</label>
            <input
                type="range"
                min="-30"
                max="30"
                step="1"
                v-model="bass"
                @input="updateBass"
            />
        </div>
        <div class="remix-parametr">
            <Checkbox v-model="isLinked" label="Связать тональность с темпом" />
        </div>
        <div class="remix-parametr">
            <Checkbox
                v-model="stereoEffect"
                @input="updateStereoEffect"
                label="8D эффект"
            />
        </div>
    </div>
</template>

<style scoped>
.remix-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    cursor: pointer;
    outline: none;
    overflow: hidden;
    border-radius: 16px;
}

input[type="range"]::-webkit-slider-runnable-track {
    height: 15px;
    background: #ccc;
    border-radius: 16px;
}

input[type="range"]::-moz-range-track {
    height: 15px;
    background: #ccc;
    border-radius: 16px;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    height: 15px;
    width: 15px;
    background-color: #fff;
    border-radius: 50%;
    border: 2px solid var(--primary-color-100);
    box-shadow: -407px 0 0 400px var(--primary-color-100);
}

input[type="range"]::-moz-range-thumb {
    height: 15px;
    width: 15px;
    background-color: #fff;
    border-radius: 50%;
    border: 1px solid var(--primary-color-100);
    box-shadow: -407px 0 0 400px var(--primary-color-100);
}

.buttons-wrapper {
    width: 100%;
    display: flex;
    gap: 8px;
}

.buttons-wrapper .form-button {
    flex: 8;
}

.buttons-wrapper .ads-button {
    flex: 2;
}
</style>
