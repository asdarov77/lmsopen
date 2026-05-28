<template>
  <v-list-item
    prepend-icon="mdi-location-exit"
    title="Выход"
    v-on:click="logout"
    class="logout-btn"
  ></v-list-item>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth.store';

const router = useRouter();
const authStore = useAuthStore();

function logout() {
  authStore.logout();
  router.push('/login');
}
</script>

<style>
.logout-btn {
  background-color: white;
  height: 80px;
}
</style>
