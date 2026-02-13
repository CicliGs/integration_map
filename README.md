# Интеграция с отзывами Яндекс.Карт

Приложение на **Laravel** и **Vue 3** (SPA): авторизация, сохранение ссылки на страницу отзывов организации в Яндекс.Картах и просмотр рейтинга с отзывами.

## Возможности

- **Вход и регистрация** — сессия, CSRF, throttle.
- **Настройки** — сохранение URL страницы отзывов (только домены Яндекс.Карт).
- **Отзывы** — парсинг страницы по сохранённой ссылке: рейтинг, количество отзывов/оценок, список отзывов (автор, дата, текст, звёзды).

Фронт: Vue 3, Vue Router, Pinia, Vite. Бэкенд: Laravel, сессия (Sanctum SPA), одна точка входа — `resources/views/app.blade.php`.

---

## Быстрый старт (Docker)

Нужны Docker, docker-compose и свободные порты **8000** (Laravel) и **5174** (Vite).

```bash
git clone <repo-url> integration_map && cd integration_map
docker-compose up -d --build
```

Миграции и тестовый пользователь (один раз):

```bash
docker-compose exec app sh -lc "
  touch database/database.sqlite &&
  php artisan migrate --force &&
  php artisan db:seed --class=AdminUserSeeder --force
"
```

- **Приложение:** http://localhost:8000  
- **Логин:** `admin@example.com` / `password`  
- После входа: **Настройки** — указать ссылку на отзывы; **Отзывы** — просмотр рейтинга и списка.

Vite подхватывается через `@vite` в Blade, отдельно ничего не открывать.

---

## Локальный запуск (без Docker)

Требуется: PHP ≥ 8.1 (pdo_sqlite), Composer, Node ≥ 18, npm.

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
```

В `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/полный/путь/к/проекту/database/database.sqlite
```

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
npm run dev
php artisan serve
```

Открыть http://127.0.0.1:8000 (для продакшена: `npm run build`).

---

## Маршруты и API

Все страницы отдают один HTML с Vue-приложением; роутинг на фронте.

| Маршрут       | Описание                    |
|---------------|-----------------------------|
| `/`, `/login`, `/register`, `/reviews`, `/settings`, `/*` | Один Blade `app` (SPA) |

API (префикс `/api`, сессия, JSON):

| Метод | URL | Доступ | Описание |
|-------|-----|--------|----------|
| GET   | `/sanctum/csrf-cookie` | — | Получить CSRF cookie |
| POST  | `/login`               | — | Вход (email, password, remember) |
| POST  | `/register`             | — | Регистрация (name, email, password, password_confirmation) |
| POST  | `/logout`               | auth | Выход |
| GET   | `/user`                 | auth | Текущий пользователь |
| GET   | `/settings/yandex`      | auth | Текущий URL отзывов |
| POST  | `/settings/yandex`      | auth | Сохранить URL (yandex_reviews_url, только Яндекс.Карты) |
| GET   | `/reviews`              | auth | Рейтинг и отзывы по сохранённому URL |

Формат ссылки в настройках: страница раздела «Отзывы» организации, например  
`https://yandex.ru/maps/org/название_организации/123456789/reviews/`.

---

## Структура проекта

- **Backend:** `app/Http/Controllers`, `app/Services` (Auth, Settings, YandexReviewsService + YandexPageParser), `app/Repositories`, `app/Models`, `app/Http/Requests`, `app/Exceptions`.
- **Frontend:** `resources/js` — `api/`, `components/layout/`, `layouts/`, `router/`, `stores/`, `views/auth/`, `views/ReviewsView.vue`, `views/SettingsView.vue`. Алиас `@` → `resources/js`, имена маршрутов в `router/routeNames.js`.
- **Точка входа SPA:** `resources/views/app.blade.php` + `resources/js/app.js`.

Подробнее про фронт — в `resources/js/README.md`.
