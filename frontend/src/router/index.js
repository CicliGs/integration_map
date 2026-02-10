import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import LoginView from '@/views/LoginView.vue';
import ReviewsView from '@/views/ReviewsView.vue';
import SettingsView from '@/views/SettingsView.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/reviews',
      },
      {
        path: 'reviews',
        name: 'reviews',
        component: ReviewsView,
      },
      {
        path: 'settings',
        name: 'settings',
        component: SettingsView,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' });
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return next({ name: 'reviews' });
  }

  return next();
});

export default router;

import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '@/views/LoginView.vue';
import ReviewsView from '@/views/ReviewsView.vue';
import SettingsView from '@/views/SettingsView.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/reviews',
      },
      {
        path: 'reviews',
        name: 'reviews',
        component: ReviewsView,
      },
      {
        path: 'settings',
        name: 'settings',
        component: SettingsView,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' });
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return next({ name: 'reviews' });
  }

  return next();
});

export default router;


