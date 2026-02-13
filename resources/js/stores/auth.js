import { defineStore } from 'pinia';
import api from '@/api/client';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        initialized: false,
    }),
    getters: {
        isAuthenticated: (state) => !!state.user,
        isInitialized: (state) => state.initialized,
    },
    actions: {
        async fetchUser() {
            try {
                const { data } = await api.get('/user');
                this.user = data;
            } catch {
                this.user = null;
            } finally {
                this.initialized = true;
            }
        },
        async login(email, password, remember = false) {
            await api.get('/sanctum/csrf-cookie');
            const { data } = await api.post('/login', { email, password, remember });
            this.user = data.user;
        },
        async register(name, email, password, password_confirmation) {
            await api.get('/sanctum/csrf-cookie');
            const { data } = await api.post('/register', {
                name,
                email,
                password,
                password_confirmation,
            });
            this.user = data.user;
        },
        async logout() {
            try {
                await api.post('/logout');
            } finally {
                this.user = null;
            }
        },
    },
});
