<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('questions.edit') }} #{{ id }}</h1>
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
      <v-col cols="12" md="8">
        <v-card variant="outlined">
          <v-card-text>
            <v-form @submit.prevent="saveChanges">
              <v-text-field
                v-model="editedQuestion.question_text"
                :label="t('questions.questionText')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
                @update:model-value="checkChanges"
              />

              <div
                v-for="(answer, index) in editedQuestion.answers"
                :key="index"
              >
                <v-row>
                  <v-col cols="1" class="d-flex align-center">
                    <v-radio-group v-model="correctAnswerIndex" hide-details>
                      <v-radio
                        :label="`${t('questions.answer')} ${index + 1}`"
                        :value="index"
                        @update:model-value="checkChanges"
                      />
                    </v-radio-group>
                  </v-col>
                  <v-col cols="11">
                    <v-text-field
                      v-model="editedQuestion.answers[index].answer"
                      :label="`${t('questions.answer')} ${index + 1}`"
                      variant="outlined"
                      density="compact"
                      hide-details="auto"
                      class="mb-2"
                      @update:model-value="checkChanges"
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
  id: { type: [String, Number], required: true }
})

const isLoading = ref(false)
const error = ref(null)
const editedQuestion = ref({
  question_text: '',
  answers: []
})
const correctAnswerIndex = ref(null)
const showRemoveButtons = ref(false)
const saveChangesButtons = ref(false)
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

function checkChanges() {
  saveChangesButtons.value = true
}

function toggleRemoveButtons() {
  showRemoveButtons.value = !showRemoveButtons.value
}

function saveChanges() {
  editedQuestion.value.answers.forEach((answer, index) => {
    answer.is_correct = correctAnswerIndex.value === index
  })

  api
    .patch(`/api/questions/${editedQuestion.value.id}`, editedQuestion.value)
    .then(() => {
      saveChangesButtons.value = false
      snackbarMessage.value = t('app.common.saved')
      snackbarColor.value = 'success'
      showSnackbar.value = true
      router.push({ name: 'questions.bank' })
    })
    .catch(err => {
      snackbarMessage.value = err.response?.data?.message || t('app.error.save')
      snackbarColor.value = 'error'
      showSnackbar.value = true
    })
}

function addAnswer() {
  editedQuestion.value.answers.push({
    id: null,
    answer: '',
    is_correct: false,
    question_id: editedQuestion.value.id
  })
  saveChangesButtons.value = true
}

function removeAnswer(index) {
  if (correctAnswerIndex.value === index) {
    snackbarMessage.value = t('questions.cannotRemoveCorrect')
    snackbarColor.value = 'error'
    showSnackbar.value = true
    return
  }
  editedQuestion.value.answers.splice(index, 1)
  toggleRemoveButtons()
  saveChangesButtons.value = true
}

onMounted(() => {
  isLoading.value = true

  api
    .get('/api/questions', { params: { id: props.id } })
    .then(response => {
      const data = response.data
      editedQuestion.value = Array.isArray(data) ? data[0] : data
      correctAnswerIndex.value = editedQuestion.value.answers.findIndex(
        a => a.is_correct
      )
    })
    .catch(err => {
      error.value = err.response?.data?.message || t('app.error.fetch')
    })
    .finally(() => {
      isLoading.value = false
    })
})
</script>
