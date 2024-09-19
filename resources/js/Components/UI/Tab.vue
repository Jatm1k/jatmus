<script setup>
import { inject, onMounted, computed, ref } from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const registerTab = inject("registerTab");
const activeTab = inject("activeTab");
const tabIndex = ref(0);

// Регистрация вкладки
onMounted(() => {
    tabIndex.value = registerTab(props.label, props.disabled);
});

// Определение, активна ли вкладка
const isActive = computed(() => activeTab.value === tabIndex.value);
</script>
<template>
    <div v-if="isActive" :class="{ active: isActive }">
        <slot></slot>
    </div>
</template>

<style scoped></style>
