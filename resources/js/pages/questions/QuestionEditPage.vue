<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('questions.edit') }}</h1>
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
  id: {
    type: [String, Number],
    required: true
  }
});

const formRef = ref(null);
const isLoading = ref(false);
const isSubmitting = ref(false);
const error = ref(null);
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
  category_id: null,
  aukstructure_id: null,
  answers: []
});

function addAnswer() {
  form.value.answers.push({ text: '', is_correct: false });
}

function removeAnswer(idx) {
  if (form.value.answers.length > 1) {
    form.value.answers.splice(idx, 1);
  }
}

async function fetchQuestion() {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await api.get(`/api/questions/${props.id}`);
    const data = response.data;
    form.value.question_text = data.question_text || '';
    form.value.type = data.type || 'single';
    form.value.category_id = data.category_id || null;
    form.value.aukstructure_id = data.aukstructure_id || null;
    form.value.answers = (data.answers || []).map(a => ({
      text: a.text || '',
      is_correct: !!a.is_correct
    }));
  } catch (err) {
    error.value = err.response?.data?.message || t('app.error.fetch');
  } finally {
    isLoading.value = false;
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
    await api.put(`/api/questions/${props.id}`, form.value);
    snackbarMessage.value = t('questions.updated');
    snackbarColor.value = 'success';
    showSnackbar.value = true;
    router.push({ name: 'questions.bank' });
  } catch (err) {
    snackbarMessage.value = err.response?.data?.message || t('app.error.update');
    snackbarColor.value = 'error';
    showSnackbar.value = true;
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchCategories();
  fetchQuestion();
});
</script>
