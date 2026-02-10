<template>
  <div class="login-page">
    <div class="login-card">
      <h1 class="title">Daily Grow</h1>
      <p class="subtitle">Вход в панель интеграции</p>

      <form @submit.prevent="onSubmit">
        <label class="field">
          <span>Email</span>
          <input v-model="email" type="email" required />
        </label>

        <label class="field">
          <span>Пароль</span>
          <input v-model="password" type="password" required />
        </label>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="submit" :disabled="loading">
          {{ loading ? 'Входим...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const onSubmit = async () => {
  loading.value = true;
  error.value = '';
  try {
    await auth.login(email.value, password.value);
    router.push({ name: 'reviews' });
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка входа';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0e7ff, #f5f6fb);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.login-card {
  width: 360px;
  background-color: #ffffff;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
}

.title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
}

.subtitle {
  margin: 4px 0 20px;
  font-size: 13px;
  color: #6b7280;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 13px;
  margin-bottom: 14px;
}

.field input {
  border-radius: 8px;
  border: 1px solid #d1d5db;
  padding: 8px 10px;
  font-size: 14px;
  outline: none;
}

.field input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.1);
}

.error {
  color: #dc2626;
  font-size: 12px;
  margin-bottom: 8px;
}

.submit {
  width: 100%;
  border: none;
  border-radius: 8px;
  padding: 10px 0;
  background-color: #4f46e5;
  color: #ffffff;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}

.submit:disabled {
  opacity: 0.7;
  cursor: default;
}
</style>

<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Daily Grow</h1>
      <p class="subtitle">Вход в панель интеграции</p>
      <form @submit.prevent="onSubmit">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
          />
        </div>
        <div class="form-group">
          <label for="password">Пароль</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
          />
        </div>
        <p v-if="error" class="error">
          {{ error }}
        </p>
        <button
          class="btn-primary"
          type="submit"
          :disabled="loading"
        >
          {{ loading ? 'Входим...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const onSubmit = async () => {
  error.value = '';
  loading.value = true;
  try {
    await auth.login(form);
    await router.push({ name: 'reviews' });
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка входа';
  } finally {
    loading.value = false;
  }
};
</script>


