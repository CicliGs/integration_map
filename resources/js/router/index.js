import { createRouter, createWebHistory } from 'vue-router';
import { ROUTES } from './routeNames';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: ROUTES.LOGIN,
        component: () => import('@/views/auth/LoginView.vue'),
    },
    {
        path: '/register',
        name: ROUTES.REGISTER,
        component: () => import('@/views/auth/RegisterView.vue'),
    },
    {
        path: '/settings',
        name: ROUTES.SETTINGS,
        component: () => import('@/views/SettingsView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/reviews',
        name: ROUTES.REVIEWS,
        component: () => import('@/views/ReviewsView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/',
        redirect: { name: ROUTES.REVIEWS },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: ROUTES.REVIEWS },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    if (!auth.isInitialized) {
        try {
            await auth.fetchUser();
        } catch {
            // ignore
        }
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return next({ name: ROUTES.LOGIN });
    }

    if ((to.name === ROUTES.LOGIN || to.name === ROUTES.REGISTER) && auth.isAuthenticated) {
        return next({ name: ROUTES.REVIEWS });
    }

    return next();
});

export default router;
