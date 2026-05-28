<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('categories.edit') }}</h1>
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

    <v-row v-else>
      <v-col cols="12" md="6">
        <v-card variant="outlined">
          <v-card-text>
            <v-form ref="formRef" @submit.prevent="submit">
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
              <v-text-field
                v-model="form.code"
                :label="t('categories.code')"
                variant="outlined" density="compact" class="mb-3"
              />
              <v-btn color="primary" type="submit" :loading="isSubmitting" :disabled="isSubmitting">
                {{ t('app.common.save') }}
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-snackbar v-model="showSnackbar" :color="snackbarColor" location="top">
      {{ snackbarMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();
const router = useRouter();

const props = defineProps({
  id: { type: [String, Number], required: true }
});

const formRef = ref(null);
const isLoading = ref(false);
const isSubmitting = ref(false);
const error = ref(null);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
const aircrafts = ref([]);

const form = ref({ title: '', aircraft_id: null, code: '' });

async function fetchCategory() {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get(`/api/categories/${props.id}`);
    const category = response.data;
    form.value.title = category.title || '';
    form.value.aircraft_id = category.aircraft_id || null;
    form.value.code = category.code || '';
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

async function submit() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;
  isSubmitting.value = true;
  try {
    await api.put(`/api/categories/${props.id}`, {
      title: form.value.title,
      aircraft_id: form.value.aircraft_id,
      code: form.value.code
    });
    snackbarMessage.value = t('categories.updated');
    snackbarColor.value = 'success';
    showSnackbar.value = true;
    router.push({ name: 'categories.index' });
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.update');
    snackbarColor.value = 'error';
    showSnackbar.value = true;
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchCategory();
  fetchAircrafts();
});
</script>
