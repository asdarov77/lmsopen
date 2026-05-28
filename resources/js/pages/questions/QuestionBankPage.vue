<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('questions.bank') }}</h1>
        <v-btn
          color="primary"
          variant="tonal"
          :to="{ name: 'questions.create' }"
        >
          <v-icon start>mdi-plus</v-icon>
          {{ t('questions.add') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="filters.category_id"
              :items="categories"
              item-title="title"
              item-value="id"
              :label="t('questions.category')"
              clearable
              density="compact"
              variant="outlined"
              hide-details
              @update:model-value="fetchQuestions"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="searchQuery"
              :label="t('app.common.search')"
              prepend-inner-icon="mdi-magnify"
              clearable
              density="compact"
              variant="outlined"
              hide-details
              @update:model-value="debouncedSearch"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

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

    <v-row v-else-if="questions.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('questions.noQuestions') }}
        </v-alert>
      </v-col>
    </v-row>

    <template v-else>
      <v-card variant="outlined">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">ID</th>
              <th class="text-left">{{ t('questions.questionText') }}</th>
              <th class="text-left">{{ t('questions.category') }}</th>
              <th class="text-left">{{ t('questions.type') }}</th>
              <th class="text-left">{{ t('app.common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="question in questions" :key="question.id">
              <td>{{ question.id }}</td>
              <td>{{ question.question_text }}</td>
              <td>{{ question.category?.title || question.category_title }}</td>
              <td>{{ question.type }}</td>
              <td>
                <v-btn
                  size="small"
                  color="primary"
                  variant="tonal"
                  class="mr-2"
                  :to="{ name: 'questions.edit', params: { id: question.id } }"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  size="small"
                  color="error"
                  variant="tonal"
                  @click="confirmDelete(question)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <v-row v-if="pagination.totalPages > 1" class="mt-4">
        <v-col cols="12" class="d-flex justify-center">
          <v-pagination
            v-model="pagination.page"
            :length="pagination.totalPages"
            :total-visible="5"
            @update:model-value="fetchQuestions"
          />
        </v-col>
      </v-row>

      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>{{ t('app.common.confirm') }}</v-card-title>
          <v-card-text>
            {{ t('questions.confirmDelete') }}
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn variant="text" @click="deleteDialog = false">
              {{ t('app.common.cancel') }}
            </v-btn>
            <v-btn
              color="error"
              variant="tonal"
              :loading="isDeleting"
              @click="deleteQuestion"
            >
              {{ t('app.common.delete') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </template>
  </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const isLoading = ref(false);
const isDeleting = ref(false);
const error = ref(null);
const questions = ref([]);
const categories = ref([]);
const searchQuery = ref('');
const deleteDialog = ref(false);
const questionToDelete = ref(null);
let searchTimeout = null;

const filters = reactive({
  category_id: null,
  search: ''
});

const pagination = reactive({
  page: 1,
  perPage: 20,
  total: 0,
  totalPages: 0
});

async function fetchQuestions() {
  isLoading.value = true;
  error.value = null;

  try {
    const params = {
      page: pagination.page,
      per_page: pagination.perPage,
      ...filters
    };

    const response = await api.get('/api/questions', { params });
    const data = response.data;

    if (Array.isArray(data)) {
      questions.value = data;
    } else if (data.data) {
      questions.value = data.data;
      if (data.meta?.pagination) {
        const pag = data.meta.pagination;
        pagination.page = pag.page;
        pagination.perPage = pag.perPage;
        pagination.total = pag.total;
        pagination.totalPages = pag.totalPages;
      }
    } else {
      questions.value = [];
    }
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.search = searchQuery.value;
    pagination.page = 1;
    fetchQuestions();
  }, 300);
}

async function fetchCategories() {
  try {
    const response = await api.get('/api/categories');
    categories.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Error fetching categories:', err);
  }
}

function confirmDelete(question) {
  questionToDelete.value = question;
  deleteDialog.value = true;
}

async function deleteQuestion() {
  if (!questionToDelete.value) return;

  isDeleting.value = true;

  try {
    await api.delete(`/api/questions/${questionToDelete.value.id}`);
    questions.value = questions.value.filter(q => q.id !== questionToDelete.value.id);
    deleteDialog.value = false;
    questionToDelete.value = null;
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.delete');
  } finally {
    isDeleting.value = false;
  }
}

onMounted(() => {
  fetchCategories();
  fetchQuestions();
});
</script>
