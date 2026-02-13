<template>
    <div class="auth-page">
        <div class="card card--auth">
            <h1 class="card__title card__title--center" id="register-title">Регистрация</h1>

            <form class="form" aria-labelledby="register-title" @submit.prevent="onSubmit">
                <label class="form__field">
                    <span class="form__label">Имя</span>
                    <input
                        id="register-name"
                        v-model="name"
                        class="input"
                        type="text"
                        autocomplete="name"
                        required
                        :disabled="loading"
                        :aria-invalid="!!errors.name"
                    />
                    <span v-if="errors.name" class="form__error">{{ errors.name }}</span>
                </label>

                <label class="form__field">
                    <span class="form__label">Email</span>
                    <input
                        id="register-email"
                        v-model="email"
                        class="input"
                        type="email"
                        autocomplete="email"
                        required
                        :disabled="loading"
                        :aria-invalid="!!errors.email"
                    />
                    <span v-if="errors.email" class="form__error">{{ errors.email }}</span>
                </label>

                <label class="form__field">
                    <span class="form__label">Пароль</span>
                    <input
                        id="register-password"
                        v-model="password"
                        class="input"
                        type="password"
                        autocomplete="new-password"
                        required
                        :disabled="loading"
                        :aria-invalid="!!errors.password"
                        :aria-describedby="errors.password ? 'register-password-error' : undefined"
                    />
                    <span v-if="errors.password" id="register-password-error" class="form__error">{{ errors.password }}</span>
                </label>

                <label class="form__field">
                    <span class="form__label">Подтверждение пароля</span>
                    <input
                        id="register-password-confirmation"
                        v-model="passwordConfirmation"
                        class="input"
                        type="password"
                        autocomplete="new-password"
                        required
                        :disabled="loading"
                        :aria-invalid="!!errors.password"
                        :aria-describedby="errors.password ? 'register-password-error' : undefined"
                    />
                </label>

                <button
                    class="button button--primary"
                    type="submit"
                    :disabled="loading"
                    :aria-busy="loading"
                >
                    {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
                </button>

                <p v-if="error" class="form__error" role="alert">
                    {{ error }}
                </p>

                <p class="auth-page__link">
                    Уже есть аккаунт?
                    <RouterLink :to="{ name: ROUTES.LOGIN }">Войти</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { ROUTES } from '@/router/routeNames';
import { useAuthStore } from '@/stores/auth';

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');
const errors = reactive({ name: '', email: '', password: '' });

const router = useRouter();
const auth = useAuthStore();

async function onSubmit() {
    error.value = '';
    errors.name = '';
    errors.email = '';
    errors.password = '';

    if (password.value !== passwordConfirmation.value) {
        errors.password = 'Пароли не совпадают';
        return;
    }
    if (password.value.length < 8) {
        errors.password = 'Пароль должен быть не менее 8 символов';
        return;
    }

    loading.value = true;
    try {
        await auth.register(name.value, email.value, password.value, passwordConfirmation.value);
        await router.push({ name: ROUTES.REVIEWS });
    } catch (e) {
        const data = e?.response?.data;
        if (data?.errors) {
            if (data.errors.name) errors.name = data.errors.name[0];
            if (data.errors.email) errors.email = data.errors.email[0];
            if (data.errors.password) errors.password = data.errors.password[0];
        }
        error.value = data?.message || 'Не удалось зарегистрироваться';
    } finally {
        loading.value = false;
    }
}
</script>
