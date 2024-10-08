<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import Icon from "./UI/Icon.vue";
import axios from "axios";
import { store } from "../store";
import MiniLogo from "./UI/MiniLogo.vue";
import lamejs from "lamejs";
import Popup from "./UI/Popup.vue";

const tg = window.Telegram.WebApp;
const audioContext = new (window.AudioContext || window.webkitAudioContext)();
const audioBuffer = ref(null);
const sourceNode = ref(null);
const pannerNode = ref(null); // PannerNode для создания эффекта 8D
const filterNode = ref(null); // Узел фильтра для управления низкими частотами
const isPlaying = ref(false);
const duration = ref(0);
const currentTime = ref(0);
const isDownloadAudio = ref(false);
const startTime = ref(0);
const resumeTime = ref(0); // время, с которого продолжается воспроизведение
const animationFrameId = ref(null); // ID для requestAnimationFrame
const pannerAnimationId = ref(null); // ID для анимации панорамирования
const panDirection = ref(1); // Направление панорамирования: 1 (вправо) или -1 (влево)

const sendAudio = async () => {
    try {
        isDownloadAudio.value = true;

        if (!audioBuffer.value) {
            throw new Error("Audio buffer is not loaded.");
        }

        // Рассчитываем новую длину аудио в зависимости от playbackRate
        const newLength = Math.floor(
            audioBuffer.value.length / store.playbackRate
        );
        // Создаем OfflineAudioContext для рендеринга аудио с эффектами
        const offlineContext = new OfflineAudioContext({
            numberOfChannels: audioBuffer.value.numberOfChannels,
            length: newLength,
            sampleRate: audioBuffer.value.sampleRate,
        });

        // Создаем и настраиваем новый источник
        const offlineSource = offlineContext.createBufferSource();
        offlineSource.buffer = audioBuffer.value;

        // Применяем эффекты
        const filterNode = offlineContext.createBiquadFilter();
        filterNode.type = "lowshelf";
        filterNode.frequency.value = 200;
        filterNode.gain.value = store.bass;

        const pannerNode = offlineContext.createStereoPanner();

        // Подключение узлов
        offlineSource.connect(filterNode);
        filterNode.connect(pannerNode);
        pannerNode.connect(offlineContext.destination);

        // Устанавливаем скорость воспроизведения и тональность
        offlineSource.playbackRate.value = store.playbackRate;
        offlineSource.detune.value = store.pitchShift * 100;

        // Анимация панорамирования для эффекта 8D
        if (store.stereoEffect) {
            const duration = audioBuffer.value.duration / store.playbackRate;
            const panOscillationFrequency = 0.2; // Частота колебаний панорамы (в герцах)
            const totalOscillations = duration * panOscillationFrequency;
            const panValues = [];

            for (let i = 0; i < totalOscillations; i++) {
                panValues.push(-1); // Левый канал
                panValues.push(1); // Правый канал
            }

            pannerNode.pan.setValueCurveAtTime(panValues, 0, duration);
        }

        // Рендеринг аудио с эффектами
        offlineSource.start();
        const renderedBuffer = await offlineContext.startRendering();

        // Процесс кодирования и отправки
        const numChannels = renderedBuffer.numberOfChannels;
        const mp3Encoder = new lamejs.Mp3Encoder(
            numChannels,
            renderedBuffer.sampleRate,
            128
        );

        let mp3Data = [];
        const samplesPerFrame = 1152;

        for (let i = 0; i < renderedBuffer.length; i += samplesPerFrame) {
            let samples = [];

            for (let channel = 0; channel < numChannels; channel++) {
                const channelData = renderedBuffer.getChannelData(channel);
                const frameSlice = channelData.subarray(i, i + samplesPerFrame);
                const int16Array = new Int16Array(frameSlice.length);

                for (let j = 0; j < frameSlice.length; j++) {
                    int16Array[j] = Math.max(
                        -32768,
                        Math.min(32767, frameSlice[j] * 32768)
                    );
                }

                samples.push(int16Array);
            }

            let mp3buf;
            if (numChannels === 2) {
                mp3buf = mp3Encoder.encodeBuffer(samples[0], samples[1]);
            } else {
                mp3buf = mp3Encoder.encodeBuffer(samples[0]);
            }

            if (mp3buf.length > 0) {
                mp3Data.push(mp3buf);
            }
        }

        const mp3buf = mp3Encoder.flush();
        if (mp3buf.length > 0) {
            mp3Data.push(mp3buf);
        }

        const mp3Blob = new Blob(mp3Data, { type: "audio/mp3" });
        const formData = new FormData();
        formData.append("audio", mp3Blob, "audio.mp3");
        formData.append(
            "filename",
            store.currentSong.processed_filename ??
                store.currentSong.original_filename
        );

        const response = await axios.post("/send-audio", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        console.log("Audio sent successfully:", response.data);
    } catch (error) {
        console.error("Error sending audio:", error);
    } finally {
        isDownloadAudio.value = false;
    }
};

const abortController = ref(null); // Для хранения экземпляра AbortController

const loadAudio = async (url) => {
    try {
        // Прерываем предыдущую загрузку, если она существует
        if (abortController.value) {
            abortController.value.abort();
        }

        // Создаем новый AbortController для текущей загрузки
        abortController.value = new AbortController();
        const { signal } = abortController.value;

        // Очищаем буфер перед загрузкой нового трека
        audioBuffer.value = null;

        const response = await fetch(url, { signal }); // Передаем сигнал для возможности отмены
        const reader = response.body.getReader();
        const totalLength = +response.headers.get("Content-Length");
        let receivedLength = 0; // Общее количество загруженных байтов
        let audioData = new Uint8Array(0); // Данные аудио
        let isPlayingFirstChunk = false; // Флаг для воспроизведения первой части
        let isFullLoaded = false; // Флаг для полной загрузки
        let currentPlaybackTime = 0; // Текущее время воспроизведения
        const partSize = 0.125; // Загружаем по 1/8 трека
        const partLength = Math.floor(totalLength * partSize); // Длина первой части
        let chunksLoaded = 0;
        const requiredChunksBeforePlay = 1;

        const playChunk = async (chunk) => {
            const combinedData = new Uint8Array(
                audioData.length + chunk.length
            );
            combinedData.set(audioData);
            combinedData.set(chunk, audioData.length);

            audioData = combinedData;

            // Декодируем данные
            const decodedData = await audioContext.decodeAudioData(
                chunk.buffer
            );

            if (!audioBuffer.value) {
                audioBuffer.value = decodedData;
            } else {
                // Добавляем новый чанк в буфер
                const newBuffer = audioContext.createBuffer(
                    audioBuffer.value.numberOfChannels,
                    audioBuffer.value.length + decodedData.length,
                    audioBuffer.value.sampleRate
                );

                for (
                    let channel = 0;
                    channel < audioBuffer.value.numberOfChannels;
                    channel++
                ) {
                    const oldData = audioBuffer.value.getChannelData(channel);
                    const newData = decodedData.getChannelData(channel);
                    const combined = new Float32Array(
                        oldData.length + newData.length
                    );

                    combined.set(oldData);
                    combined.set(newData, oldData.length);

                    newBuffer.copyToChannel(combined, channel);
                }

                audioBuffer.value = newBuffer;
            }

            if (
                store.isPlaying &&
                receivedLength <= partLength &&
                !isPlayingFirstChunk
            ) {
                createSourceNode(0);
                play();
                isPlayingFirstChunk = true;
            }

            duration.value = audioBuffer.value.duration;
            chunksLoaded++; // Увеличиваем счетчик чанков
        };

        while (true) {
            const { done, value } = await reader.read();

            if (done) {
                isFullLoaded = true;
                break; // Все данные загружены
            }

            receivedLength += value.length;

            // Воспроизводим часть аудио
            await playChunk(value);
        }

        if (isFullLoaded && isPlayingFirstChunk) {
            // Когда загрузка завершена, сохраняем текущее время воспроизведения
            resumeTime.value = audioContext.currentTime - startTime.value;

            // Перезапускаем воспроизведение с текущего времени
            if (store.isPlaying) {
                createSourceNode(resumeTime.value); // Используем сохраненное время
                // play();
            }
        }
    } catch (error) {
        if (error.name === "AbortError") {
            console.log("Audio loading was aborted.");
            // Очищаем буфер перед загрузкой нового трека
            audioBuffer.value = null;
            currentTime.value = 0; // Сброс текущего времени
        } else {
            console.error("Error loading audio:", error);
        }
    } finally {
        abortController.value = null; // Сбрасываем контроллер после завершения загрузки
    }
};

const createSourceNode = (startTime) => {
    // Останавливаем и освобождаем предыдущий узел
    if (sourceNode.value) {
        sourceNode.value.stop();
        sourceNode.value.disconnect();
    }
    cancelAnimationFrame(pannerAnimationId.value);

    sourceNode.value = audioContext.createBufferSource();
    sourceNode.value.buffer = audioBuffer.value;

    // Создаем и настраиваем фильтр для управления басами
    filterNode.value = audioContext.createBiquadFilter();
    filterNode.value.type = "lowshelf";
    filterNode.value.frequency.value = 200; // Частота среза для низких частот
    filterNode.value.gain.value = store.bass; // Устанавливаем начальное значение басов

    // Создаем и настраиваем PannerNode для эффекта 8D
    pannerNode.value = audioContext.createStereoPanner();
    pannerNode.value.pan.value = 0; // Начальная панорама (центр)

    // Подключаем цепочку: sourceNode -> filterNode -> pannerNode -> destination
    sourceNode.value.connect(filterNode.value);
    filterNode.value.connect(pannerNode.value);
    pannerNode.value.connect(audioContext.destination);

    // Устанавливаем начальные значения playbackRate и detune
    updatePlaybackRate();
    updateDetune();

    // Запускаем узел с новой позиции
    sourceNode.value.start(0, startTime); // используем startTime для начала воспроизведения с сохраненного времени
};

const updatePlaybackRate = () => {
    if (sourceNode.value) {
        sourceNode.value.playbackRate.value = store.playbackRate;
    }
};

const updateDetune = () => {
    if (sourceNode.value) {
        // Изменяем тональность в центах (100 центов = 1 полутон)
        sourceNode.value.detune.value = store.pitchShift * 100;
    }
};

const updateBass = () => {
    if (filterNode.value) {
        filterNode.value.gain.value = store.bass; // Обновляем значение басов
    }
};

const animatePanning = () => {
    let panValue = -1; // Начинаем с левого канала
    const panSpeed = 0.008; // Скорость панорамирования

    const updatePanning = () => {
        if (!pannerNode.value) return;

        // Обновляем значение панорамы
        panValue += panSpeed * panDirection.value;

        // Меняем направление панорамирования, когда достигаем границы
        if (panValue >= 1 || panValue <= -1) {
            panDirection.value *= -1;
        }

        pannerNode.value.pan.value = panValue;

        pannerAnimationId.value = requestAnimationFrame(updatePanning);
    };

    pannerAnimationId.value = requestAnimationFrame(updatePanning);
};

const toggle8DEffect = () => {
    if (store.stereoEffect) {
        animatePanning();
    } else {
        // Останавливаем панорамирование и сбрасываем панораму в центр
        cancelAnimationFrame(pannerAnimationId.value);
        if (pannerNode.value) {
            pannerNode.value.pan.value = 0;
        }
    }
};

const play = () => {
    createSourceNode(resumeTime.value);

    startTime.value = audioContext.currentTime - resumeTime.value;

    isPlaying.value = true;
    store.isPlaying = true;

    animateProgress();
    toggle8DEffect(); // Включаем или отключаем эффект 8D в зависимости от store.stereoEffect
};

const pause = () => {
    if (sourceNode.value) {
        sourceNode.value.stop(); // Остановить воспроизведение
    }

    resumeTime.value = audioContext.currentTime - startTime.value; // Сохранить время на момент паузы
    isPlaying.value = false;
    store.isPlaying = false;

    cancelAnimationFrame(animationFrameId.value); // Остановка анимации прогресса
    cancelAnimationFrame(pannerAnimationId.value); // Остановка анимации панорамирования
};

const togglePlay = () => {
    if (isPlaying.value) {
        pause();
    } else {
        play();
    }
};

const animateProgress = () => {
    const updateTime = () => {
        if (!isPlaying.value) return; // Останавливаем обновление, если воспроизведение на паузе

        currentTime.value = audioContext.currentTime - startTime.value;

        if (currentTime.value >= duration.value) {
            pause();
            currentTime.value = 0;
            resumeTime.value = 0;
        } else {
            animationFrameId.value = requestAnimationFrame(updateTime);
        }
    };

    animationFrameId.value = requestAnimationFrame(updateTime);
};

const seek = (event) => {
    cancelAnimationFrame(animationFrameId.value);

    const progressBar = event.currentTarget;
    const clickPosition =
        event.clientX - progressBar.getBoundingClientRect().left;
    const newTime = (clickPosition / progressBar.clientWidth) * duration.value;

    resumeTime.value = newTime;
    currentTime.value = newTime;

    if (isPlaying.value) {
        play(); // Перезапуск воспроизведения с нового времени
    } else {
        startTime.value = audioContext.currentTime - resumeTime.value;
        createSourceNode(resumeTime.value); // Обновляем источник на новом времени
        sourceNode.value.stop();
    }
};

onMounted(() => {
    watch(
        () => store.currentSong,
        async () => {
            // Останавливаем текущее воспроизведение
            if (sourceNode.value) {
                sourceNode.value.stop(0); // Немедленная остановка
                sourceNode.value.disconnect(); // Отключаем старый источник
                sourceNode.value = null;
            }

            // Сбрасываем состояние времени
            cancelAnimationFrame(animationFrameId.value);
            resumeTime.value = 0;
            currentTime.value = 0;

            // Загружаем новое аудио
            if (store.currentSong && store.currentSong.processed_url) {
                await loadAudio(store.currentSong.processed_url);
            }
        },
        { immediate: true }
    );

    // Следим за изменением темпа и обновляем playbackRate
    watch(
        () => store.playbackRate,
        () => {
            updatePlaybackRate();
        }
    );

    // Следим за изменением тональности и обновляем detune
    watch(
        () => store.pitchShift,
        () => {
            updateDetune();
        }
    );

    // Следим за изменением басов и обновляем фильтр
    watch(
        () => store.bass,
        () => {
            updateBass();
        }
    );

    // Следим за изменением эффекта 8D и обновляем панорамирование
    watch(
        () => store.stereoEffect,
        () => {
            toggle8DEffect();
        }
    );

    watch(
        () => store.isPlaying,
        (newVal) => {
            if (newVal) {
                play();
            } else {
                pause();
            }
            cancelAnimationFrame(animationFrameId.value);
        }
    );
});

onBeforeUnmount(() => {
    if (sourceNode.value) {
        sourceNode.value.stop();
    }
    audioContext.close();
    cancelAnimationFrame(animationFrameId.value);
    cancelAnimationFrame(pannerAnimationId.value); // Остановка анимации панорамирования
});
</script>

<template>
    <Popup v-if="isDownloadAudio" :show="isDownloadAudio">
        <h3>Отправляем аудио вам в личку, пожалуйста подождите</h3>
        <img
            src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Telegram-Animated-Emojis/main/Smileys/Alien%20Monster.webp"
            alt="Alien Monster"
            class="emoji-image"
        />
    </Popup>
    <div class="progress-bar" @click="seek">
        <div
            class="progress-bar__fill"
            :style="{ width: (currentTime / duration) * 100 + '%' }"
        ></div>
    </div>
    <div class="player">
        <div class="player__image">
            <MiniLogo />
        </div>
        <div class="player__info">
            <span class="player__filename">{{
                store.currentSong?.processed_filename
            }}</span>
            <span class="player__author">{{
                store.currentSong?.user.username ?? ""
            }}</span>
        </div>
        <div class="player__controls">
            <button @click="togglePlay" class="player__button">
                <Icon :name="isPlaying ? 'pause' : 'play'" />
            </button>
            <button
                @click="sendAudio"
                class="player__button"
                :disabled="isDownloadAudio"
            >
                <Icon name="download" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.player {
    display: flex;
    align-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    border-radius: 5px;
}

.player__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.player__filename {
    width: 100%;
    font-size: 12px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    color: var(--title-color);
}

.player__author {
    font-size: 10px;
}

.player__controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.progress-bar {
    position: relative;
    width: 100%;
    height: 2px;
    background-color: var(--bg-color-300);
    /* border-radius: 5px; */
    cursor: pointer;
}

.progress-bar__fill {
    position: absolute;
    height: 100%;
    background-color: var(--primary-color-100);
    border-radius: 5px;
}

.player__button {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text-color);
    background-color: transparent;
    border: none;
}

.player__image {
    display: flex;
    align-items: center;
    justify-self: center;
    background-color: var(--bg-color-300);
    padding: 8px;
    border-radius: 8px;
    width: 40px;
    height: 40px;
}
</style>
