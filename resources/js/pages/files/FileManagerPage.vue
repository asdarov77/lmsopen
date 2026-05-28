<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('app.files') }}</h1>
        <v-btn variant="tonal" @click="goback" :disabled="!parent">
          <v-icon start>mdi-arrow-left</v-icon>
          {{ t('app.common.back') }}
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

    <v-row v-else-if="subDirectories.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('files.noItems') }}
        </v-alert>
      </v-col>
    </v-row>

    <template v-else>
      <v-row>
        <v-col cols="12">
          <v-card variant="outlined">
            <v-table>
              <thead>
                <tr>
                  <th class="text-left">#</th>
                  <th class="text-left">{{ t('files.name') }}</th>
                  <th class="text-left">{{ t('files.type') }}</th>
                  <th class="text-left">{{ t('app.common.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in subDirectories" :key="index">
                  <td>{{ index + 1 }}</td>
                  <td>{{ item }}</td>
                  <td>
                    <v-chip size="small" variant="tonal" :color="isHtmlFile(item) ? 'primary' : 'secondary'">
                      {{ isHtmlFile(item) ? 'HTML' : t('files.folder') }}
                    </v-chip>
                  </td>
                  <td>
                    <v-btn
                      size="small"
                      variant="tonal"
                      color="primary"
                      @click="openItem(item)"
                    >
                      <v-icon start>
                        {{ isHtmlFile(item) ? 'mdi-file-eye' : 'mdi-folder-open' }}
                      </v-icon>
                      {{ isHtmlFile(item) ? t('app.common.open') : t('files.open') }}
                    </v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card>
        </v-col>
      </v-row>

      <v-row class="mt-2">
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-text class="text-caption text-medium-emphasis">
              {{ t('files.currentPath') }}: {{ parent || t('files.root') }}
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api.service'

const { t } = useI18n()

const subDirectories = ref([])
const parent = ref('')
const prevParent = ref('')
const prevNameLyx = ref('')
const isLoading = ref(false)
const error = ref(null)

function isHtmlFile(name) {
  return name.endsWith('.html') || name.endsWith('.htm')
}

function openItem(name) {
  if (isHtmlFile(name)) {
    const url = parent.value + '/' + name
    window.open(url, '_blank')
    return
  }
  showLyx(name)
}

function showLyx(nameLyx) {
  prevParent.value = parent.value
  prevNameLyx.value = nameLyx

  const formData = { nameLyx, parent: parent.value }
  isLoading.value = true
  error.value = null

  api
    .post('/api/tree/list', formData)
    .then(response => {
      subDirectories.value = response.data.folders || []
      parent.value = response.data.fullpath || ''
    })
    .catch(err => {
      error.value = err.response?.data?.message || t('app.error.fetch')
    })
    .finally(() => {
      isLoading.value = false
    })
}

function goback() {
  if (!prevParent.value) return
  parent.value = prevParent.value
  showLyx(prevNameLyx.value)
}

function fetchRoot() {
  isLoading.value = true
  error.value = null

  api
    .get('/api/tree')
    .then(response => {
      subDirectories.value = response.data.subfolders || []
      parent.value = response.data.course_root || ''
    })
    .catch(err => {
      error.value = err.response?.data?.message || t('app.error.fetch')
    })
    .finally(() => {
      isLoading.value = false
    })
}

onMounted(() => {
  fetchRoot()
})
</script>
