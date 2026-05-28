<template>
  <v-container fluid>
    <v-row justify="center">
      <v-col cols="12" sm="10" md="8" lg="6">
        <v-card elevation="12" style="overflow: visible">
          <v-toolbar color="primary">
            <v-toolbar-title>{{ t('group.enrollmentTitle') }}</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-row v-if="isLoading">
              <v-col cols="12" class="text-center">
                <v-progress-circular indeterminate color="primary" />
              </v-col>
            </v-row>

            <v-form v-else ref="formRef" @submit.prevent="submitForm">
              <v-select
                v-model="form.aircrafts"
                :label="t('group.aircraft')"
                :items="aircrafts"
                item-value="id"
                item-title="path"
                clearable
                @update:model-value="onAircraftChange"
              />

              <v-select
                v-model="form.categories"
                :label="t('group.category')"
                :items="filteredCategories"
                item-value="id"
                item-title="title"
                clearable
                @update:model-value="onCategoryChange"
              />

              <v-divider class="my-4" />

              <v-select
                v-model="form.courses"
                :label="t('group.courses')"
                :items="courseOptions"
                item-value="id"
                item-title="title"
                multiple
                clearable
                chips
                class="mb-4"
              />

              <v-select
                v-model="form.teacher"
                :label="t('group.instructor')"
                :items="filteredInstructors"
                item-title="fio"
                item-value="id"
                clearable
              />

              <v-select
                v-model="form.typeOfLesson"
                :label="t('group.lessonType')"
                :items="lessonTypes"
                item-value="id"
                item-title="title"
                clearable
              />

              <v-divider class="my-4" />

              <v-row>
                <v-col cols="6">
                  <v-text-field
                    v-model="form.study_from"
                    :label="t('group.dateFrom')"
                    type="date"
                    variant="outlined"
                  />
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="form.study_to"
                    :label="t('group.dateTo')"
                    type="date"
                    variant="outlined"
                  />
                </v-col>
              </v-row>

              <v-alert
                v-if="validationErrors.length"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="validationErrors = []"
              >
                <ul class="mb-0 pl-4">
                  <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
                </ul>
              </v-alert>

              <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="errorMessage = ''"
              >
                {{ errorMessage }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer />
            <v-btn color="error" variant="text" @click="cancelBtn">
              {{ t('common.cancel') }}
            </v-btn>
            <v-btn
              color="primary"
              :loading="isSubmitting"
              :disabled="isSubmitting || isLoading"
              @click="submitForm"
            >
              {{ t('common.save') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="3000"
    >
      {{ snackbar.text }}
      <template #actions>
        <v-btn icon size="small" @click="snackbar.show = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useCourseStore } from '@/stores/course.store';
import { useUserStore } from '@/stores/user.store';
import api from '@/services/api.service';

const router = useRouter();
const { t } = useI18n();
const courseStore = useCourseStore();
const userStore = useUserStore();

const props = defineProps({
  id: { type: Number, required: true }
});

const formRef = ref(null);
const isLoading = ref(false);
const isSubmitting = ref(false);
const validationErrors = ref([]);
const errorMessage = ref('');

const filteredCategories = ref([]);
const courseOptions = ref([]);

const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
});

const form = reactive({
  aircrafts: null,
  categories: null,
  courses: [],
  teacher: null,
  typeOfLesson: null,
  study_from: new Date().toISOString().substring(0, 10),
  study_to: ''
});

const aircrafts = computed(() => courseStore.aircrafts || []);
const categories = computed(() => courseStore.categories || []);
const users = computed(() => userStore.users || []);

const filteredInstructors = computed(() =>
  users.value.filter(u => u.role === 'Инструктор')
);

const lessonTypes = [
  { id: 1, title: t('lessonTypes.lecture') },
  { id: 2, title: t('lessonTypes.practice') },
  { id: 3, title: t('lessonTypes.self') }
];

function onAircraftChange(aircraftId) {
  form.categories = null;
  form.courses = [];
  courseOptions.value = [];

  if (!aircraftId) {
    filteredCategories.value = [];
    return;
  }

  filteredCategories.value = categories.value.filter(
    c => c.aircraft_id === aircraftId
  );
}

async function onCategoryChange(categoryId) {
  form.courses = [];
  courseOptions.value = [];

  if (!categoryId || !form.aircrafts) return;

  try {
    const response = await api.get('/api/courses', {
      params: {
        aircraft_id: form.aircrafts,
        category_id: categoryId
      }
    });
    courseOptions.value = response.data || [];
  } catch (error) {
    console.error('Error fetching courses:', error);
  }
}

function validateDates() {
  validationErrors.value = [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const studyFrom = new Date(form.study_from);
  const studyTo = new Date(form.study_to);

  if (!form.study_from) {
    validationErrors.value.push(t('validation.dateFromRequired'));
    return false;
  }

  if (!form.study_to) {
    validationErrors.value.push(t('validation.dateToRequired'));
    return false;
  }

  if (studyFrom < today) {
    validationErrors.value.push(t('validation.dateFromPast'));
    return false;
  }

  if (studyFrom > studyTo) {
    validationErrors.value.push(t('validation.dateFromAfterTo'));
    return false;
  }

  return true;
}

async function submitForm() {
  if (!validateDates()) return;

  isSubmitting.value = true;
  errorMessage.value = '';

  const formData = {
    course_id: form.courses,
    group_id: props.id,
    category_id: form.categories,
    study_from: form.study_from,
    study_to: form.study_to,
    teacher: form.teacher,
    typeOfLesson: form.typeOfLesson
  };

  try {
    await api.post('/api/group2learning', formData);
    router.push({ name: 'groups.list' });
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('errors.saveFailed');
  } finally {
    isSubmitting.value = false;
  }
}

function cancelBtn() {
  router.go(-1);
}

onMounted(async () => {
  isLoading.value = true;

  try {
    await Promise.all([
      courseStore.fetchAircrafts(),
      courseStore.fetchCategories(),
      courseStore.fetchCourses(),
      userStore.fetchUsers()
    ]);

    form.study_from = new Date().toISOString().substring(0, 10);
  } catch (error) {
    console.error('Error loading data:', error);
    errorMessage.value = t('errors.loadFailed');
  } finally {
    isLoading.value = false;
  }
});
</script>
