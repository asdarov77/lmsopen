<template>
  <div>
    <div class="text-subtitle-1 mb-3">{{ t('settings.gradeBoundaries') }}</div>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">{{ t('settings.grade') }}</th>
              <th class="text-left">{{ t('settings.boundary') }} (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(grade, index) in gradeBoundaries" :key="index">
              <td class="font-weight-medium">{{ grade.grade }}</td>
              <td>
                <v-text-field
                  v-model.number="grade.boundary"
                  type="number"
                  variant="outlined"
                  density="compact"
                  hide-details
                  min="0"
                  max="100"
                  @update:model-value="updateGrade(index)"
                />
              </td>
            </tr>
          </tbody>
        </v-table>
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
const gradeBoundaries = ref([])
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

function updateGrade(index) {
  const currentGrade = gradeBoundaries.value[index]
  const previousGrade = gradeBoundaries.value[index - 1]
  const nextGrade = gradeBoundaries.value[index + 1]

  if (index === 0) {
    currentGrade.boundary = Math.max(currentGrade.boundary, 0)
  } else if (index === gradeBoundaries.value.length - 1) {
    currentGrade.boundary = Math.min(currentGrade.boundary, 100)
  } else {
    currentGrade.boundary = Math.max(
      previousGrade.boundary,
      Math.min(currentGrade.boundary, nextGrade.boundary)
    )
  }

  api
    .post('/api/grade-boundaries', { boundary: currentGrade.boundary, grade: currentGrade.grade })
    .then(() => {
      snackbarMessage.value = t('app.common.saved')
      snackbarColor.value = 'success'
      showSnackbar.value = true
    })
    .catch(err => {
      snackbarMessage.value = err.response?.data?.message || t('app.error.save')
      snackbarColor.value = 'error'
      showSnackbar.value = true
    })
}

onMounted(() => {
  isLoading.value = true

  api
    .get('/api/grade-boundaries')
    .then(response => {
      gradeBoundaries.value = response.data?.data || response.data || []
    })
    .catch(err => console.error(err))
    .finally(() => {
      isLoading.value = false
    })
})
</script>
