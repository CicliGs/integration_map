<template>
  <section class="reviews-page">
    <header class="header">
      <div class="source-badge">Яндекс Карты</div>
    </header>

    <div class="grid">
      <div class="reviews-list">
        <article
          v-for="review in reviews"
          :key="review.id"
          class="review-card"
        >
          <div class="review-header">
            <div class="meta">
              <div class="date">{{ review.date }}</div>
              <div class="branch">{{ review.branch }}</div>
              <div class="phone">{{ review.phone }}</div>
            </div>
            <div class="rating">
              <span v-for="n in 5" :key="n" class="star">
                {{ n <= review.rating ? '★' : '☆' }}
              </span>
            </div>
          </div>
          <p class="text">
            {{ review.text }}
          </p>
        </article>
      </div>

      <aside class="summary" v-if="summary">
        <div class="summary-rating">
          <div class="value">{{ summary.rating.toFixed(1) }}</div>
          <div class="stars">
            <span v-for="n in 5" :key="n" class="star">
              {{ n <= Math.round(summary.rating) ? '★' : '☆' }}
            </span>
          </div>
        </div>
        <div class="summary-count">
          Всего отзывов: <strong>{{ summary.reviews_count }}</strong>
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const reviews = ref([]);
const summary = ref(null);

const loadReviews = async () => {
  const { data } = await axios.get('/api/yandex-reviews');
  reviews.value = data.reviews || [];
  summary.value = data.summary;
};

onMounted(loadReviews);
</script>

<style scoped>
.reviews-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.source-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  background-color: #fee2e2;
  color: #b91c1c;
  font-size: 12px;
  font-weight: 600;
}

.grid {
  display: grid;
  grid-template-columns: 3fr 1fr;
  gap: 16px;
  align-items: flex-start;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.review-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 14px 16px;
  box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06);
}

.review-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.meta {
  font-size: 12px;
  color: #6b7280;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.rating .star {
  color: #f59e0b;
  font-size: 16px;
}

.text {
  font-size: 14px;
  color: #374151;
}

.summary {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 18px 16px;
  box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06);
}

.summary-rating {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.summary-rating .value {
  font-size: 32px;
  font-weight: 700;
}

.summary-rating .star {
  color: #f59e0b;
  font-size: 18px;
}

.summary-count {
  font-size: 14px;
  color: #4b5563;
}
</style>

<template>
  <div class="reviews-page">
    <header class="reviews-header">
      <div class="source">
        <span class="dot"></span>
        <span>Яндекс Карты</span>
      </div>
    </header>

    <div class="reviews-layout">
      <section class="reviews-list">
        <article
          v-for="review in reviews"
          :key="review.id"
          class="review-card"
        >
          <header class="review-header">
            <div>
              <div class="review-date">
                {{ review.date }}
              </div>
              <div class="review-branch">
                {{ review.branch }}
              </div>
              <div class="review-phone">
                {{ review.phone }}
              </div>
            </div>
            <div class="review-rating">
              <span
                v-for="n in 5"
                :key="n"
                class="star"
                :class="{ active: n <= review.rating }"
              >
                ★
              </span>
            </div>
          </header>
          <p class="review-text">
            {{ review.text }}
          </p>
        </article>
      </section>

      <aside class="reviews-summary" v-if="summary">
        <div class="summary-card">
          <div class="summary-rating">
            <span class="value">{{ summary.rating }}</span>
            <div class="stars">
              <span
                v-for="n in 5"
                :key="n"
                class="star"
                :class="{ active: n <= Math.round(summary.rating) }"
              >
                ★
              </span>
            </div>
          </div>
          <div class="summary-count">
            Всего отзывов: {{ summary.reviews_count }}
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from '@/utils/axios';

const reviews = ref([]);
const summary = ref(null);

const loadReviews = async () => {
  const { data } = await axios.get('/yandex/reviews');
  reviews.value = data.reviews || [];
  summary.value = data.summary;
};

onMounted(loadReviews);
</script>


