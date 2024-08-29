<script setup>
import { Link } from "@inertiajs/vue3";
import Icon from "../Components/UI/Icon.vue";
import BalanceIcon from "../Components/UI/BalanceIcon.vue";
import Logo from "../Components/UI/Logo.vue";
import DailyReward from "../Components/DailyReward.vue";
import { provide, ref } from "vue";
import axios from "axios";

const routes = [
    { name: "profile", icon: "user" },
    { name: "home", icon: "plus-circle" },
    { name: "feed", icon: "music" },
];

const loadApp = ref(true);

const tg = window.Telegram.WebApp;
tg.expand();

const tgUser = tg.initDataUnsafe.user;

const user = ref(null);
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
        <div class="loader"></div>
    </div>
    <div class="container" v-else>
        <DailyReward v-if="user" />
        <div class="header">
            <a href="https://t.me/jatmusic">
                <Logo />
            </a>
            <span class="balance">
                {{ user ? user.balance : 0 }} <BalanceIcon />
            </span>
        </div>
        <div class="content">
            <slot />
        </div>
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

.content {
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

.balance {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 6px;
    border-radius: 8px;
    color: var(--title-color);
}

.footer {
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 12px 16px;
    border-top: 1px solid var(--border-color);
}

.footer__link {
    font-size: 26px;
}

.footer__link.active {
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
