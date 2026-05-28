<template>
  <v-container fluid>
    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="errorMessage">
      <v-col cols="12">
        <v-alert type="error" variant="tonal">
          {{ errorMessage }}
        </v-alert>
      </v-col>
    </v-row>

    <template v-else-if="course">
      <v-row class="mb-4">
        <v-col cols="12">
          <v-btn
            variant="text"
            prepend-icon="mdi-arrow-left"
            :to="{ name: 'courses.list' }"
          >
            {{ t('common.back') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="8">
          <v-card>
            <v-card-item>
              <v-card-title class="text-h4">{{ course.title }}</v-card-title>
              <v-card-subtitle v-if="course.aircraft" class="text-h6 mt-2">
                {{ course.aircraft.title || course.aircraft }}
              </v-card-subtitle>
            </v-card-item>

            <v-card-text>
              <p class="text-body-1 mb-4">
                {{ course.description || course.short_description }}
              </p>

              <div v-if="course.categories && course.categories.length">
                <v-chip
                  v-for="cat in course.categories"
                  :key="cat.id"
                  color="primary"
                  variant="tonal"
                  class="mr-2 mb-2"
                  size="small"
                >
                  {{ cat.title }}
                </v-chip>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                color="primary"
                variant="tonal"
                :to="{
                  name: 'courses.manifest',
                  params: { id: course.id },
                  query: { categoryId: course.categories?.[0]?.id || '' }
                }"
              >
                {{ t('courses.startLearning') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card variant="outlined">
            <v-card-item>
              <v-card-title class="text-h6">{{ t('courses.details') }}</v-card-title>
            </v-card-item>
            <v-card-text>
              <v-list density="compact">
                <v-list-item>
                  <template #prepend>
                    <v-icon>mdi-airplane</v-icon>
                  </template>
                  <v-list-item-title>{{ t('courses.aircraft') }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ course.aircraft?.title || course.aircraft || '-' }}
                  </v-list-item-subtitle>
                </v-list-item>

                <v-list-item v-if="course.categories?.length">
                  <template #prepend>
                    <v-icon>mdi-shape</v-icon>
                  </template>
                  <v-list-item-title>{{ t('courses.category') }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ course.categories.map(c => c.title).join(', ') }}
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const props = defineProps({
  id: { type: Number, required: true }
});

const isLoading = ref(false);
const errorMessage = ref('');
const course = ref(null);

async function fetchCourse() {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await api.get(`/api/courses/${props.id}`);
    course.value = response.data;
  } catch (error) {
    errorMessage.value = t('errors.loadFailed');
    console.error('Error fetching course:', error);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchCourse();
});
</script>
