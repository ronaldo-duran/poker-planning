import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import es from './locales/es.json';

export default createI18n({
    legacy: false,
    locale: localStorage.getItem('locale') || 'en',
    fallbackLocale: 'en',
    messages: { en, es },
});
