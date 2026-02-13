<template>
    <div class="page">
            <header class="page__header">
                <div>
                    <div class="reviews__source">
                        <span class="reviews__badge">
                            <svg width="13" height="16" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.0209 0C2.69556 0 0 2.69556 0 6.0209C0 7.68297 0.673438 9.1879 1.76262 10.2774C2.8521 11.3675 5.41881 12.9449 5.56934 14.6007C5.5919 14.849 5.77164 15.0523 6.0209 15.0523C6.27017 15.0523 6.4499 14.849 6.47247 14.6007C6.62299 12.9449 9.1897 11.3675 10.2792 10.2774C11.3684 9.1879 12.0418 7.68297 12.0418 6.0209C12.0418 2.69556 9.34625 0 6.0209 0Z" fill="#FF4433"/>
                                <path d="M6.10732 8.21463C7.27116 8.21463 8.21461 7.27115 8.21461 6.10732C8.21461 4.94348 7.27116 4 6.10732 4C4.94349 4 4 4.94348 4 6.10732C4 7.27115 4.94349 8.21463 6.10732 8.21463Z" fill="white"/>
                            </svg>
                            Яндекс Карты
                        </span>
                    </div>
                </div>
            </header>

            <div
                v-if="loading"
                class="state state--muted"
                role="status"
                aria-live="polite"
                aria-busy="true"
            >
                Загрузка отзывов с Яндекс.Карт...
            </div>

            <div v-else-if="error" class="state state--error" role="alert">
                {{ error }}
            </div>

            <div v-else-if="!hasData" class="state state--error" role="alert">
                Не найдена ссылка на организацию. Перейдите в раздел «Настройки» и сохраните ссылку на страницу отзывов Яндекс.Карт.
            </div>

            <div v-else class="reviews">
                <div class="reviews__main">
                    <div class="reviews__list-section">
                        <div class="reviews__list">
                            <article
                                v-for="review in reviews"
                                :key="review.id || (getAuthorName(review) + (review.date || '') + (review.text || ''))"
                                class="review-card"
                            >
                                <div class="review-card__inner">
                                    <div class="review-card__content-wrapper">
                                        <div class="review-card__top-row">
                                            <div class="review-card__date-location">
                                                <span class="review-card__date">{{ formatReviewDate(review.date) }}</span>
                                                <span v-if="company && company.name" class="review-card__venue-name">{{ company.name }}</span>
                                                <span class="review-card__branch">
                                                    <svg class="review-card__branch-icon" width="13" height="16" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6.0209 0C2.69556 0 0 2.69556 0 6.0209C0 7.68297 0.673438 9.1879 1.76262 10.2774C2.8521 11.3675 5.41881 12.9449 5.56934 14.6007C5.5919 14.849 5.77164 15.0523 6.0209 15.0523C6.27017 15.0523 6.4499 14.849 6.47247 14.6007C6.62299 12.9449 9.1897 11.3675 10.2792 10.2774C11.3684 9.1879 12.0418 7.68297 12.0418 6.0209C12.0418 2.69556 9.34625 0 6.0209 0Z" fill="#FF4433"/>
                                                        <path d="M6.10732 8.21463C7.27116 8.21463 8.21461 7.27115 8.21461 6.10732C8.21461 4.94348 7.27116 4 6.10732 4C4.94349 4 4 4.94348 4 6.10732C4 7.27115 4.94349 8.21463 6.10732 8.21463Z" fill="white"/>
                                                    </svg>
                                                    <span v-if="getReviewBranch(review)">{{ getReviewBranch(review) }}</span>
                                                </span>
                                            </div>
                                            <div class="review-card__rating">
                                                <template v-for="i in 5" :key="i">
                                                    <span
                                                        class="review-card__star"
                                                        :class="{ 'review-card__star--filled': reviewStarFilled(review, i) }"
                                                    >★</span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="review-card__author-row">
                                            <span class="review-card__author">{{ getAuthorName(review) }}</span>
                                            <span v-if="getReviewPhone(review)" class="review-card__phone">{{ getReviewPhone(review) }}</span>
                                        </div>
                                        <p class="review-card__text">{{ review.text }}</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>

                <aside class="reviews__summary-card">
                    <div class="reviews__summary-rating">
                        <span class="reviews__summary-value">{{ formatCompanyRating() }}</span>
                        <span class="reviews__summary-stars">
                            <template v-for="i in 5" :key="i">
                                <span
                                    class="reviews__summary-star"
                                    :class="{ 'reviews__summary-star--filled': companyStarFilled(i) }"
                                >★</span>
                            </template>
                        </span>
                    </div>
                    <hr class="reviews__summary-divider">
                    <p class="reviews__summary-total">
                        Всего отзывов: {{ formatReviewsCount() }}
                    </p>
                </aside>
            </div>
        </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/api/client';

