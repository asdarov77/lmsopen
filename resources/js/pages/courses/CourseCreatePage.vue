<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('courses.create') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8" lg="6">
        <v-card variant="outlined">
          <v-card-title>{{ t('courses.create') }}</v-card-title>
          <v-card-text>
            <v-form ref="formRef">
              <v-text-field
                v-model="form.title"
                :label="t('courses.title')"
                :rules="[rules.required]"
                variant="outlined"
              />

              <v-text-field
                v-model="form.short_description"
                :label="t('courses.shortDescription')"
                variant="outlined"
              />

              <v-textarea
                v-model="form.long_description"
                :label="t('courses.longDescription')"
                variant="outlined"
              />

              <v-select
                v-model="form.category_id"
                :label="t('courses.category')"
                :items="courseStore.categories"
                item-value="id"
                item-title="title"
                variant="outlined"
                multiple
              />

              <v-text-field
                v-model="form.path"
                :label="t('courses.path')"
                variant="outlined"
              />

              <v-alert
                v-if="error"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="error = ''"
              >
                {{ error }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-btn color="error" variant="text" @click="cancel">
              {{ t('app.common.cancel') }}
            </v-btn>
            <v-spacer />
            <v-btn
              color="primary"
              :loading="saving"
              :disabled="saving"
              @click="submitForm"
            >
              {{ t('app.common.save') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="3000"
    >
      {{ snackbar.text }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useCourseStore } from '@/stores/course.store';

const router = useRouter();
const { t } = useI18n();
const courseStore = useCourseStore();

const formRef = ref(null);
const saving = ref(false);
const error = ref('');

const form = reactive({
  title: '',
  short_description: '',
  long_description: '',
  category_id: [],
  path: ''
});

const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
});

const rules = {
  required: value => !!value || t('app.validation.required')
};

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  saving.value = true;
  error.value = '';

  try {
    await courseStore.createCourse({
      title: form.title,
      short_description: form.short_description,
      long_description: form.long_description,
      path: form.path,
      category_id: form.category_id
    });
    snackbar.text = t('courses.createSuccess');
    snackbar.color = 'success';
    snackbar.show = true;
    setTimeout(() => router.push({ name: 'courses.list' }), 1000);
  } catch (err) {
    error.value = err.response?.data?.message || t('courses.createError');
  } finally {
    saving.value = false;
  }
}

function cancel() {
  router.push({ name: 'courses.list' });
}

onMounted(() => {
  if (courseStore.categories.length === 0) {
    courseStore.fetchCategories();
  }
});
</script>
