import './bootstrap';

import '@fontsource/archivo/latin-600.css';
import '@fontsource/archivo/latin-ext-600.css';
import '@fontsource/archivo/latin-700.css';
import '@fontsource/archivo/latin-ext-700.css';
import '@fontsource/source-sans-3/latin-400.css';
import '@fontsource/source-sans-3/latin-ext-400.css';
import '@fontsource/source-sans-3/latin-600.css';
import '@fontsource/source-sans-3/latin-ext-600.css';
import '@fontsource/ibm-plex-mono/latin-500.css';
import '@fontsource/ibm-plex-mono/latin-ext-500.css';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createI18n } from 'vue-i18n';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import fr from './locales/fr.json';
import en from './locales/en.json';

const supportedLocales = ['fr', 'en'];
const savedLocale = window.localStorage.getItem('appgov-locale');
const initialLocale = supportedLocales.includes(savedLocale) ? savedLocale : 'fr';

document.documentElement.lang = initialLocale;

createInertiaApp({
    title: (title) => (title ? `${title} — AppGov Cameroun` : 'AppGov Cameroun'),
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue'),
    ),
    setup({ el, App, props, plugin }) {
        const i18n = createI18n({
            legacy: false,
            locale: initialLocale,
            fallbackLocale: 'fr',
            messages: { fr, en },
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#155EEF',
        showSpinner: false,
    },
});
