# Frontend (Vue 3 + Vite)

Одностраничное приложение: авторизация, настройки URL отзывов Яндекс.Карт, просмотр отзывов.

## Стек

- Vue 3 (Composition API, `<script setup>`)
- Vue Router, Pinia
- Vite, Laravel Vite Plugin
- Стили: `resources/css/app.css` (BEM-подобные классы)

## Структура `resources/js`

```
api/
  client.js              # Axios: baseURL /api, withCredentials, CSRF, timeout 15s

components/
  layout/
    AppHeader.vue        # Шапка: меню, логотип, выход
    sidebar/
      SidebarBrand.vue   # Имя/email пользователя в сайдбаре
      SidebarLogo.vue    # Логотип в хедере

layouts/
  DashboardLayout.vue    # Оболочка: хедер + сайдбар + контент (для /reviews, /settings)

router/
  index.js               # Маршруты, beforeEach (auth, redirect)
  routeNames.js          # ROUTES.LOGIN, ROUTES.REGISTER, ROUTES.REVIEWS, ROUTES.SETTINGS

stores/
  auth.js                # useAuthStore: user, fetchUser, login, register, logout

views/
  auth/
    LoginView.vue        # /login
    RegisterView.vue     # /register
  ReviewsView.vue        # /reviews — загрузка и отображение отзывов
  SettingsView.vue       # /settings — форма URL Яндекс.Карт

App.vue                 # Условный layout: auth-страницы без DashboardLayout
app.js                  # createApp, Pinia, router, mount #app
bootstrap.js            # Глобальный axios (по необходимости)
```

## Алиас `@`

В `vite.config.js`: `@` → `resources/js`.  
Примеры: `@/api/client`, `@/stores/auth`, `@/views/auth/LoginView.vue`, `@/router/routeNames`.

## Маршруты

Имена заданы в `router/routeNames.js`. В коде использовать константы `ROUTES.*`, а не строки (`router.push({ name: ROUTES.REVIEWS })`, `:to="{ name: ROUTES.LOGIN }"`).

## Страницы без layout

`/login` и `/register` рендерятся без хедера и сайдбара (только форма по центру). Остальные — внутри `DashboardLayout`.
