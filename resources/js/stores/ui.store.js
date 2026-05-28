import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUiStore = defineStore('ui', () => {
  const drawer = ref(false);
  const language = ref('ru');

  const login = () => {
    drawer.value = true;
  };

  const logout = () => {
    drawer.value = false;
  };

  const setLanguage = (lang) => {
    language.value = lang;
  };

  const toggleDrawer = () => {
    drawer.value = !drawer.value;
  };

  return {
    drawer,
    language,
    login,
    logout,
    setLanguage,
    toggleDrawer,
  };
});
