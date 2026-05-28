<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('users.changePassword') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card variant="outlined">
          <v-card-title>{{ t('users.changePasswordFor') }} {{ form.fio }}</v-card-title>

          <v-card-text>
            <v-form ref="formRef">
              <v-text-field
                v-model="form.password"
                :label="t('auth.password')"
                prepend-icon="mdi-lock"
                :type="showPassword ? 'text' : 'password'"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
                :rules="[rules.required, rules.minLength]"
                variant="outlined"
              />

              <v-text-field
                v-model="form.password_confirmation"
                :label="t('auth.passwordConfirmation')"
                prepend-icon="mdi-lock"
                :type="showPassword ? 'text' : 'password'"
                :rules="[rules.required, rules.matchPassword]"
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
            <v-btn
              color="error"
              variant="text"
              @click="cancel"
            >
              {{ t('app.common.cancel') }}
            </v-btn>
            <v-spacer />
            <v-btn
              color="primary"
              :loading="isLoading"
              :disabled="isLoading"
              @click="handleSave"
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
import api from '@/services/api.service';

const props = defineProps({
  id: { type: [String, Number], required: true }
});

const router = useRouter();
const { t } = useI18n();

const formRef = ref(null);
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref('');

const form = reactive({
  fio: '',
  password: '',
  password_confirmation: ''
});

const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
});

const rules = {
  required: value => !!value || t('app.validation.required'),
  minLength: value => (value && value.length >= 6) || t('app.validation.minLength', { min: 6 }),
  matchPassword: value => value === form.password || t('app.validation.passwordMismatch')
};

async function fetchUser() {
  try {
    const response = await api.get(`/api/users/${props.id}`);
    form.fio = response.data.data?.fio || '';
  } catch (err) {
    console.error('Error fetching user:', err);
  }
}

async function handleSave() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isLoading.value = true;
  error.value = '';

  try {
    await api.put(`/api/users/${props.id}/password`, {
      password: form.password,
      password_confirmation: form.password_confirmation
    });
    snackbar.text = t('users.passwordChangeSuccess');
    snackbar.color = 'success';
    snackbar.show = true;
    setTimeout(() => router.push({ name: 'users.list' }), 1000);
  } catch (err) {
    error.value = err.response?.data?.message || t('users.passwordChangeError');
  } finally {
    isLoading.value = false;
  }
}

function cancel() {
  router.push({ name: 'users.list' });
}

onMounted(fetchUser);
</script>

<style scoped>
</style>
