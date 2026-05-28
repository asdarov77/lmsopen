<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('questions.examine') }}</h1>
      </v-col>
    </v-row>

    <v-row v-if="!currentCategory || !currentAukstructure">
      <v-col cols="12" md="6">
        <v-card variant="outlined">
          <v-card-text>
            <v-select
              v-model="currentCategory"
              :items="categories"
              item-title="title"
              item-value="id"
              :label="t('questions.category')"
              variant="outlined"
              density="compact"
              class="mb-3"
              clearable
              @update:model-value="onCategoryChange"
            />
            <v-select
              v-model="currentAukstructure"
              :items="aukstructures"
              item-title="title"
              item-value="id"
              :label="t('questions.topic')"
              variant="outlined"
              density="compact"
              :disabled="!currentCategory"
              clearable
              @update:model-value="fetchQuestions"
            />
          </v-card-text>
        </v-card>
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

    <template v-else-if="questions.length > 0">
      <v-row>
        <v-col cols="12">
          <v-card variant="outlined">
            <v-card-text>
              <div class="text-subtitle-1 mb-2">
                {{ t('questions.question') }} {{ currentIndex + 1 }} / {{ questions.length }}
              </div>
              <v-divider class="mb-4" />
              <div class="text-body-1 mb-4">
                {{ currentQuestion.question_text }}
              </div>
              <v-radio-group v-model="selectedAnswer">
                <v-radio
                  v-for="(answer, idx) in currentQuestion.answers"
                  :key="idx"
                  :label="answer.text"
                  :value="answer.id"
                />
              </v-radio-group>
            </v-card-text>
            <v-card-actions>
              <v-btn
                variant="tonal"
                :disabled="currentIndex === 0"
                @click="prevQuestion"
              >
                <v-icon start>mdi-chevron-left</v-icon>
                {{ t('app.common.previous') }}
              </v-btn>
              <v-spacer />
              <v-btn
                color="primary"
                variant="tonal"
                :disabled="currentIndex === questions.length - 1"
                @click="nextQuestion"
              >
                {{ t('app.common.next') }}
                <v-icon end>mdi-chevron-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </template>

    <v-row v-else-if="currentCategory && currentAukstructure">
      <v-col cols="12" class="text-center">
        <v-alert type="info" variant="tonal">
          {{ t('questions.noQuestions') }}
        </v-alert>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import api from '@/services/api.service';

const { t } = useI18n();
const route = useRoute();

const props = defineProps({
  idEdit: {
    type: [String, Number],
    default: null
  },
  idCategory: {
    type: [String, Number],
    default: null
  }
});

const isLoading = ref(false);
const error = ref(null);
const categories = ref([]);
const aukstructures = ref([]);
const questions = ref([]);
const currentCategory = ref(props.idCategory || null);
const currentAukstructure = ref(props.idEdit || null);
const selectedAnswer = ref(null);
const currentIndex = ref(0);

const currentQuestion = computed(() => questions.value[currentIndex.value] || {});

function onCategoryChange() {
  currentAukstructure.value = null;
  questions.value = [];
  currentIndex.value = 0;
  if (currentCategory.value) {
    fetchAukstructures();
  }
}

async function fetchCategories() {
  try {
    const response = await api.get('/api/categories');
    categories.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Error fetching categories:', err);
  }
}

async function fetchAukstructures() {
  if (!currentCategory.value) return;

  try {
    const response = await api.get('/api/questions', {
      params: { category_id: currentCategory.value }
    });
    const data = response.data?.data || response.data || [];
    const unique = data.reduce((acc, q) => {
      if (q.aukstructure_id && !acc.some(a => a.id === q.aukstructure_id)) {
        acc.push({ id: q.aukstructure_id, title: q.title });
      }
      return acc;
    }, []);
    aukstructures.value = unique;
  } catch (err) {
    console.error('Error fetching aukstructures:', err);
  }
}

async function fetchQuestions() {
  if (!currentCategory.value || !currentAukstructure.value) return;

  isLoading.value = true;
  error.value = null;
  currentIndex.value = 0;

  try {
    const response = await api.get('/api/questions', {
      params: {
        category_id: currentCategory.value,
        aukstructure_id: currentAukstructure.value
      }
    });
    questions.value = response.data?.data || response.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || t('app.errors.fetch');
  } finally {
    isLoading.value = false;
  }
}

function nextQuestion() {
  if (currentIndex.value < questions.value.length - 1) {
    currentIndex.value++;
    selectedAnswer.value = null;
  }
}

function prevQuestion() {
  if (currentIndex.value > 0) {
    currentIndex.value--;
    selectedAnswer.value = null;
  }
}

onMounted(() => {
  fetchCategories();
  if (props.idCategory) {
    fetchAukstructures();
  }
  if (props.idEdit && props.idCategory) {
    fetchQuestions();
  }
});
</script>
