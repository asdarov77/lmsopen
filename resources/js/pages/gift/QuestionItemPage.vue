<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="8">
        <v-card variant="outlined">
          <v-card-text v-if="!question.id">
            <v-progress-circular indeterminate color="primary" />
          </v-card-text>

          <v-card-text v-else>
            <h2 class="text-h6 mb-4">{{ question.question_text }}</h2>
            <v-radio-group v-model="selectedAnswers[question.id]">
              <v-radio
                v-for="answer in question.answers"
                :key="answer.id"
                :label="answer.answer"
                :value="answer.id"
              />
            </v-radio-group>

            <v-btn color="primary" class="mt-3" @click="nextQuestion">
              {{ t('tests.next') }}
            </v-btn>
            <v-btn color="success" class="mt-3 ml-3" @click="submitTest">
              {{ t('tests.finish') }}
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
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
  idEdit: { type: [String, Number], default: null }
})

const question = ref({})
const selectedAnswers = ref({})
const answerStatus = ref({})

function nextQuestion() {
  const nextId = parseInt(props.idEdit || route.params.idEdit) + 1

  router.push({ name: 'questions.item', params: { idEdit: nextId } })

  api
    .get(`/api/questions/${nextId}`)
    .then(response => {
      question.value = response.data
    })
    .catch(err => console.error(err))
}

function submitTest() {
  const studentAnswers = []
  for (const questionId in selectedAnswers.value) {
    studentAnswers.push({
      question_id: questionId,
      answer_id: selectedAnswers.value[questionId]
    })
  }

  api
    .post('/api/student-answers', { studentAnswers })
    .then(() => {
      router.push({ name: 'tests.results' })
    })
    .catch(err => console.error(err))
}

onMounted(() => {
  const id = props.idEdit || route.params.idEdit

  if (id) {
    api
      .get(`/api/questions/${id}`)
      .then(response => {
        question.value = response.data
      })
      .catch(err => console.error(err))
  }
})
</script>
