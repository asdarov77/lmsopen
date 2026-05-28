<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('user.myCourses') }}</h1>
      </v-col>
    </v-row>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="error">
      <v-col cols="12">
        <v-alert type="error" variant="tonal" closable @click:close="error = null">
          {{ error }}
        </v-alert>
      </v-col>
    </v-row>

    <v-row v-else-if="courses.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('user.noCourses') }}
        </v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col
        v-for="course in courses"
        :key="course.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card height="100%" class="d-flex flex-column" variant="outlined">
          <v-card-item>
            <v-card-title class="text-body-1">{{ course.title }}</v-card-title>
            <v-card-subtitle v-if="course.aircraft" class="text-caption">
              {{ course.aircraft.title || course.aircraft }}
            </v-card-subtitle>
          </v-card-item>

          <v-card-text class="flex-grow-1">
            <p class="text-body-2 text-medium-emphasis mb-2">
              {{ course.short_description || course.description }}
            </p>
            <v-chip
              v-if="course.progress !== undefined"
              size="small"
              :color="progressColor(course.progress)"
              variant="tonal"
            >
              {{ course.progress }}%
            </v-chip>
          </v-card-text>

          <v-card-actions>
            <v-btn
              color="primary"
              variant="tonal"
              :to="{ name: 'user.course', params: { id: course.id } }"
            >
              {{ t('user.continueLearning') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import api from '@/services/api.service';

const { t } = useI18n();
const authStore = useAuthStore();

const isLoading = ref(false);
const error = ref(null);
const courses = ref([]);

function progressColor(progress) {
  if (progress >= 80) return 'success';
  if (progress >= 50) return 'warning';
  return 'primary';
}

async function fetchMyCourses() {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await api.get('/api/user/courses');
    courses.value = response.data?.data || response.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchMyCourses();
});
</script>
