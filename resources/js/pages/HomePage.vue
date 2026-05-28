<template>
  <v-container fluid class="fill-height">
    <v-row align="center" justify="center">
      <v-col cols="12" class="text-center">
        <h1 class="text-h3 mb-4">{{ t('app.title') }}</h1>

        <v-card class="mx-auto mb-8" max-width="600" variant="outlined">
          <v-card-text>
            <p class="text-body-1 mb-4">
              {{ t('courses.noCourses') }}
            </p>

            <v-btn
              color="primary"
              size="large"
              :to="{ name: 'courses.list' }"
            >
              {{ t('courses.title') }}
            </v-btn>
          </v-card-text>
        </v-card>

        <v-row v-if="authStore.isLoggedIn" justify="center">
          <v-col cols="6" sm="4" md="2">
            <v-card variant="tonal" color="primary">
              <v-card-text class="text-center">
                <div class="text-h4">{{ userStore.totalUsers }}</div>
                <div class="text-caption">{{ t('users.title') }}</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="4" md="2">
            <v-card variant="tonal" color="success">
              <v-card-text class="text-center">
                <div class="text-h4">{{ userStore.totalGroups }}</div>
                <div class="text-caption">{{ t('groups.title') }}</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="4" md="2">
            <v-card variant="tonal" color="info">
              <v-card-text class="text-center">
                <div class="text-h4">{{ userStore.allPermissions.length }}</div>
                <div class="text-caption">{{ t('users.permissions') }}</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="4" md="2">
            <v-card variant="tonal" color="warning">
              <v-card-text class="text-center">
                <div class="text-h4">{{ courseStore.totalCourses }}</div>
                <div class="text-caption">{{ t('courses.title') }}</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="4" md="2">
            <v-card variant="tonal" color="secondary">
              <v-card-text class="text-center">
                <div class="text-h4">{{ courseStore.categories.length }}</div>
                <div class="text-caption">{{ t('app.menu.categories') }}</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import { useUserStore } from '@/stores/user.store';
import { useCourseStore } from '@/stores/course.store';

const { t } = useI18n();
const authStore = useAuthStore();
const userStore = useUserStore();
const courseStore = useCourseStore();

onMounted(() => {
  if (authStore.isLoggedIn) {
    userStore.fetchUsers({ per_page: 1 });
    userStore.fetchGroups();
    userStore.fetchPermissions();
    courseStore.fetchCourses({ per_page: 1 });
    courseStore.fetchCategories();
  }
});
</script>
