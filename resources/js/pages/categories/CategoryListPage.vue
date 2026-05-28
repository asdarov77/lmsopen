<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('categories.title') }}</h1>
        <v-btn color="primary" variant="tonal" @click="dialog = true">
          <v-icon start>mdi-plus</v-icon>
          {{ t('categories.add') }}
        </v-btn>
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

    <v-row v-else-if="categories.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('categories.noCategories') }}
        </v-alert>
      </v-col>
    </v-row>

    <template v-else>
      <v-card variant="outlined">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">ID</th>
              <th class="text-left">{{ t('categories.title') }}</th>
              <th class="text-left">{{ t('categories.aircraft') }}</th>
              <th class="text-left">{{ t('app.common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categories" :key="category.id">
              <td>{{ category.id }}</td>
              <td>{{ category.title }}</td>
              <td>{{ category.aircraft?.title }}</td>
              <td>
                <v-btn
                  size="small" color="primary" variant="tonal" class="mr-2"
                  :to="{ name: 'categories.edit', params: { id: category.id } }"
                >
                  <v-icon start>mdi-pencil</v-icon>
                  {{ t('app.common.edit') }}
                </v-btn>
                <v-btn
                  size="small" color="error" variant="tonal"
                  @click="confirmDelete(category)"
                >
                  <v-icon start>mdi-delete</v-icon>
                  {{ t('app.common.delete') }}
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>{{ t('app.confirm.title') }}</v-card-title>
          <v-card-text>
            {{ t('categories.confirmDelete', { title: categoryToDelete?.title }) }}
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn variant="text" @click="deleteDialog = false">{{ t('app.common.cancel') }}</v-btn>
            <v-btn color="error" variant="tonal" :loading="isDeleting" @click="deleteCategory">
              {{ t('app.common.delete') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </template>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>{{ t('categories.create') }}</v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-select
              v-model="form.aircraft_id"
              :items="aircrafts"
              item-title="title"
              item-value="id"
              :label="t('categories.aircraft')"
              :rules="[v => !!v || t('app.validation.required')]"
              variant="outlined" density="compact" class="mb-3"
            />
            <v-text-field
              v-model="form.title"
              :label="t('categories.title')"
              :rules="[v => !!v || t('app.validation.required')]"
              variant="outlined" density="compact" class="mb-3"
            />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">{{ t('app.common.cancel') }}</v-btn>
          <v-btn color="primary" :loading="isSubmitting" @click="submitForm">{{ t('app.common.save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const isLoading = ref(false);
const isDeleting = ref(false);
const isSubmitting = ref(false);
const error = ref(null);
const categories = ref([]);
const deleteDialog = ref(false);
const categoryToDelete = ref(null);
const dialog = ref(false);
const formRef = ref(null);
const aircrafts = ref([]);
const form = ref({ title: '', aircraft_id: null });

async function fetchCategories() {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/categories');
    categories.value = response.data?.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

async function fetchAircrafts() {
  try {
    const response = await api.get('/api/aircrafts');
    aircrafts.value = response.data.data || [];
  } catch (err) {
    console.error(err);
  }
}

function confirmDelete(category) {
  categoryToDelete.value = category;
  deleteDialog.value = true;
}

async function deleteCategory() {
  if (!categoryToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/api/categories/${categoryToDelete.value.id}`);
    categories.value = categories.value.filter(c => c.id !== categoryToDelete.value.id);
    deleteDialog.value = false;
    categoryToDelete.value = null;
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.delete');
  } finally {
    isDeleting.value = false;
  }
}

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;
  isSubmitting.value = true;
  try {
    const response = await api.post('/api/categories', {
      title: form.value.title,
      aircraft_id: form.value.aircraft_id
    });
    const created = response.data.data || response.data;
    categories.value.unshift(created);
    dialog.value = false;
    form.value = { title: '', aircraft_id: null };
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.create');
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchCategories();
  fetchAircrafts();
});
</script>
