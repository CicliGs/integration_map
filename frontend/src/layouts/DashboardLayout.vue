<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="logo">Daily Grow</div>
      <div class="account-name">Название аккаунта</div>
      <nav class="nav">
        <router-link
          :to="{ name: 'reviews' }"
          class="nav-item"
          :class="{ active: $route.name === 'reviews' }"
        >
          Отзывы
        </router-link>
        <router-link
          :to="{ name: 'settings' }"
          class="nav-item"
          :class="{ active: $route.name === 'settings' }"
        >
          Настройки
        </router-link>
      </nav>
    </aside>
    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
</script>

<style scoped>
.layout {
  display: flex;
  height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background-color: #f5f6fb;
}

.sidebar {
  width: 220px;
  background-color: #ffffff;
  border-right: 1px solid #e5e7f0;
  padding: 24px 16px;
  box-sizing: border-box;
}

.logo {
  font-weight: 700;
  font-size: 18px;
  margin-bottom: 4px;
}

.account-name {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 24px;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  padding: 10px 12px;
  border-radius: 6px;
  color: #4b5563;
  text-decoration: none;
  font-size: 14px;
  transition: background-color 0.15s ease;
}

.nav-item:hover {
  background-color: #f3f4ff;
}

.nav-item.active {
  background-color: #e0e7ff;
  color: #1d4ed8;
  font-weight: 600;
}

.content {
  flex: 1;
  padding: 24px 32px;
  box-sizing: border-box;
  overflow-y: auto;
}
</style>

<template>
  <div class="app">
    <aside class="sidebar">
      <div class="logo">
        <span class="logo-icon">▶</span>
        <span class="logo-text">Daily Grow</span>
      </div>
      <div class="account-name">Название аккаунта</div>
      <nav class="nav">
        <RouterLink
          to="/reviews"
          class="nav-item"
          :class="{ active: $route.name === 'reviews' }"
        >
          Отзывы
        </RouterLink>
        <RouterLink
          to="/settings"
          class="nav-item"
          :class="{ active: $route.name === 'settings' }"
        >
          Настройки
        </RouterLink>
      </nav>
    </aside>
    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>


