<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('questions.bank') }}</h1>
      </v-col>
    </v-row>

    <v-card variant="outlined" class="mb-4">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-select
              v-model="currentCategory"
              :items="categories"
              item-title="title"
              item-value="id"
              :label="t('questions.category')"
              variant="outlined"
              density="compact"
              hide-details="auto"
              @update:model-value="onCategoryChange"
            />
          </v-col>
          <v-col cols="12" md="6">
            <v-select
              v-model="currentAukTheme"
              :items="auk"
              item-title="title"
              item-value="aukstructure_id"
              :label="t('questions.topic')"
              variant="outlined"
              density="compact"
              hide-details="auto"
              :disabled="!currentCategory"
              @update:model-value="getQuestions"
            />
          </v-col>
        </v-row>
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

    <template v-if="questions.length > 0">
      <v-card variant="outlined">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">#</th>
              <th class="text-left">{{ t('questions.questionText') }}</th>
              <th class="text-left">{{ t('app.common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in displayedQuestions" :key="index">
              <td>{{ (page - 1) * pageSize + index + 1 }}</td>
              <td>{{ item.question_text }}</td>
              <td>
                <v-btn
                  size="small"
                  color="green"
                  variant="tonal"
                  class="mr-2"
                  :to="{ name: 'questions.edit', params: { id: item.id } }"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  size="small"
                  color="red"
                  variant="tonal"
                  @click="deleteItem(item.id)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <v-row class="mt-3">
        <v-col cols="2">
          <v-btn
            color="primary"
            :to="{
              name: 'questions.create',
              params: {
                categoryId: currentCategory,
                aukstructureId: currentAukTheme
              }
            }"
          >
            <v-icon start>mdi-plus</v-icon>
            {{ t('questions.add') }}
          </v-btn>
        </v-col>
        <v-col cols="9">
          <v-pagination
            v-model="page"
            :length="totalPages"
            class="mt-1"
            rounded="circle"
          />
        </v-col>
        <v-col cols="1">
          <v-select
            v-model="pageSize"
            :items="[10, 20, 50, 100]"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCourseStore } from '@/stores/course.store'
import api from '@/services/api.service'

const { t } = useI18n()
const courseStore = useCourseStore()

const categories = computed(() => courseStore.categories)

const themesOfQuestions = ref([])
const aukstructures = ref([])
const questions = ref([])
const currentCategory = ref(null)
const currentAukTheme = ref(null)
const auk = ref([])
const page = ref(1)
const pageSize = ref(10)
const isLoading = ref(false)
const error = ref(null)

const totalPages = computed(() =>
  Math.ceil(questions.value.length / pageSize.value)
)

const displayedQuestions = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return questions.value.slice(start, start + pageSize.value)
})

function onCategoryChange() {
  currentAukTheme.value = null
  questions.value = []
  getThemes()
}

function getThemes() {
  if (!currentCategory.value) return

  api
    .get('/api/questions', {
      params: { category_id: currentCategory.value }
    })
    .then(response => {
      const data = response.data?.data || response.data || []
      const unique = data
        .filter(q => q.category_id === currentCategory.value)
        .reduce((acc, q) => {
          if (!acc.find(a => a.title === q.title)) {
            acc.push({ title: q.title, aukstructure_id: q.aukstructure_id })
          }
          return acc
        }, [])
      auk.value = unique
    })
    .catch(err => console.error(err))
}

function getQuestions() {
  if (!currentCategory.value || !currentAukTheme.value) return

  isLoading.value = true
  error.value = null

  api
    .get('/api/questions', {
      params: {
        category_id: currentCategory.value,
        aukstructure_id: currentAukTheme.value
      }
    })
    .then(response => {
      questions.value = response.data?.data || response.data || []
    })
    .catch(err => {
      error.value = err.response?.data?.message || t('app.errors.fetch')
    })
    .finally(() => {
      isLoading.value = false
    })
}

async function deleteItem(id) {
  if (!confirm(t('app.common.confirmDelete'))) return

  try {
    await api.delete(`/api/questions/${id}`)
    await getQuestions()
  } catch (err) {
    console.error(err)
  }
}

onMounted(() => {
  courseStore.fetchCategories()
  api
    .get('/api/aukstructure')
    .then(response => {
      aukstructures.value = response.data || []
    })
    .catch(err => console.error(err))
})
</script>
