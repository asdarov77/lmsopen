<template>
  <v-container fluid>
            <v-row class="mb-4">
          <v-col cols="12" class="text-right">
            <v-btn color="secondary" @click="scanStorage" :loading="isLoading" :disabled="isLoading">
              <v-icon start>mdi-magnify</v-icon>
              {{ t('aircrafts.scanStorage') }}
            </v-btn>
          </v-col>
        </v-row>
      <v-col cols="12">
        <h1 class="text-h4">{{ t('aircrafts.title') }}</h1>
      </v-col>
    </v-row>

    <v-card class="mb-4">
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="createAircraft">
          <v-row align="center">
            <v-col cols="12" md="8">
              <v-text-field
                v-model="newAircraftTitle"
                :label="t('aircrafts.name')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                hide-details="auto"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-btn
                color="primary"
                type="submit"
                :loading="isCreating"
                :disabled="isCreating"
              >
                <v-icon start>mdi-plus</v-icon>
                {{ t('aircrafts.add') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
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

    <v-row v-else-if="aircrafts.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('aircrafts.noAircrafts') }}
        </v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col
        v-for="aircraft in aircrafts"
        :key="aircraft.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card variant="outlined" class="d-flex justify-space-between align-center">
          <div>
            <v-card-item>
              <v-card-title class="text-body-1">{{ aircraft.title }}</v-card-title>
              <v-card-subtitle v-if="aircraft.code" class="text-caption">
                {{ aircraft.code }}
              </v-card-subtitle>
            </v-card-item>
          </div>
          <v-btn icon @click="loadAircraftCourses(aircraft.id)" :loading="loadingAircraftId === aircraft.id" :title="t('aircrafts.loadCourses')">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </v-card>
          <v-card-item>
            <v-card-title class="text-body-1">{{ aircraft.title }}</v-card-title>
            <v-card-subtitle v-if="aircraft.code" class="text-caption">
              {{ aircraft.code }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const loadingAircraftId = ref(null);

async function loadAircraftCourses(aircraftId) {
  loadingAircraftId.value = aircraftId;
  try {
    await api.post(`/api/aircrafts/scan/${aircraftId}/load`);
    await fetchAircrafts(); // refresh list
  } catch (err) {
    console.error(err);
  } finally {
    loadingAircraftId.value = null;
  }
}

async function scanStorage() {
  try {
    const response = await api.get('/api/aircrafts/scan');
    // Optionally show a toast with response data
    await fetchAircrafts();
  } catch (err) {
    console.error(err);
  }
}
const isLoading = ref(false);
const isCreating = ref(false);
const error = ref(null);
const aircrafts = ref([]);
const newAircraftTitle = ref('');

async function fetchAircrafts() {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await api.get('/api/aircrafts');
    aircrafts.value = response.data?.data || response.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

async function createAircraft() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isCreating.value = true;
  error.value = null;

  try {
    const response = await api.post('/api/aircrafts', {
      title: newAircraftTitle.value
    });
    aircrafts.value.unshift(response.data);
    newAircraftTitle.value = '';
    formRef.value.resetValidation();
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.create');
  } finally {
    isCreating.value = false;
  }
}

onMounted(() => {
  fetchAircrafts();
});
</script>
