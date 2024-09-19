<script setup>
import BalanceIcon from "../Components/UI/BalanceIcon.vue";
import Block from "../Components/UI/Block.vue";
import { store } from "../store";
import Popup from "../Components/UI/Popup.vue";
import Song from "../Components/UI/Song.vue";
import Tabs from "../Components/UI/Tabs.vue";
import Tab from "../Components/UI/Tab.vue";
import RemixPro from "../Components/UI/RemixPro.vue";
import Remix from "../Components/UI/Remix.vue";
import { inject, ref, provide } from "vue";
import axios from "axios";

const user = inject("user");

const tg = window.Telegram.WebApp;

const AdController = window.Adsgram.init({ blockId: "2780" });
const showAdPopup = ref(false);

const props = defineProps(["song"]);

const form = ref({
    song: null,
    song_id: props?.song?.id,
    effect: "speed_up",
    effect_type: "low",
});
const processing = ref(false);
const processedSong = ref(null);

function processAudio() {
    const formData = new FormData();
    if (form.value.song) {
        formData.append("song", form.value.song);
    } else {
        formData.append("song_id", form.value.song_id);
    }
    formData.append("effect", form.value.effect);
    formData.append("effect_type", form.value.effect_type);
    processing.value = true;
    axios
        .post("/process", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        })
        .then((response) => {
            processing.value = false;
            processedSong.value = response.data.song;
            user.value.balance = --user.value.balance;
        })
        .catch((error) => {
            processing.value = false;
            tg.showAlert(error.response.data.message);
        });
}

function showAds() {
    showAdPopup.value = false;
    AdController.show()
        .then((result) => {
            user.value.balance += 2;
            addFiveMinutes();
            tg.showAlert("Награда получена");
        })
        .catch((result) => {
            tg.showAlert("Произошла ошибка. Попробуйте ещё раз позже.");
        });
}

function showAdPopupHandler() {
    showAdPopup.value = !showAdPopup.value;
}

function addFiveMinutes() {
    const now = new Date();

    now.setUTCMinutes(now.getUTCMinutes() + 5);

    user.value.next_ad_view = formatDate(now);
}

function formatDate(date) {
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, "0"); // Месяцы начинаются с 0
    const day = String(date.getUTCDate()).padStart(2, "0");
    const hours = String(date.getUTCHours()).padStart(2, "0");
    const minutes = String(date.getUTCMinutes()).padStart(2, "0");
    const seconds = String(date.getUTCSeconds()).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function uploadSong(event) {
    const file = event.target.files[0];
    form.value.song = file;
    const audioURL = URL.createObjectURL(file);
    store.currentSong = {
        processed_url: audioURL,
        processed_filename: file.name,
        user: user, // Вы можете изменить это по необходимости
    };
}

provide("showAds", showAds);
</script>
<template>
    <Popup :show="showAdPopup" @close="showAdPopup = false">
        <h3>Бесплатный бонус</h3>
        <p>Посмотри рекламу и получи 2 <BalanceIcon /></p>
        <button class="button" @click.prevent="showAds">Забрать бонус</button>
    </Popup>
    <div class="processing" v-if="processing">
        <img
            src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Telegram-Animated-Emojis/main/Objects/Hourglass%20Done.webp"
            alt="Hourglass Done"
            class="emoji-image"
        />
        <span>Обрабатываем ваш трек...</span>
    </div>
    <Block v-else>
        <form @submit.prevent="processAudio" class="form">
            <label class="song-input" :class="{ active: form.song || song }">
                <input
                    type="file"
                    name="song"
                    @input="uploadSong($event)"
                    accept="audio/*"
                />
            </label>
            <div v-if="form.song" class="selected-song">
                Выбран файл: {{ form.song.name }}
            </div>
            <div v-else-if="song" class="selected-song">
                Выбран файл: {{ song.original_filename }}
            </div>
            <Tabs>
                <Tab label="Ремикс">
                    <Remix
                        :form="form"
                        :song="song"
                        :show-ad-popup-handler="showAdPopupHandler"
                    />
                </Tab>
                <Tab label="JM Studio" :disabled="!user.is_premium">
                    <RemixPro
                        :form="form"
                        :song="song"
                        :show-ad-popup-handler="showAdPopupHandler"
                    />
                </Tab>
            </Tabs>
        </form>
    </Block>
    <Block v-if="processedSong && !processing">
        <div class="processed-song">
            <h2>Результат:</h2>
            <Song :song="processedSong" />
        </div>
    </Block>
</template>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.song-input {
    display: flex;
    align-items: center;
    justify-content: center;
    width: clamp(6.25rem, 50vw, 15.625rem);
    height: clamp(6.25rem, 50vw, 15.625rem);
    text-align: center;
    padding: 10px 10px;
    cursor: pointer;
    background-image: url("/assets/img/vinil-logo4.png");
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    color: white;
    border-radius: 100%;
    transition: background-color 0.3s ease;
}

.song-input.active {
    background-image: url("/assets/img/vinil-logo2.png");
    animation: rotate 5s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.song-input input[type="file"] {
    display: none;
}

.selected-song {
    text-align: center;
    font-size: 10px;
    overflow-wrap: break-word;
    word-break: break-all;
}

.processing {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.processed-song {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.button-loader {
    background: linear-gradient(
            to bottom right,
            #bcbcbc00 calc(50% - 40px),
            #ffffff7d 50%,
            #bcbcbc00 calc(50% + 40px)
        )
        bottom right/calc(200% + 80px) calc(200% + 80px);
    animation: ct8 3s infinite;
}

@keyframes ct8 {
    100% {
        background-position: top left;
    }
}
</style>
