<script setup>
import { ref, inject } from "vue";
import Buttons from "./Buttons.vue";
import BalanceIcon from "./BalanceIcon.vue";
import TimerButton from "./TimerButton.vue";
import { toRefs } from "vue";

const user = inject("user");

// Определяем пропсы, которые принимает компонент
const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    song: {
        Object,
    },
    showAdPopupHandler: {
        type: Function,
        required: true,
    },
});

// Используем toRefs для создания реактивных ссылок на свойства объекта
const { form } = toRefs(props);

const effects = [
    { value: "speed_up", title: "Speed up" },
    { value: "slowed", title: "Slowed" },
    { value: "8d", title: "8D" },
    { value: "bass", title: "Bass" },
];

const effectTypes = [
    { value: "low", title: "Слабый" },
    { value: "medium", title: "Средний" },
    { value: "hard", title: "Сильный" },
];
</script>

<template>
    <div class="remix-form">
        <Buttons :elems="effects" name="effect" v-model="form.effect" />
        <Buttons
            :elems="effectTypes"
            name="effect_type"
            v-model="form.effect_type"
        />
        <div class="buttons-wrapper">
            <button
                type="submit"
                class="button form-button"
                :disabled="(!form.song && !song) || user.balance <= 0"
            >
                <template v-if="user.balance > 0">
                    Создать ремикс! - 1
                    <BalanceIcon />
                </template>
                <template v-else
                    >Недостаточно <BalanceIcon /> на балансе</template
                >
            </button>
            <TimerButton
                class="button ads-button"
                @click.prevent="showAdPopupHandler"
                :time="user.next_ad_view"
            >
                +2 <BalanceIcon />
            </TimerButton>
        </div>
    </div>
</template>

<style scoped>
.remix-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
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
