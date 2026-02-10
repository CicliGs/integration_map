<template>
  <section class="settings">
    <h2 class="title">Подключить Яндекс</h2>
    <p class="description">
      Укажите ссылку на Яндекс, пример
      <a
        href="https://yandex.ru/maps/org/sample"
        target="_blank"
        rel="noreferrer"
      >
        https://yandex.ru/maps/org/sample
      </a>
    </p>

    <form class="form" @submit.prevent="onSave">
      <input
        v-model="yandexUrl"
        type="url"
        placeholder="https://yandex.ru/maps/org/sample/reviews/"
      />

      <button type="submit" :disabled="loading">
        {{ loading ? 'Сохраняем...' : 'Сохранить' }}
      </button>
    </form>

    <p v-if="message" class="message">{{ message }}</p>
    <p v-if="error" class="error">{{ error }}</p>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const yandexUrl = ref('');
const loading = ref(false);
const message = ref('');
const error = ref('');

const loadSettings = async () => {
  const { data } = await axios.get('/api/yandex-settings');
  yandexUrl.value = data.yandex_url || '';
};

const onSave = async () => {
  loading.value = true;
  message.value = '';
  error.value = '';
  try {
    await axios.post('/api/yandex-settings', { yandex_url: yandexUrl.value });
    message.value = 'Настройки сохранены';
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка при сохранении';
  } finally {
    loading.value = false;
  }
};

onMounted(loadSettings);
</script>

<style scoped>
.settings {
  max-width: 640px;
}

.title {
  font-size: 20px;
  margin-bottom: 8px;
}

.description {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 20px;
}

.form {
  display: flex;
  gap: 12px;
}

.form input {
  flex: 1;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  padding: 8px 10px;
  font-size: 14px;
}

.form button {
  padding: 8px 18px;
  border-radius: 8px;
  border: none;
  background-color: #4f46e5;
  color: #ffffff;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}

.message {
  margin-top: 10px;
  font-size: 13px;
  color: #10b981;
}

.error {
  margin-top: 10px;
  font-size: 13px;
  color: #dc2626;
}
</style>

<template>
  <div class="settings-page">
    <h2>Подключить Яндекс</h2>
    <p class="hint">
      Укажите ссылку на Яндекс, пример
      <a
        href="https://yandex.ru/maps/org/samoye_apashnoye_kafe/1010051395/reviews/"
        target="_blank"
        rel="noreferrer"
      >
        https://yandex.ru/maps/org/samoye_apashnoye_kafe/1010051395/reviews/
      </a>
    </p>
    <form @submit.prevent="onSave" class="settings-form">
      <input
        v-model="url"
        type="url"
        required
        placeholder="https://yandex.ru/maps/..."
      />
      <button
        class="btn-primary"
        type="submit"
        :disabled="loading"
      >
        {{ loading ? 'Сохраняем...' : 'Сохранить' }}
      </button>
    </form>
    <p v-if="message" class="success">
      {{ message }}
    </p>
    <p v-if="error" class="error">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from '@/utils/axios';

const url = ref('');
const loading = ref(false);
const message = ref('');
const error = ref('');

const loadSettings = async () => {
  const { data } = await axios.get('/yandex/settings');
  url.value = data.yandex_url || '';
};

const onSave = async () => {
  loading.value = true;
  message.value = '';
  error.value = '';
  try {
    await axios.post('/yandex/settings', {
      yandex_url: url.value,
    });
    message.value = 'Настройки сохранены';
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка сохранения';
  } finally {
    loading.value = false;
  }
};

onMounted(loadSettings);
</script>


