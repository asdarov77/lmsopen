<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('categories.create') }}</h1>
      </v-col>
    </v-row>

    <v-row>
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
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <v-text-field
                v-model="form.title"
                :label="t('categories.title')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <v-btn
                color="primary"
                type="submit"
                :loading="isSubmitting"
                :disabled="isSubmitting"
              >
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
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '@/services/api.service';

const { t } = useI18n();
const router = useRouter();

const formRef = ref(null);
const isSubmitting = ref(false);
const aircrafts = ref([]);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

const form = ref({
  title: '',
  aircraft_id: null
});

async function fetchAircrafts() {
  try {
    const response = await api.get('/api/aircrafts');
    aircrafts.value = response.data.data || [];
  } catch (err) {
    console.error('Error fetching aircrafts:', err);
  }
}

async function submit() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isSubmitting.value = true;

  try {
    await api.post('/api/categories', {
      title: form.value.title,
      aircraft_id: form.value.aircraft_id
    });
    snackbarMessage.value = t('categories.created');
    snackbarColor.value = 'success';
    showSnackbar.value = true;
    router.push({ name: 'categories.list' });
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.create');
    snackbarColor.value = 'error';
    showSnackbar.value = true;
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchAircrafts();
});
</script>
