<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('courses.title') }}</h1>
      </v-col>
    </v-row>

    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.aircraft_id"
              :items="aircrafts"
              item-title="title"
              item-value="id"
              :label="t('courses.aircraft')"
              clearable
              @update:model-value="fetchCourses"
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.category_id"
              :items="categories"
              item-title="title"
              item-value="id"
              :label="t('courses.category')"
              clearable
              @update:model-value="fetchCourses"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="searchQuery"
              :label="t('app.common.search')"
              prepend-inner-icon="mdi-magnify"
              clearable
              @update:model-value="debouncedSearch"
            />
          </v-col>
          <v-col cols="12" md="2" class="d-flex align-center">
            <v-btn
              v-if="authStore.isLoggedIn && authStore.hasPermission('manage-users')"
              color="success"
              prepend-icon="mdi-plus"
              :to="{ name: 'courses.create' }"
            >
              {{ t('courses.add') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="courses.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('courses.noCourses') }}
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
        <v-card height="100%" class="d-flex flex-column">
          <v-card-item>
            <v-card-title class="text-body-1">{{ course.title }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ course.aircraft?.title }}
            </v-card-subtitle>
          </v-card-item>

          <v-card-text class="flex-grow-1">
            <p class="text-body-2 text-medium-emphasis mb-2">
              {{ course.short_description || course.description }}
            </p>
            <v-chip
              v-for="category in course.categories?.slice(0, 2)"
              :key="category.id"
              size="small"
              class="mr-1 mb-1"
              color="primary"
              variant="tonal"
            >
              {{ category.title }}
            </v-chip>
          </v-card-text>

          <v-card-actions>
            <v-btn
              color="primary"
              variant="tonal"
              :to="{ name: 'courses.show', params: { id: course.id } }"
            >
              {{ t('courses.startLearning') }}
            </v-btn>
            <v-spacer />
            <v-btn
              v-if="authStore.isLoggedIn && authStore.hasPermission('manage-users')"
              size="small"
              color="secondary"
              variant="text"
              :to="{ name: 'courses.edit', params: { id: course.id } }"
              icon="mdi-pencil"
            />
            <v-btn
              v-if="authStore.isLoggedIn && authStore.hasPermission('manage-users')"
              size="small"
              color="error"
              variant="text"
              icon="mdi-delete"
              @click="confirmDelete(course)"
            />
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-if="pagination.totalPages > 1" class="mt-4">
      <v-col cols="12" class="d-flex justify-center">
        <v-pagination
          v-model="pagination.page"
          :length="pagination.totalPages"
          :total-visible="5"
          @update:model-value="onPageChange"
        />
      </v-col>
    </v-row>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('app.common.confirm') }}</v-card-title>
        <v-card-text>
          {{ t('courses.deleteConfirm', { title: deleteTarget?.title || deleteTarget?.id }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="error" variant="text" @click="deleteDialog = false">
            {{ t('app.common.cancel') }}
          </v-btn>
          <v-btn color="primary" variant="tonal" @click="deleteCourse">
            {{ t('app.common.delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="3000"
    >
      {{ snackbar.text }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import api from '@/services/api.service';

const { t } = useI18n();
const authStore = useAuthStore();

const isLoading = ref(false);
const courses = ref([]);
const aircrafts = ref([]);
const categories = ref([]);
const searchQuery = ref('');
const deleteDialog = ref(false);
const deleteTarget = ref(null);
let searchTimeout = null;

const filters = reactive({
  aircraft_id: null,
  category_id: null,
  search: ''
});

const pagination = reactive({
  page: 1,
  perPage: 12,
  total: 0,
  totalPages: 0
});

const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
});

function confirmDelete(course) {
  deleteTarget.value = course;
  deleteDialog.value = true;
}

async function deleteCourse() {
  if (!deleteTarget.value) return;
  try {
    await api.delete(`/api/courses/${deleteTarget.value.id}`);
    courses.value = courses.value.filter(c => c.id !== deleteTarget.value.id);
    snackbar.value = { show: true, text: t('courses.deleteSuccess'), color: 'success' };
  } catch {
    snackbar.value = { show: true, text: t('courses.deleteError'), color: 'error' };
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

async function fetchCourses() {
  isLoading.value = true;
  try {
    const params = {
      page: pagination.page,
      per_page: pagination.perPage,
      ...filters
    };
    const response = await api.get('/api/courses', { params });
    const result = response.data.data;
    courses.value = Array.isArray(result) ? result : (result?.data || []);
    if (result?.meta?.pagination) {
      const pag = result.meta.pagination;
      pagination.page = pag.page;
      pagination.perPage = pag.perPage;
      pagination.total = pag.total;
      pagination.totalPages = pag.totalPages;
    }
  } catch (error) {
    console.error('Error fetching courses:', error);
  } finally {
    isLoading.value = false;
  }
}

async function fetchFilters() {
  try {
    const [aircraftsRes, categoriesRes] = await Promise.all([
      api.get('/api/aircrafts'),
      api.get('/api/categories')
    ]);
    aircrafts.value = aircraftsRes.data?.data || [];
    categories.value = categoriesRes.data?.data || [];
  } catch (error) {
    console.error('Error fetching filters:', error);
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.search = searchQuery.value;
    pagination.page = 1;
    fetchCourses();
  }, 300);
}

function onPageChange() {
  fetchCourses();
}

onMounted(() => {
  fetchFilters();
  fetchCourses();
});
</script>
