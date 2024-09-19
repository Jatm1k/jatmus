<script setup>
import { ref, provide, inject } from "vue";
import Icon from "./Icon.vue";
import Popup from "./Popup.vue";
const user = inject("user");
const tabs = ref([]);
const activeTab = ref(0);
const showPopup = ref(false);
const showAds = inject("showAds");

function selectTab(index) {
    activeTab.value = index;
}

// Функция для регистрации вкладки
function registerTab(label, disabled = false) {
    tabs.value.push({
        label: label,
        disabled: disabled,
    });

    return tabs.value.length - 1;
}

function showAdsHandler() {
    if (user.value.ad_check_count >= 9) {
        user.value.ad_check_count = 0;
        user.value.is_premium = true;
    } else {
        user.value.ad_check_count += 1;
    }

    showAds();
}

// Предоставление данных вложенным компонентам
provide("registerTab", registerTab);
provide("activeTab", activeTab);
</script>

<template>
    <Popup :show="showPopup" @close="showPopup = false">
        <h2>
            Данный функционал доступен в <span class="premium">Premium</span>
        </h2>
        <img
            src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Telegram-Animated-Emojis/main/Objects/Locked%20With%20Key.webp"
            alt="Locked With Key"
            class="emoji-image"
        />
        Посмотрите рекламу еще
        {{ 10 - user.ad_check_count }} раз чтобы разблокировать
        <button class="button" @click.prevent="showAdsHandler">
            Получить Premium ({{ user.ad_check_count }}/10)
        </button>
    </Popup>
    <div class="tabs">
        <div class="tab-headers">
            <button
                v-for="(tab, index) in tabs"
                :key="index"
                :class="{ active: activeTab === index }"
                @click.prevent="
                    tab.disabled ? (showPopup = true) : selectTab(index)
                "
            >
                {{ tab.label }}
                <Icon name="lock" :solid="true" v-if="tab.disabled" />
            </button>
        </div>
        <div class="tab-content">
            <slot :active-tab="activeTab"></slot>
        </div>
    </div>
</template>

<style scoped>
.tabs {
    width: 100%;
}

.tab-headers {
    display: flex;
}

.tab-headers button {
    font-size: 14px;
    flex: 1;
    text-align: center;
    padding: 8px;
    background-color: transparent;
    outline: none;
    border: none;
    color: var(--text-color);
}

.tab-headers button.active {
    border-radius: 4px 4px 0 0;
    border-top: 4px solid var(--primary-color-100);
    color: var(--primary-color-100);
    background-color: var(--bg-color-300);
}

.tab-content {
    padding: 10px;
    background-color: var(--bg-color-300);
    border-radius: 0px 0px 8px 8px;
}
</style>
