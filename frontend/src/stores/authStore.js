import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(email, password) {
      const { data } = await axios.post('/api/login', { email, password });
      this.user = data.user;
    },
    async fetchMe() {
      try {
        const { data } = await axios.get('/api/me');
        this.user = data.user;
      } catch (e) {
        this.user = null;
      }
    },
    async logout() {
      await axios.post('/api/logout');
      this.user = null;
    },
  },
});


