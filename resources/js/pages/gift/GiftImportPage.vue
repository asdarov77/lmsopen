<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('gift.import') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card variant="outlined">
          <v-card-text>
            <p class="text-body-2 text-medium-emphasis mb-4">
              {{ t('gift.importDescription') }}
            </p>

            <v-file-input
              v-model="file"
              :label="t('gift.selectFile')"
              accept=".txt,.gift"
              variant="outlined"
              density="compact"
              :rules="[v => !!v || t('app.validation.required')]"
              class="mb-3"
              show-size
              hide-details="auto"
            />

            <v-alert
              v-if="resultMessage"
              :type="resultType"
              variant="tonal"
              closable
              class="mb-3"
              @click:close="resultMessage = null"
            >
              {{ resultMessage }}
            </v-alert>

            <v-btn
              color="primary"
              :loading="isUploading"
              :disabled="isUploading || !file"
              @click="uploadFile"
            >
              <v-icon start>mdi-upload</v-icon>
              {{ t('gift.upload') }}
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-if="results.length > 0" class="mt-4">
      <v-col cols="12">
        <h2 class="text-h5 mb-2">{{ t('gift.importResults') }}</h2>
        <v-card variant="outlined">
          <v-table>
            <thead>
              <tr>
                <th class="text-left">{{ t('gift.question') }}</th>
                <th class="text-left">{{ t('gift.status') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(result, idx) in results" :key="idx">
                <td>{{ result.question }}</td>
                <td>
                  <v-chip
                    :color="result.success ? 'success' : 'error'"
                    size="small"
                    variant="tonal"
                  >
                    {{ result.success ? t('app.common.success') : t('app.common.error') }}
                  </v-chip>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const file = ref(null);
const isUploading = ref(false);
const resultMessage = ref(null);
const resultType = ref('success');
const results = ref([]);

async function uploadFile() {
  if (!file.value) return;

  isUploading.value = true;
  resultMessage.value = null;
  results.value = [];

  const formData = new FormData();
  formData.append('file', file.value);

  try {
    const response = await api.post('/api/gift/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    const imported = response.data?.imported || [];
    results.value = Array.isArray(imported) ? imported : [];
    const successCount = results.value.filter(r => r && r.success).length;
    const failCount = results.value.filter(r => !r || !r.success).length;
    resultMessage.value = t('gift.importSuccess', { total: results.value.length, success: successCount, fail: failCount });
    resultType.value = failCount > 0 ? 'warning' : 'success';
  } catch (err) {
    resultMessage.value = err.response?.data?.message || t('app.errors.save');
    resultType.value = 'error';
  } finally {
    isUploading.value = false;
  }
}
</script>
