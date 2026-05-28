<template>
  <div>
    <div class="text-subtitle-1 mb-3">{{ t('settings.general') }}</div>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <form @submit.prevent="saveSettings">
          <div
            v-for="setting in settings"
            :key="setting.name"
            class="mb-3"
          >
            <v-checkbox
              v-if="setting.type === 'boolean'"
              v-model="setting.value"
              :label="setting.name"
              hide-details
            />
            <v-text-field
              v-else
              v-model="setting.value"
              :label="setting.name"
              variant="outlined"
              density="compact"
              hide-details
            />
          </div>

          <v-btn
            color="primary"
            type="submit"
            :loading="isSaving"
          >
            {{ t('app.common.save') }}
          </v-btn>
        </form>
      </v-col>
    </v-row>

    <v-snackbar v-model="showSnackbar" :color="snackbarColor" location="top">
      {{ snackbarMessage }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api.service'

const { t } = useI18n()

const isLoading = ref(false)
const isSaving = ref(false)
const settings = ref([])
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

async function saveSettings() {
  isSaving.value = true

  try {
    await api.post('/api/settings', settings.value)
    snackbarMessage.value = t('app.common.saved')
    snackbarColor.value = 'success'
    showSnackbar.value = true
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.save')
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    isSaving.value = false
  }
}

onMounted(async () => {
  isLoading.value = true

  try {
    const response = await api.get('/api/settings')
    settings.value = response.data?.data || response.data || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
})
</script>
