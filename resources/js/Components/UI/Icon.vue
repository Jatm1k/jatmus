<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    name: String,
    solid: Boolean,
});

const result = computed(() => {
    return props.solid ? `bxs-${props.name}` : `bx-${props.name}`;
});
</script>
<template>
    <i class="bx" :class="result"></i>
</template>

<style scoped></style>
