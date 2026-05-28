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
            <v-form ref="formRef" @submit.prevent="submit">
              <v-text-field
                v-model="form.question_text"
                :label="t('questions.questionText')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <v-select
                v-model="form.type"
                :items="questionTypes"
                :label="t('questions.type')"
                :rules="[v => !!v || t('app.validation.required')]"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <v-select
                v-model="form.category_id"
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

              <v-text-field
                v-model="form.aukstructure_id"
                :label="t('questions.aukstructureId')"
                type="number"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />

              <div class="mb-3">
                <div class="text-subtitle-2 mb-2">{{ t('questions.answers') }}</div>
                <div
                  v-for="(answer, idx) in form.answers"
                  :key="idx"
                  class="d-flex align-center mb-2"
                >
                  <v-text-field
                    v-model="answer.text"
                    :label="`${t('questions.answer')} ${idx + 1}`"
                    variant="outlined"
                    density="compact"
                    hide-details
                    class="mr-2"
                  />
                  <v-checkbox
                    v-model="answer.is_correct"
                    :label="t('questions.correct')"
                    hide-details
                  />
                  <v-btn
                    icon
                    size="small"
                    color="error"
                    variant="text"
                    @click="removeAnswer(idx)"
                  >
                    <v-icon>mdi-close</v-icon>
                  </v-btn>
                </div>
                <v-btn
                  size="small"
                  variant="tonal"
                  @click="addAnswer"
                >
                  <v-icon start>mdi-plus</v-icon>
                  {{ t('questions.addAnswer') }}
                </v-btn>
              </div>

              <v-btn
                color="primary"
                type="submit"
                :loading="isSubmitting"
                :disabled="isSubmitting"
              >
                {{ t('app.common.save') }}
              </v-btn>
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
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api.service';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const props = defineProps({
  categoryId: {
    type: [String, Number],
    default: null
  },
  aukstructureId: {
    type: [String, Number],
    default: null
  }
});

const formRef = ref(null);
const isSubmitting = ref(false);
const categories = ref([]);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

const questionTypes = ref([
  { title: t('questions.typeSingle'), value: 'single' },
  { title: t('questions.typeMultiple'), value: 'multiple' },
  { title: t('questions.typeTrueFalse'), value: 'true_false' }
]);

const form = ref({
  question_text: '',
  type: 'single',
  category_id: props.categoryId || null,
  aukstructure_id: props.aukstructureId || null,
  answers: [
    { text: '', is_correct: false },
    { text: '', is_correct: false }
  ]
});

function addAnswer() {
  form.value.answers.push({ text: '', is_correct: false });
}

function removeAnswer(idx) {
  if (form.value.answers.length > 1) {
    form.value.answers.splice(idx, 1);
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

async function submit() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isSubmitting.value = true;

  try {
    await api.post('/api/questions', form.value);
    snackbarMessage.value = t('questions.created');
    snackbarColor.value = 'success';
    showSnackbar.value = true;
    router.push({ name: 'questions.bank' });
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.create');
    snackbarColor.value = 'error';
    showSnackbar.value = true;
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchCategories();
});
</script>
