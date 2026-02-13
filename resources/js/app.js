import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import '../css/app.css';

const mountElement = document.getElementById('app');
if (!mountElement) {
    throw new Error('Element #app not found');
}

try {
    const app = createApp(App);
    app.use(createPinia());
    app.use(router);
    app.mount('#app');
} catch (error) {
    if (typeof console !== 'undefined') {
        console.error('Failed to mount Vue app:', error);
    }
    if (mountElement) {
        const isDev = import.meta.env?.DEV;
        mountElement.innerHTML = `
            <div class="app-error" role="alert">
                <h1>Ошибка загрузки приложения</h1>
                <p><strong>${escapeHtml(error?.message || String(error))}</strong></p>
                ${isDev && error?.stack ? `<pre class="app-error__stack">${escapeHtml(error.stack)}</pre>` : ''}
                <p>Обновите страницу или проверьте консоль браузера.</p>
            </div>
        `;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
