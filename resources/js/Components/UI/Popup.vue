<script setup>
const props = defineProps({
    show: Boolean,
});
</script>
<template>
    <div class="popup-bg" v-if="show">
        <div class="popup">
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
    align-items: end;
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
}
</style>
