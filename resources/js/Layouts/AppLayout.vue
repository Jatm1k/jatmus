<script setup>
import { Link } from "@inertiajs/vue3";
import Icon from "../Components/UI/Icon.vue";
import BalanceIcon from "../Components/UI/BalanceIcon.vue";
import Logo from "../Components/UI/Logo.vue";
import { provide, ref } from "vue";
import axios from "axios";
import NewNewAudioPlayer from "../Components/NewNewAudioPlayer.vue";
import NewAudioPlayer from "../Components/NewAudioPlayer.vue";
import { store } from "../store";
import Popup from "../Components/UI/Popup.vue";
import Store from "../Components/Store.vue";

const routes = [
    { name: "profile", icon: "user" },
    { name: "home", icon: "plus-circle" },
    { name: "feed", icon: "music" },
];

const loadApp = ref(true);
const showStore = ref(false);

const tg = window.Telegram.WebApp;
console.log(tg);

tg.expand();

const tgUser = tg.initDataUnsafe.user;

const user = ref(null);

function openStore() {
    showStore.value = true;
}

function auth() {
    axios
        .post("/auth/check")
        .then((res) => {
            if (res.data.auth) {
                user.value = res.data.user;
            } else {
                axios
                    .post("/auth/login", tgUser)
                    .then((res) => {
                        user.value = res.data.user;
                    })
                    .catch((error) => {
                        tg.showAlert(error.response.data.message);
                    });
            }
            loadApp.value = false;
        })
        .catch((error) => {
            tg.showAlert(error.response.data.message);
        });
}

auth();

provide("user", user);
</script>
<template>
    <div class="loader-container" v-if="loadApp || !user">
        <span class="loader-text" v-if="!tg.initData">
            Приложение доступно только в
            <a class="link" href="https://t.me/jatmusbot?start">
                Telegram боте
            </a>
        </span>
        <div class="loader" v-else></div>
    </div>
    <div class="container" v-else>
        <Popup :show="showStore" @close="showStore = false">
            <Store />
        </Popup>
        <div class="header">
            <a href="https://t.me/jatmusic">
                <Logo />
            </a>
            <div class="header-right">
                <span class="balance">
                    {{ user ? user.balance : 0 }} <BalanceIcon />
                </span>
                <a @click="openStore" class="store-button">
                    <Icon name="store" :solid="true" />
                </a>
            </div>
        </div>
        <div class="app-content">
            <slot />
        </div>
        <NewNewAudioPlayer v-if="store.currentSong && store.hasEffects()" />
        <NewAudioPlayer v-if="store.currentSong && !store.hasEffects()" />
        <div class="footer">
            <Link
                :href="route(r.name)"
                class="footer__link"
                :class="{ active: r.name === route().current() }"
                v-for="r in routes"
                :key="r.name"
            >
                <Icon :name="r.icon" :solid="r.name === route().current()" />
            </Link>
        </div>
    </div>
</template>
<style scoped>
.container,
.loader-container {
    max-width: 500px;
    margin: 0 auto;
    /* border: 1px solid var(--border-color); */
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.loader-container {
    justify-content: center;
    align-items: center;
}

.app-content {
    flex: 1;
    padding: 32px 16px;
    overflow-y: auto;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 16px;
}

.header__logo {
    width: clamp(3.125rem, 0.982rem + 22.86vw, 8.125rem);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-right: 16px;
}

.balance {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 6px;
    border-radius: 8px;
    color: var(--title-color);
}

.store-button {
    padding: 4px;
    border-radius: 8px;
    color: var(--title-color);
    font-size: 16px;
    cursor: pointer;
}

.footer {
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 0px 16px;
    gap: 32px;
    /* border-top: 1px solid var(--border-color); */
}

.footer__link {
    font-size: 26px;
    flex: 1;
    text-align: center;
    padding: 8px;
    border-radius: 4px;
    /* border-top: 5px solid var(--border-color); */
}

.footer__link.active {
    border-top: 5px solid var(--primary-color-100);
    color: var(--primary-color-100);
}

.loader {
    width: 52px;
    height: 12px;
    --c: radial-gradient(farthest-side, var(--mixed-color-100) 90%, #0000);
    background: var(--c) left, var(--c) right;
    background-size: 12px 12px;
    background-repeat: no-repeat;
    display: grid;
}
.loader:before,
.loader:after {
    content: "";
    width: 12px;
    height: 12px;
    grid-area: 1/1;
    margin: auto;
    border-radius: 50%;
    transform-origin: -12px 50%;
    background: var(--primary-color-100);
    animation: d9 1s infinite linear;
}
.loader:after {
    transform-origin: calc(100% + 12px) 50%;
    --s: -1;
    animation-delay: -0.5s;
}

@keyframes d9 {
    58%,
    100% {
        transform: rotate(calc(var(--s, 1) * 1turn));
    }
}
</style>
