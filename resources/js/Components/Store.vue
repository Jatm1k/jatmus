<script setup>
import Popup from "./UI/Popup.vue";
import BalanceIcon from "./UI/BalanceIcon.vue";
import Icon from "./UI/Icon.vue";
import axios from "axios";
import { inject, reactive, ref } from "vue";

const tg = window.Telegram.WebApp;

const starItems = reactive([
    { amount: 1, price: 1, isLoading: false },
    { amount: 10, price: 9, isLoading: false },
    { amount: 50, price: 40, isLoading: false },
    { amount: 100, price: 70, isLoading: false },
    { amount: 150, price: 100, isLoading: false },
    { amount: 500, price: 200, isLoading: false },
]);
const premiumItems = reactive([
    {
        title: "Премиум на месяц",
        price: 100,
        duration: "month",
        isLoading: false,
    },
    { title: "Премиум на год", price: 500, duration: "year", isLoading: false },
]);
const user = inject("user");
function buy(item, index) {
    starItems[index].isLoading = true;

    axios
        .post("/buy", {
            amount: item.amount,
            price: item.price,
        })
        .then((res) => {
            const invoiceLink = res.data.link;
            tg.openInvoice(invoiceLink, (status) => {
                if (status === "paid") {
                    axios
                        .post("/add-balance", {
                            amount: item.amount,
                        })
                        .then((res) => {
                            user.value.balance = res.data.balance;
                        });
                }
            });
        })
        .finally(() => {
            starItems[index].isLoading = false;
        });
}

function buyPremium(item, index) {
    premiumItems[index].isLoading = true;
    console.log(premiumItems[index].isLoading);

    axios
        .post("/buy-premium", {
            duration: item.duration,
            price: item.price,
        })
        .then((res) => {
            user.value = res.data.user;
        })
        .catch((error) => {
            tg.showAlert(error.response.data.message);
        })
        .finally(() => {
            premiumItems[index].isLoading = false;
        });
}
</script>
<template>
    <div class="store">
        <h2 class="store__title">Магазин</h2>
        <div class="store__items">
            <div
                class="store__item"
                v-for="(item, index) in starItems"
                :key="index"
            >
                <span class="store__item-title"
                    >{{ item.amount }} <BalanceIcon
                /></span>
                <button
                    class="store__item-price_star"
                    :class="{ 'store__item-price_loading': item.isLoading }"
                    @click="buy(item, index)"
                    :disabled="item.isLoading"
                >
                    {{ item.price }} <Icon name="star" :solid="true" />
                </button>
            </div>
        </div>
        <hr />
        <div class="store__items">
            <div
                class="store__item"
                v-for="(item, index) in premiumItems"
                :key="item.title"
            >
                <span class="store__item-title">{{ item.title }}</span>
                <button
                    class="store__item-price"
                    :class="{ 'store__item-price_loading': item.isLoading }"
                    @click="buyPremium(item, index)"
                    :disabled="item.isLoading"
                >
                    {{ item.price }} <BalanceIcon />
                </button>
            </div>
        </div>

        <p class="store__premium-description">
            Что даёт премиум:
            <br />
            - создание ремиксов не требует <BalanceIcon />
            <br />
            - доступ к JM Studio(позволяет добавлять эффекты в реальном времени)
            <br />
            - отключает всю рекламу
        </p>
    </div>
</template>

<style scoped>
.store__items {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    justify-content: center;
}
hr {
    border: none;
    border-bottom: 1px solid var(--border-color);
    margin: 16px 0;
}
.store__item {
    display: flex;
    flex: 1 1 100px;
    align-items: center;
    flex-direction: column;
    justify-content: space-between;
    gap: 8px;
    background-color: var(--bg-color-300);
    padding: 10px;
    border-radius: 12px;
}
.store__item-price {
    background-color: var(--bg-color-200);
    outline: none;
    border: none;
    color: var(--text-color);
    padding: 8px 24px;
    border-radius: 8px;
}

.store__item-price_star {
    background-color: var(--bg-color-200);
    outline: none;
    border: none;
    color: var(--premium-color);
    padding: 8px 24px;
    border-radius: 8px;
}

.store__premium-description {
    color: var(--text-color);
    text-align: center;
    font-size: 12px;
    margin-top: 16px;
}

.store__item-price_loading {
    background: linear-gradient(
            to bottom right,
            #0000 calc(50% - 40px),
            var(--bg-color-400) 50%,
            #0000 calc(50% + 40px)
        )
        bottom right/calc(200% + 80px) calc(200% + 80px) var(--bg-color-200);
    animation: ct8 1.5s infinite;
}
@keyframes ct8 {
    100% {
        background-position: top left;
    }
}
</style>
