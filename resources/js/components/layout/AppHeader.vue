<template>
    <header class="app-header">
        <button
            type="button"
            class="app-header__menu"
            aria-label="Открыть меню"
            :aria-expanded="sidebarOpen"
            @click="$emit('toggle-sidebar')"
        >
            <svg class="app-header__menu-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z" fill="currentColor"/>
            </svg>
        </button>
        <RouterLink :to="{ name: ROUTES.REVIEWS }" class="app-header__logo" aria-label="На главную">
            <SidebarLogo />
        </RouterLink>
        <button
            type="button"
            class="app-header__logout"
            aria-label="Выйти"
            @click="handleLogout"
        >
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15.2 29.7778H24V32H15.2C13.99 32 13 31 13 29.7778V14.2222C13 13 13.99 12 15.2 12H24V14.2222H15.2V29.7778Z" fill="#909AB4"/>
                <path d="M26.1 17L25.113 18.0071L28.319 21.2857H21V22.7143H28.319L25.106 25.9929L26.1 27L31 22L26.1 17Z" fill="#909AB4" stroke="#909AB4"/>
            </svg>
        </button>
    </header>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router';
import { ROUTES } from '@/router/routeNames';
import { useAuthStore } from '@/stores/auth';
import SidebarLogo from './sidebar/SidebarLogo.vue';

defineProps({
    sidebarOpen: { type: Boolean, default: false },
});
defineEmits(['toggle-sidebar']);

const router = useRouter();
const auth = useAuthStore();

async function handleLogout() {
    await auth.logout();
    router.push({ name: ROUTES.LOGIN });
}
</script>
