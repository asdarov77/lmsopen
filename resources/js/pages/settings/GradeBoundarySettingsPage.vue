<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('settings.gradeBoundaries') }}</h1>
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
          <v-table>
            <thead>
              <tr>
                <th class="text-left">{{ t('settings.grade') }}</th>
                <th class="text-left">{{ t('settings.boundary') }} (%)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(grade, idx) in gradeBoundaries" :key="idx">
                <td class="font-weight-medium">{{ grade.grade }}</td>
                <td>
                  <v-text-field
                    v-model="grade.boundary"
                    type="number"
                    variant="outlined"
                    density="compact"
                    hide-details
                    min="0"
                    max="100"
                    @update:model-value="onBoundaryChange(idx)"
                  />
                </td>
              </tr>
            </tbody>
          </v-table>
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
import api from '@/services/api.service';

const { t } = useI18n();

const isLoading = ref(false);
const error = ref(null);
const gradeBoundaries = ref([]);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

async function fetchGradeBoundaries() {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await api.get('/api/grade-boundaries');
    gradeBoundaries.value = response.data?.data || response.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
  }
}

function onBoundaryChange(index) {
  const boundaries = gradeBoundaries.value;

  if (index > 0) {
    boundaries[index].boundary = Math.max(
      boundaries[index].boundary,
      boundaries[index - 1].boundary + 1
    );
  }

  if (index < boundaries.length - 1) {
    boundaries[index].boundary = Math.min(
      boundaries[index].boundary,
      boundaries[index + 1].boundary - 1
    );
  }

  boundaries[index].boundary = Math.max(0, Math.min(100, boundaries[index].boundary));
  saveBoundaries();
}

async function saveBoundaries() {
  try {
    await api.post('/api/grade-boundaries', {
      boundaries: gradeBoundaries.value
    });
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.save');
    snackbarColor.value = 'error';
    showSnackbar.value = true;
  }
}

onMounted(() => {
  fetchGradeBoundaries();
});
</script>
