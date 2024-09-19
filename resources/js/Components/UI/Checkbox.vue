<template>
    <div class="toggle-switch" @click="toggleCheckbox">
        <div class="switch" :class="{ checked: isChecked }">
            <div class="knob"></div>
        </div>
        <label>{{ label }}</label>
    </div>
</template>

<script>
import { ref, watch, defineComponent } from "vue";

export default defineComponent({
    name: "Checkbox",
    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },
        label: {
            type: String,
            default: "",
        },
    },
    setup(props, { emit }) {
        const isChecked = ref(props.modelValue);

        // Синхронизация состояния с родительским компонентом
        watch(
            () => props.modelValue,
            (newValue) => {
                isChecked.value = newValue;
            }
        );

        const toggleCheckbox = () => {
            isChecked.value = !isChecked.value;
            emit("update:modelValue", isChecked.value);
            emit("input", isChecked.value); // Вызов события input
        };

        return {
            isChecked,
            toggleCheckbox,
        };
    },
});
</script>

<style scoped>
.toggle-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 16px; /* Увеличен размер текста */
}

.switch {
    width: 35px; /* Увеличено */
    height: 20px; /* Увеличено */
    background-color: var(
        --bg-color-600
    ); /* Цвет фона выключенного состояния */
    border-radius: 50px;
    position: relative;
    transition: background-color 0.2s;
    margin-right: 10px; /* Увеличено */
}

.switch.checked {
    background-color: var(
        --primary-color-100
    ); /* Цвет фона включенного состояния */
}

.knob {
    width: 16px; /* Увеличено */
    height: 16px; /* Увеличено */
    background-color: white; /* Цвет кнопки */
    border-radius: 50%;
    position: absolute;
    top: 2px; /* Увеличено для центрирования */
    left: 2px; /* Увеличено для центрирования */
    transition: transform 0.2s;
}

.switch.checked .knob {
    transform: translateX(
        15px
    ); /* Увеличено перемещение кнопки вправо при включении */
}

label {
    user-select: none; /* Запрет выделения текста */
}
</style>
