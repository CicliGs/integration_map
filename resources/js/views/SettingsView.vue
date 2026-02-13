<template>
    <div class="page">
        <div class="page__topbar">
            <div class="page__topbar-title">
                <h1 id="settings-title">Подключить Яндекс</h1>
                <p class="page__subtitle">
                    Укажите ссылку на Яндекс, пример:
                    <a
                        href="https://yandex.by/maps/org/samoye_populyarnoye_kafe_tsentr/1010501395/reviews/"
                        target="_blank"
                        rel="noreferrer noopener"
                        class="page__subtitle-link"
                    >
                        yandex.by/maps/org/.../1010501395/reviews/
                    </a>
                </p>
            </div>
        </div>

        <form class="form form--settings" aria-labelledby="settings-title" @submit.prevent="onSubmit">
            <div class="form__field">
                <label for="yandex-url" class="visually-hidden">Ссылка на страницу отзывов Яндекс.Карт</label>
                <input
                    id="yandex-url"
                    v-model="placeUrl"
                    class="input"
                    type="url"
                    required
                    placeholder="https://yandex.by/maps/org/название_организации/1010501395/reviews/"
                    :disabled="loading"
                    :aria-invalid="!!error || !!fieldError"
                    :aria-describedby="fieldError ? 'settings-field-error' : error ? 'settings-error' : undefined"
                />
                <p v-if="fieldError" id="settings-field-error" class="form__error form__error--inline" role="alert">
                    {{ fieldError }}
                </p>
            </div>

            <div class="form__actions">
                <button
                    class="button button--primary"
                    type="submit"
                    :disabled="loading"
                    :aria-busy="loading"
                >
                    {{ loading ? 'Сохранение...' : 'Сохранить' }}
                </button>
                <p v-if="message" class="form__success" role="status">
                    {{ message }}
                </p>
            </div>

            <p v-if="error && !fieldError" id="settings-error" class="form__error" role="alert">
                {{ error }}
            </p>
        </form>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '@/api/client';

const placeUrl = ref('');

const loading = ref(false);
const message = ref('');
const error = ref('');
const fieldError = ref('');

const loadSettings = async () => {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await api.get('/settings/yandex');

        if (data) {
            placeUrl.value = data.yandex_reviews_url ?? '';
        }
    } catch (e) {
        error.value = e?.response?.data?.message || 'Не удалось загрузить настройки';
    } finally {
        loading.value = false;
    }
};

const onSubmit = async () => {
    loading.value = true;
    message.value = '';
    error.value = '';
    fieldError.value = '';

    try {
        await api.post('/settings/yandex', {
            yandex_reviews_url: placeUrl.value,
        });
        message.value = 'Ссылка сохранена. Отзывы подгружаются по ней автоматически.';
    } catch (e) {
        const data = e?.response?.data;
        const errors = data?.errors;
        if (errors?.yandex_reviews_url?.length) {
            fieldError.value = errors.yandex_reviews_url[0];
        } else {
            error.value = typeof data?.message === 'string' ? data.message : 'Не удалось сохранить настройки';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(loadSettings);
</script>