const company = ref(null);
const reviews = ref([]);
const loading = ref(false);
const error = ref('');

const hasData = computed(() =>
    Boolean(company.value?.name) || (Array.isArray(reviews.value) && reviews.value.length > 0)
);

const load = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get('/reviews');
        company.value = response.data.company ?? {
            name: null,
            rating: null,
            reviews_count: null,
            ratings_count: null,
        };
        const raw = response.data.reviews || [];

        const seen = new Set();
        const unique = [];

        for (const item of raw) {
            const key = [item.author || '', item.date || '', item.text || ''].join('|');
            if (!seen.has(key)) {
                seen.add(key);
                unique.push(item);
            }
        }

        reviews.value = unique;
    } catch (err) {
        error.value = err.response?.status === 401
            ? 'Войдите в аккаунт для просмотра отзывов.'
            : 'Не удалось загрузить отзывы.';
        company.value = { name: null, rating: null, reviews_count: null, ratings_count: null };
    } finally {
        loading.value = false;
    }
};

onMounted(load);

const getReviewRating = (review) => (review && review.rating) ? review.rating : null;

/** Звезда с индексом index (1..5) заполнена при рейтинге rating (0..5). */
const starFilled = (rating, index) => {
    if (rating == null) return false;
    const num = Number(rating);
    return index <= Math.floor(num) || (index === Math.floor(num) + 1 && num % 1 >= 0.5);
};

const reviewStarFilled = (review, index) => starFilled(getReviewRating(review), index);

const companyStarFilled = (index) => starFilled(company.value?.rating, index);

const formatCompanyRating = () => {
    if (!company.value || !company.value.rating) return '—';
    const value = Number(company.value.rating);
    return Number.isNaN(value) ? '—' : value.toFixed(1).replace('.', ',');
};

const formatReviewsCount = () => {
    if (!company.value || company.value.reviews_count == null) return '—';
    const count = company.value.reviews_count;
    return count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
};

const splitAuthor = (raw) => {
    if (!raw) return { name: 'Аноним', badge: null };
    let text = String(raw).replace(/\s+/g, ' ').trim();
    text = text.replace(/(Подписаться|Subscribe)$/iu, '').trim();
    let name = text;
    let badge = null;
    const badgeMarkers = ['Знаток города', 'Дегустатор', 'Эксперт', 'Local guide', 'Local Guide'];
    let badgeIndex = -1;
    for (const marker of badgeMarkers) {
        const idx = text.indexOf(marker);
        if (idx !== -1 && (badgeIndex === -1 || idx < badgeIndex)) badgeIndex = idx;
    }
    if (badgeIndex !== -1) {
        name = text.slice(0, badgeIndex).trim();
        badge = text.slice(badgeIndex).trim();
    } else {
        const genericMatch = text.match(/^(.*?)([А-ЯA-ZЁ][^0-9]*\d+\s*уровн[а-я]*)$/u);
        if (genericMatch) {
            name = genericMatch[1].trim();
            badge = genericMatch[2].trim();
        }
    }
    return { name: name || 'Аноним', badge };
};

const getAuthorName = (review) => {
    const parts = splitAuthor(review && review.author ? review.author : '');
    return parts.name;
};

const getReviewPhone = (review) => {
    if (!review) return null;
    if (review.phone) return review.phone;
    if (review.author) {
        const phoneMatch = review.author.match(/\+?7\s?\d{3}\s?\d{3}\s?\d{2}\s?\d{2}/);
        if (phoneMatch) return phoneMatch[0];
    }
    return null;
};

const getReviewBranch = (review) => {
    if (!review) return null;
    if (review.branch) return review.branch;
    if (review.location) return review.location;
    if (review.text) {
        const branchMatch = review.text.match(/филиал\s*(?:№|номер)?\s*(\d+|[^\s]+)/iu);
        if (branchMatch) {
            return `Филиал ${branchMatch[1]}`;
        }
    }
    return null;
};

const formatReviewDate = (dateStr) => {
    if (!dateStr) return '—';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${day}.${month}.${year} ${hours}:${minutes}`;
    } catch {
        return dateStr;
    }
};
</script>
