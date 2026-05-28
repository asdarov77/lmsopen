<template>
  <v-app>
    <v-app-bar app color="primary" prominent v-show="courseItemShow">
      <v-app-bar-nav-icon
        v-if="checkLeftSideMenu()"
        variant="text"
        @click.stop="drawer = !drawer"
      ></v-app-bar-nav-icon>

      <v-app-bar-title>
        <router-link
          to="/"
          style="cursor: pointer; color: white; text-decoration: none"
        >
          {{ t("app.title") }}
        </router-link>
      </v-app-bar-title>

      <account-menu></account-menu>
    </v-app-bar>

    <v-navigation-drawer
      app
      v-model="drawer"
      v-if="isLoggedIn && courseItemManiShow"
      bottom
      permanent
      width="300"
    >
      <left-side-menu v-if="isLoggedIn"></left-side-menu>
    </v-navigation-drawer>

    <v-main>
      <v-container fluid>
        <router-view></router-view>
      </v-container>
    </v-main>

    <v-footer
      app
      bottom
      fixed
      padless
      width="100%"
      style="z-index: 999"
      v-show="courseItemShow"
    >
      <div class="container mx-auto flex justify-between items-center">
        <p>&copy; 2023 Dinamika</p>
      </div>
      <v-col class="text-right">
        <v-btn size="x-small" @click="changeEn" class="right">eng</v-btn>
        <v-btn size="x-small" @click="changeRu" class="right">rus</v-btn>
      </v-col>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import AccountMenu from '@/components/navigation/AccountMenu.vue';
import LeftSideMenu from '@/components/navigation/LeftSideMenu.vue';

const route = useRoute();
const { t, locale } = useI18n();
const authStore = useAuthStore();

const drawer = ref(true);
const courseItemShow = ref(false);
const courseItemManiShow = ref(false);

const isLoggedIn = computed(() => authStore.isLoggedIn);

function checkLeftSideMenu() {
  if (isLoggedIn.value && authStore.user?.role !== 'Обучаемый') {
    return true;
  }
  return false;
}

function changeRu() {
  locale.value = 'ru';
  localStorage.setItem('locale', 'ru');
}

function changeEn() {
  locale.value = 'en';
  localStorage.setItem('locale', 'en');
}

watch(
  () => route.name,
  (name) => {
    if (name === 'courses.show') {
      drawer.value = false;
    }
    if (name !== 'courses.show') {
      courseItemShow.value = true;
    }
    if (name !== 'courses.manifest') {
      courseItemManiShow.value = true;
    }
  },
  { immediate: true }
);
</script>
