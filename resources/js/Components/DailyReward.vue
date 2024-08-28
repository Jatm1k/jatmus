<script setup>
import Popup from "./UI/Popup.vue";
import BalanceIcon from "./UI/BalanceIcon.vue";
import axios from "axios";
import { inject, ref } from "vue";

const showPopup = ref(false);
const reward = ref(null);
const user = inject("user");
function getDailyReward() {
    axios.post("/daily-reward").then((response) => {
        if (response.data.status) {
            showPopup.value = true;
            reward.value = response.data.reward;
            user.value.balance += response.data.reward.amount;
        }
    });
}

getDailyReward();

function closePopup() {
    showPopup.value = false;
}
</script>
<template>
    <Popup :show="showPopup">
        <h3>Ежедневная награда</h3>
        <img
            src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Telegram-Animated-Emojis/main/Activity/Confetti%20Ball.webp"
            alt="Confetti Ball"
            class="emoji-image"
        />
        <button class="button" @click="closePopup">
            Получить {{ reward.amount }} <BalanceIcon />
        </button>
    </Popup>
</template>

<style scoped></style>
