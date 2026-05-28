<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('files.upload') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8">
        <v-card variant="outlined" class="mb-4">
          <v-card-text>
            <div v-if="currentFile">
              <v-progress-linear
                v-model="progress"
                color="light-blue"
                height="25"
              >
                <strong>{{ progress }}%</strong>
              </v-progress-linear>
            </div>

            <v-row no-gutters align="center">
              <v-col cols="8">
                <v-file-input
                  v-model="currentFile"
                  show-size
                  :label="t('files.selectFile')"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                  @update:model-value="selectFile"
                />
              </v-col>
              <v-col cols="4" class="pl-2">
                <v-btn
                  color="success"
                  :disabled="!currentFile"
                  :loading="isUploading"
                  @click="upload"
                >
                  {{ t('files.upload') }}
                  <v-icon end>mdi-cloud-upload</v-icon>
                </v-btn>
              </v-col>
            </v-row>

            <v-alert
              v-if="message"
              border="left"
              color="blue-grey"
              dark
              class="mt-3"
            >
              {{ message }}
            </v-alert>
          </v-card-text>
        </v-card>

        <v-card v-if="fileInfos.length > 0" variant="outlined">
          <v-list>
            <v-subheader>{{ t('files.fileList') }}</v-subheader>
            <v-list-item
              v-for="(file, index) in fileInfos"
              :key="index"
            >
              <v-list-item-title>
                <a :href="file.url" target="_blank">{{ file.name }}</a>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api.service'

const { t } = useI18n()

const currentFile = ref(null)
const progress = ref(0)
const isUploading = ref(false)
const message = ref('')
const fileInfos = ref([])

function selectFile() {
  progress.value = 0
}

async function upload() {
  if (!currentFile.value) {
    message.value = t('files.selectFileFirst')
    return
  }

  message.value = ''
  isUploading.value = true

  const formData = new FormData()
  formData.append('file', currentFile.value)

  try {
    const response = await api.post('/api/files/add', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: event => {
        progress.value = Math.round((100 * event.loaded) / event.total)
      }
    })

    message.value = response.data?.message || t('files.uploadSuccess')
    await fetchFiles()
  } catch {
    progress.value = 0
    message.value = t('files.uploadError')
    currentFile.value = null
  } finally {
    isUploading.value = false
  }
}

async function fetchFiles() {
  try {
    const response = await api.get('/api/files')
    fileInfos.value = response.data?.data || response.data || []
  } catch {
    console.error('Error fetching files')
  }
}

onMounted(() => {
  fetchFiles()
})
</script>
