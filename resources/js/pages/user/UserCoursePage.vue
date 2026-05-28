<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <v-btn
          variant="text"
          class="mb-2"
          :to="{ name: 'user.learning' }"
        >
          <v-icon start>mdi-arrow-left</v-icon>
          {{ t('app.common.back') }}
        </v-btn>
        <h1 class="text-h4">{{ course?.title || t('user.courseDetail') }}</h1>
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

    <template v-else-if="course">
      <v-row>
        <v-col cols="12" md="8">
          <v-card variant="outlined" class="mb-4">
            <v-card-text>
              <p class="text-body-1">{{ course.description }}</p>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card variant="outlined" class="mb-4">
            <v-card-item>
              <v-card-title class="text-body-1">{{ t('user.courseInfo') }}</v-card-title>
            </v-card-item>
            <v-card-text>
              <div class="mb-2">
                <span class="text-caption text-medium-emphasis">{{ t('user.aircraft') }}:</span>
                <span class="ml-2">{{ course.aircraft?.title || course.aircraft || '-' }}</span>
              </div>
              <div class="mb-2">
                <span class="text-caption text-medium-emphasis">{{ t('user.categories') }}:</span>
                <span class="ml-2">
                  <v-chip
                    v-for="cat in course.categories"
                    :key="cat.id"
                    size="x-small"
                    color="primary"
                    variant="tonal"
                    class="mr-1"
                  >
                    {{ cat.title }}
                  </v-chip>
                  <template v-if="!course.categories?.length">-</template>
                </span>
              </div>
              <div v-if="course.progress !== undefined" class="mb-2">
                <span class="text-caption text-medium-emphasis">{{ t('user.progress') }}:</span>
                <v-progress-linear
                  :model-value="course.progress"
                  color="primary"
                  height="8"
                  rounded
                  class="mt-1"
                />
                <div class="text-caption text-right">{{ course.progress }}%</div>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-btn
                color="primary"
                variant="tonal"
                block
                @click="startCourse"
              >
                {{ t('user.startLearning') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <v-row v-if="course.modules?.length">
        <v-col cols="12">
          <h2 class="text-h5 mb-2">{{ t('user.modules') }}</h2>
          <v-list lines="two" variant="outlined">
            <v-list-item
              v-for="(module, idx) in course.modules"
              :key="module.id"
              :title="module.title"
              :subtitle="module.description"
            >
              <template #prepend>
                <v-avatar color="primary" variant="tonal" size="small">
                  {{ idx + 1 }}
                </v-avatar>
              </template>
              <template #append>
                <v-chip
                  v-if="module.completed"
                  color="success"
                  size="x-small"
                  variant="tonal"
                >
                  {{ t('user.completed') }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api.service';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const isLoading = ref(false);
const error = ref(null);
const course = ref(null);

async function fetchCourse() {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await api.get(`/api/user/courses/${props.id}`);
    course.value = response.data?.data || response.data;
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

function startCourse() {
  router.push({ name: 'courses.show', params: { id: props.id } });
}

onMounted(() => {
  fetchCourse();
});
</script>
