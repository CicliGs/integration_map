<template>
    <DashboardLayout v-if="!isAuthPage">
        <router-view />
    </DashboardLayout>
    <router-view v-else />
</template>

<script setup>
import { computed, onErrorCaptured } from 'vue';
import { useRoute } from 'vue-router';
import { ROUTES } from '@/router/routeNames';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const route = useRoute();
const isAuthPage = computed(
    () => route.name === ROUTES.LOGIN || route.name === ROUTES.REGISTER
);

onErrorCaptured((err, _instance, info) => {
    if (import.meta.env.DEV && typeof console !== 'undefined') {
        console.error('Vue error captured:', err, info);
    }
    return false;
});
</script>
