<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    elems: Array,
    name: String,
    modelValue: String,
});

const emit = defineEmits(["update:modelValue"]);

const selectedValue = ref(props.modelValue);

watch(selectedValue, (newValue) => {
    emit("update:modelValue", newValue);
});

const onChange = (value) => {
    selectedValue.value = value;
};
</script>

<template>
    <div class="wrapper">
        <label
            :for="elem.value"
            class="radio-button"
            v-for="(elem, index) in elems"
            :key="index"
            :class="{ active: selectedValue === elem.value }"
        >
            <input
                :id="elem.value"
                type="radio"
                :name="name"
                :value="elem.value"
                @change="onChange(elem.value)"
                v-model="selectedValue"
            />
            {{ elem.title }}
        </label>
    </div>
</template>

<style scoped>
.wrapper {
    border: 1px solid var(--border-color);
    padding: 8px 8px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    width: 100%;
    flex-wrap: wrap;
}
.radio-button {
    flex: 1;
    cursor: pointer;
    text-align: center;
    border-radius: 4px;
    padding: 4px 3px;
    font-size: 12px;
}
.radio-button.active {
    transition: all 1s;
    background-color: var(--mixed-color-100);
}

.radio-button input {
    display: none;
}
</style>
