<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card elevation="12">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>{{ t('auth.register') }}</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-form ref="formRef" @submit.prevent="handleRegister">
              <v-text-field
                v-model="form.fio"
                :label="t('auth.fio')"
                prepend-icon="mdi-account"
                :rules="[rules.required]"
                variant="outlined"
              />

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
            <v-spacer />
            <v-btn
              color="primary"
              :loading="isLoading"
              :disabled="isLoading"
              @click="handleRegister"
            >
              {{ t('auth.register') }}
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
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const formRef = ref(null);
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref('');

const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
});

const form = reactive({
  fio: '',
  password: '',
  password_confirmation: ''
});

const rules = {
  required: value => !!value || t('app.validation.required'),
  minLength: value => (value && value.length >= 6) || t('app.validation.minLength', { min: 6 }),
  matchPassword: value => value === form.password || t('app.validation.passwordMismatch')
};

async function handleRegister() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  isLoading.value = true;
  error.value = '';

  try {
    await authStore.register({
      fio: form.fio,
      password: form.password,
      password_confirmation: form.password_confirmation
    });
    snackbar.text = t('auth.registerSuccess');
    snackbar.color = 'success';
    snackbar.show = true;
    setTimeout(() => router.push('/login'), 1500);
  } catch (err) {
    error.value = err.response?.data?.message || t('auth.registerError');
  } finally {
    isLoading.value = false;
  }
}
</script>

<style scoped>
</style>
