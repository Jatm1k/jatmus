<script setup></script>
<template>
    <div class="block">
        <slot />
    </div>
</template>

<style scoped>
.block {
    background-color: var(--bg-color-200);
    padding: 16px;
    border-radius: 24px;
    margin-bottom: 16px;
}
</style>
