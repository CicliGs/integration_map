import { defineStore } from 'pinia';
import axios from '@/utils/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(payload) {
      const { data } = await axios.post('/auth/login', payload);
      this.user = data.user;
    },
    async logout() {
      await axios.post('/auth/logout');
      this.user = null;
    },
  },
});


