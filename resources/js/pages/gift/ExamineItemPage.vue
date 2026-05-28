<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('tests.title') }}</h1>
      </v-col>
    </v-row>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="questions.length === 0">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('tests.title') }}
        </v-alert>
      </v-col>
    </v-row>

    <template v-else>
      <v-card variant="outlined">
        <v-toolbar color="primary" density="compact">
          <v-toolbar-title>{{ t('tests.title') }} ({{ selectedAnswersLength }}/{{ questions.length }})</v-toolbar-title>
        </v-toolbar>

        <v-row no-gutters>
          <v-col cols="12" md="8">
            <v-sheet
              v-if="!isFinishExamClicked"
              elevation="1"
              color="grey-lighten-4"
              class="pa-4"
            >
              <h2 class="text-h6 mb-4">{{ currentQuestion.question_text }}</h2>
              <v-radio-group v-model="selectedAnswers[currentQuestion.id]">
                <v-radio
                  v-for="answer in currentQuestion.answers"
                  :key="answer.id"
                  :label="answer.answer"
                  :value="answer.id"
                />
              </v-radio-group>

              <v-btn
                v-if="selectedAnswersLength !== questions.length"
                color="primary"
                class="mt-3"
                @click="nextQuestion"
              >
                {{ t('tests.next') }}
              </v-btn>

              <v-btn
                v-if="selectedAnswersLength === questions.length && !isFinishExamClicked"
                color="primary"
                class="mt-3 ml-3"
                @click="finishExam"
              >
                {{ t('tests.finish') }}
              </v-btn>
            </v-sheet>

            <v-card v-if="isFinishExamClicked">
              <v-card-text class="text-center font-weight-bold text-h5">
                {{ t('tests.results') }}
              </v-card-text>
              <v-card-text>{{ t('tests.question') }}: {{ questions.length }}</v-card-text>
              <v-card-text>{{ t('tests.correctAnswer') }}: {{ correctAnswersCount }}</v-card-text>
              <v-card-text>{{ t('tests.wrongAnswer') }}: {{ incorrectAnswersCount }}</v-card-text>

              <div class="text-center pa-4">
                <v-progress-circular
                  :model-value="successPercentage"
                  :size="150"
                  :width="15"
                  :color="progressColor"
                >
                  <template #default>
                    <span class="text-h5">{{ successPercentage }}%</span>
                  </template>
                </v-progress-circular>
              </div>

              <h2 class="text-h5 text-center mb-4">
                {{ t('tests.grade') }}: {{ getGrade(successPercentage) }}
              </h2>
            </v-card>
          </v-col>

          <v-col cols="12" md="4">
            <v-sheet rounded elevation="1" color="grey-lighten-4" class="pa-2">
              <v-row>
                <v-col
                  v-for="(question, index) in displayedQuestions"
                  :key="index"
                  cols="3"
                >
                  <v-card
                    elevation="3"
                    class="text-center pa-1"
                    :style="{
                      backgroundColor: answerColors[question.id] || (selectedAnswers[question.id] !== undefined ? 'grey' : 'white'),
                      color: 'black',
                      cursor: 'pointer',
                      border: question.id === currentId ? '3px solid #1976d2' : 'none'
                    }"
                    @click="goToQuestion(question.id)"
                  >
                    <v-card-text class="pa-1 text-body-2">
                      {{ questionPosition(index) }}
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>

              <v-pagination
                v-model="page"
                :length="totalPages"
                class="mt-3"
                rounded="circle"
              />
            </v-sheet>
          </v-col>
        </v-row>
      </v-card>
    </template>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import api from '@/services/api.service'

const { t } = useI18n()
const route = useRoute()

const props = defineProps({
  idEdit: {
    type: Number,
    default: null
  },
  idCategory: {
    type: Number,
    default: null
  }
})

const isFinishExamClicked = ref(false)
const current = ref(-1)
const currentQuestion = ref({})
const questions = ref([])
const selectedAnswers = ref({})
const answerStatus = ref({})
const nextId = ref(0)
const currentId = ref(0)
const answerColors = ref({})
const correctAnswersCount = ref(0)
const incorrectAnswersCount = ref(0)
const page = ref(1)
const pageSize = ref(12)
const gradeBoundaries = ref([])
const isLoading = ref(false)
const error = ref(null)

const selectedAnswersLength = computed(() =>
  Object.keys(selectedAnswers.value).length
)

const successPercentage = computed(() =>
  questions.value.length
    ? Math.round((correctAnswersCount.value / questions.value.length) * 100)
    : 0
)

const progressColor = computed(() => {
  if (successPercentage.value >= 70) return 'green'
  if (successPercentage.value >= 40) return 'orange'
  return 'red'
})

const totalPages = computed(() =>
  Math.ceil(questions.value.length / pageSize.value)
)

const displayedQuestions = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return questions.value.slice(start, start + pageSize.value)
})

function questionPosition(index) {
  return (page.value - 1) * pageSize.value + index + 1
}

function getGrade(percentage) {
  for (let i = gradeBoundaries.value.length - 1; i >= 0; i--) {
    if (percentage >= gradeBoundaries.value[i].boundary) {
      return gradeBoundaries.value[i].grade
    }
  }
  return 'N/A'
}

function goToQuestion(id) {
  currentId.value = id
  api
    .get(`/api/questions/${id}`)
    .then(response => {
      currentQuestion.value = response.data?.data || response.data
    })
    .catch(err => console.error(err))

  if (id < questions.value.length) {
    nextId.value = id + 1
  } else {
    nextId.value = id
  }
}

function nextQuestion() {
  goToQuestion(nextId.value)
  page.value = Math.ceil(nextId.value / pageSize.value)
}

function finishExam() {
  checkAnswers()
  currentId.value = null
  isFinishExamClicked.value = true
}

function checkAnswers() {
  correctAnswersCount.value = 0
  incorrectAnswersCount.value = 0

  questions.value.forEach(question => {
    const selectedAnswerId = selectedAnswers.value[question.id]
    const correctAnswer = question.answers.find(a => a.is_correct)
    if (!correctAnswer) return

    if (selectedAnswerId === correctAnswer.id) {
      answerStatus.value[question.id] = t('tests.correctAnswer')
      answerColors.value[question.id] = 'green'
      correctAnswersCount.value++
    } else {
      answerStatus.value[question.id] = t('tests.wrongAnswer')
      answerColors.value[question.id] = 'red'
      incorrectAnswersCount.value++
    }
  })
}

onMounted(() => {
  const courseId = props.idEdit || parseInt(route.query.courseId)
  const categoryId = props.idCategory || parseInt(route.query.categoryId)

  if (courseId && categoryId) {
    isLoading.value = true
    api
      .get('/api/questions', {
        params: { aukstructure_id: courseId, category_id: categoryId }
      })
      .then(response => {
        questions.value = response.data?.data || response.data || []
        if (questions.value.length > 0) {
          goToQuestion(questions.value[0].id)
        }
      })
      .catch(err => {
        error.value = err.response?.data?.message || t('app.errors.fetch')
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  api
    .get('/api/grade-boundaries')
    .then(response => {
      gradeBoundaries.value = response.data?.data || response.data || []
    })
    .catch(err => console.error(err))
})
</script>
