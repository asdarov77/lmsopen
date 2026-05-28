import { createI18n as createVueI18n } from 'vue-i18n';
import { languages } from '../locales/index.js';

export const i18n = createVueI18n({
  legacy: false,
  locale: localStorage.getItem('locale') || 'ru',
  fallbackLocale: 'ru',
  messages: languages
});

export function createI18n() {
  return i18n;
}

export default i18n;
