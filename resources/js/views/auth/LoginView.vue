<template>
    <div class="auth-page">
        <div class="card card--auth">
            <h1 class="card__title card__title--center" id="login-title">Вход</h1>

            <form class="form" aria-labelledby="login-title" @submit.prevent="onSubmit">
                <label class="form__field">
                    <span class="form__label">Email</span>
                    <input
                        id="login-email"
                        v-model="email"
                        class="input"
                        type="email"
                        autocomplete="email"
                        required
                        :disabled="loading"
                        :aria-invalid="!!error"
                        :aria-describedby="error ? 'login-error' : undefined"
                    />
                </label>

                <label class="form__field">
                    <span class="form__label">Пароль</span>
                    <input
                        id="login-password"
                        v-model="password"
                        class="input"
                        type="password"
                        autocomplete="current-password"
                        required
                        :disabled="loading"
                        :aria-invalid="!!error"
                    />
                </label>

                <label class="form__field form__field--row">
                    <input
                        type="checkbox"
                        v-model="remember"
                        class="form__checkbox"
                        :disabled="loading"
                    />
                    <span class="form__label form__label--inline">Запомнить меня</span>
                </label>

                <button
                    class="button button--primary"
                    type="submit"
                    :disabled="loading"
                    :aria-busy="loading"
                >
                    {{ loading ? 'Входим...' : 'Войти' }}
                </button>

                <p v-if="error" id="login-error" class="form__error" role="alert">
                    {{ error }}
                </p>

                <p v-if="isDev" class="auth-page__hint">
                    Тестовые данные: <strong>admin@example.com</strong> / <strong>password</strong>
                </p>

                <p class="auth-page__link">
                    Нет аккаунта?
                    <RouterLink :to="{ name: ROUTES.REGISTER }">Зарегистрироваться</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { ROUTES } from '@/router/routeNames';
import { useAuthStore } from '@/stores/auth';

const isDev = import.meta.env.DEV;
const email = ref('');
const password = ref('');
const remember = ref(false);
const loading = ref(false);
const error = ref('');

const router = useRouter();
const auth = useAuthStore();

async function onSubmit() {
    if (loading.value) return;
    loading.value = true;
    error.value = '';
    try {
        await auth.login(email.value, password.value, remember.value);
        await router.push({ name: ROUTES.REVIEWS });
    } catch (e) {
        const msg = e?.response?.data?.message;
        error.value = typeof msg === 'string' ? msg : 'Неверный логин или пароль';
    } finally {
        loading.value = false;
    }
}
</script>
