<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('questions.create') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8">
        <v-card variant="outlined">
          <v-card-text>
            <v-form ref="formRef" @submit.prevent="saveChanges">
              <v-text-field
                v-model="editedQuestion.question_text"
                :label="t('questions.questionText')"
                :rules="questionRules"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <v-select
                v-model="editedQuestion.category_id"
                :items="categories"
                item-title="title"
                item-value="id"
                :label="t('questions.category')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <div
                v-for="(answer, index) in editedQuestion.answers"
                :key="index"
              >
                <v-row>
                  <v-col cols="1" class="d-flex align-center">
                    <v-radio-group
                      v-model="correctAnswerIndex"
                      @update:model-value="updateCorrectAnswer"
                      hide-details
                    >
                      <v-radio
                        :label="`${t('questions.answer')} ${index + 1}`"
                        :value="index"
                      />
                    </v-radio-group>
                  </v-col>
                  <v-col cols="11">
                    <v-text-field
                      v-model="editedQuestion.answers[index].answer"
                      :label="`${t('questions.answer')} ${index + 1}`"
                      :rules="answerRules"
                      variant="outlined"
                      density="compact"
                      hide-details="auto"
                      class="mb-2"
                    >
                      <template v-slot:append>
                        <v-btn
                          v-if="showRemoveButtons"
                          icon
                          size="small"
                          color="error"
                          @click="removeAnswer(index)"
                        >
                          <v-icon>mdi-close</v-icon>
                        </v-btn>
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>
              </div>

              <v-row class="mt-3">
                <v-col cols="12">
                  <v-btn color="primary" class="mr-2" @click="addAnswer">
                    <v-icon start>mdi-plus</v-icon>
                    {{ t('questions.addAnswer') }}
                  </v-btn>
                  <v-btn color="error" class="mr-2" @click="toggleRemoveButtons">
                    {{ t('questions.removeAnswer') }}
                  </v-btn>
                  <v-btn
                    v-if="saveChangesButtons"
                    color="primary"
                    type="submit"
                    :loading="isSubmitting"
                  >
                    {{ t('app.common.save') }}
                  </v-btn>
                </v-col>
              </v-row>
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
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api.service'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const props = defineProps({
  categoryId: { type: [String, Number], default: null },
  aukstructureId: { type: [String, Number], default: null }
})

const formRef = ref(null)
const categories = ref([])
const showRemoveButtons = ref(false)
const saveChangesButtons = ref(false)
const correctAnswerIndex = ref(0)
const isSubmitting = ref(false)
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const questionRules = [
  v => !!v || t('app.validation.required'),
  v => (v && v.length >= 5) || t('app.validation.minLength', { length: 5 })
]

const answerRules = [
  v => !!v || t('app.validation.required'),
  v => (v && v.length >= 2) || t('app.validation.minLength', { length: 2 }),
  () => checkUniqueAnswers() || t('questions.answersUnique')
]

const editedQuestion = ref({
  question_text: '',
  category_id: props.categoryId || null,
  aukstructure_id: props.aukstructureId || null,
  answers: [
    { id: null, answer: '', is_correct: true, question_id: null }
  ]
})

function checkUniqueAnswers() {
  const answers = editedQuestion.value.answers.map(a => a.answer)
  return new Set(answers).size === answers.length
}

function updateCorrectAnswer() {
  editedQuestion.value.answers.forEach((answer, index) => {
    answer.is_correct = index === correctAnswerIndex.value
  })
}

function toggleRemoveButtons() {
  showRemoveButtons.value = !showRemoveButtons.value
}

function saveChanges() {
  formRef.value.validate().then(isValid => {
    if (!isValid.valid) return

    isSubmitting.value = true

    api
      .post('/api/questions', editedQuestion.value)
      .then(() => {
        snackbarMessage.value = t('questions.created')
        snackbarColor.value = 'success'
        showSnackbar.value = true
        router.push({ name: 'questions.bank' })
      })
      .catch(err => {
        snackbarMessage.value = err.response?.data?.message || t('app.error.create')
        snackbarColor.value = 'error'
        showSnackbar.value = true
      })
      .finally(() => {
        isSubmitting.value = false
      })
  })
}

function addAnswer() {
  editedQuestion.value.answers.push({
    id: null,
    answer: '',
    is_correct: false,
    question_id: editedQuestion.value.id,
    category_id: editedQuestion.value.category_id,
    aukstructure_id: editedQuestion.value.aukstructure_id
  })
  saveChangesButtons.value = true
}

function removeAnswer(index) {
  editedQuestion.value.answers.splice(index, 1)
  toggleRemoveButtons()
  saveChangesButtons.value = true
}

onMounted(() => {
  api
    .get('/api/categories')
    .then(response => {
      categories.value = response.data?.data || response.data || []
    })
    .catch(err => console.error(err))
})
</script>
