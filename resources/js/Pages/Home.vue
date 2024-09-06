<script setup>
import { useForm } from "@inertiajs/vue3";
import AudioPlayer from "../Components/AudioPlayer.vue";
import Buttons from "../Components/UI/Buttons.vue";
import BalanceIcon from "../Components/UI/BalanceIcon.vue";
import Block from "../Components/UI/Block.vue";
import { inject, ref } from "vue";
import axios from "axios";

const user = inject("user");

const tg = window.Telegram.WebApp;

const AdController = window.Adsgram.init({ blockId: "2780" });

const props = defineProps(["song"]);

const effects = [
    { value: "speed_up", title: "Speed up" },
    { value: "slowed", title: "Slowed" },
    { value: "8d", title: "8D" },
    { value: "bass", title: "Bass" },
];

const form = ref({
    song: null,
    song_id: props?.song?.id,
    effect: "speed_up",
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
    AdController.show()
        .then((result) => {
            user.value.balance += 2;
            tg.showAlert("Награда получена");
        })
        .catch((result) => {
            tg.showAlert("Произошла ошибка. Попробуйте ещё раз позже.");
        });
}
</script>
<template>
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
                    @input="form.song = $event.target.files[0]"
                    accept="audio/*"
                />
            </label>
            <div v-if="form.song" class="selected-song">
                Выбран файл: {{ form.song.name }}
            </div>
            <div v-else-if="song" class="selected-song">
                Выбран файл: {{ song.original_filename }}
            </div>
            <Buttons :elems="effects" name="effect" v-model="form.effect" />
            <button
                type="submit"
                class="button"
                :class="{ 'button-loader': processing }"
                :disabled="
                    processing || (!form.song && !song) || user.balance <= 0
                "
            >
                <template v-if="user.balance > 0">
                    Создать ремикс! - 1
                    <BalanceIcon />
                </template>
                <template v-else
                    >Недостаточно <BalanceIcon /> на балансе</template
                >
            </button>
            <button
                class="button"
                @click.prevent="showAds"
                v-if="user.balance > 100"
            >
                Получить 2
                <BalanceIcon />
            </button>
        </form>
    </Block>
    <Block v-if="processedSong && !processing">
        <div class="processed-song">
            <h2>Результат:</h2>
            <AudioPlayer :song="processedSong" />
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
