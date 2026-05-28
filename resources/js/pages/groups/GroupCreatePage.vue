<template>
  <v-container fluid>
    <v-row justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card elevation="12">
          <v-toolbar color="primary">
            <v-toolbar-title>{{ t('groups.createTitle') }}</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-form ref="formRef" @submit.prevent="submitForm">
              <v-text-field
                v-model="form.name"
                :label="t('groups.name')"
                :rules="[rules.required]"
                variant="outlined"
                class="mb-4"
              />

              <v-textarea
                v-model="form.description"
                :label="t('groups.description')"
                variant="outlined"
                rows="3"
              />

              <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="errorMessage = ''"
              >
                {{ errorMessage }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer />
            <v-btn
              color="error"
              variant="text"
              @click="cancelBtn"
            >
              {{ t('common.cancel') }}
            </v-btn>
            <v-btn
              color="primary"
              :loading="isSubmitting"
              :disabled="isSubmitting"
              @click="submitForm"
            >
              {{ t('common.save') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const router = useRouter();
const { t } = useI18n();

const formRef = ref(null);
const isSubmitting = ref(false);
const errorMessage = ref('');

const form = reactive({
  name: '',
  description: ''
});

const rules = {
  required: value => !!value || t('app.validation.required')
};

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isSubmitting.value = true;
  errorMessage.value = '';

  try {
    await api.post('/api/groups', {
      groupname: form.name,
      groupdescription: form.description
    });
    router.push({ name: 'groups.list' });
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('errors.saveFailed');
  } finally {
    isSubmitting.value = false;
  }
}

function cancelBtn() {
  router.push({ name: 'groups.list' });
}
</script>
