<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('users.editTitle') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8" lg="6">
        <v-progress-circular
          v-if="isLoading"
          indeterminate
          color="primary"
          class="d-block mx-auto my-8"
        />

        <v-alert
          v-else-if="loadError"
          type="error"
          variant="tonal"
          closable
          @click:close="loadError = ''"
        >
          {{ loadError }}
        </v-alert>

        <v-card v-else variant="outlined">
          <v-card-title>{{ t('users.editUser') }}: {{ form.fio }}</v-card-title>

          <v-card-text>
            <v-form ref="formRef">
              <v-text-field
                v-model="form.fio"
                :label="t('users.fio')"
                :rules="[rules.required]"
                variant="outlined"
              />

              <v-select
                v-model="form.role"
                :label="t('users.role')"
                :items="roleOptions"
                :rules="[rules.required]"
                variant="outlined"
              />

              <v-text-field
                v-model="form.phonenumber"
                :label="t('users.phonenumber')"
                variant="outlined"
              />

              <v-text-field
                v-model="form.city"
                :label="t('users.city')"
                variant="outlined"
              />

              <v-text-field
                v-model="form.country"
                :label="t('users.country')"
                variant="outlined"
              />

              <v-text-field
                v-model="form.organization"
                :label="t('users.organization')"
                variant="outlined"
              />

              <v-text-field
                v-model="form.position"
                :label="t('users.position')"
                variant="outlined"
              />

              <v-select
                v-model="form.rank"
                :label="t('users.rank')"
                :items="rankOptions"
                variant="outlined"
              />

              <v-combobox
                v-model="form.spfere"
                :label="t('users.spfere')"
                :items="sfereOptions"
                variant="outlined"
              />

              <v-text-field
                v-model="form.specialization"
                :label="t('users.specialization')"
                variant="outlined"
              />

              <v-select
                v-model="form.group_id"
                :label="t('users.group')"
                :items="groups"
                item-value="id"
                item-title="groupname"
                variant="outlined"
                clearable
              />

              <v-select
                v-model="form.role_id"
                :label="t('users.selectRoles')"
                :items="allRoles"
                item-value="id"
                item-title="name"
                variant="outlined"
                multiple
                clearable
                class="mb-3"
              />

              <v-select
                v-model="form.permission_id"
                :label="t('users.permissions')"
                :items="permissions"
                item-value="id"
                item-title="name"
                variant="outlined"
                multiple
                clearable
              />

              <v-card variant="tonal" class="mt-4 pa-3">
                <v-card-title class="text-body-1 font-weight-bold">
                  {{ t('users.effectivePermissions') }}
                </v-card-title>
                <v-card-text>
                  <v-chip
                    v-for="perm in effectivePermissions"
                    :key="perm.id"
                    size="x-small"
                    color="info"
                    variant="tonal"
                    class="mr-1 mb-1"
                  >
                    {{ perm.name }}
                  </v-chip>
                  <span v-if="effectivePermissions.length === 0">—</span>
                </v-card-text>
              </v-card>

              <v-alert
                v-if="saveError"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="saveError = ''"
              >
                {{ saveError }}
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
              :loading="saving"
              :disabled="saving"
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
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import api from '@/services/api.service';

const props = defineProps({
  id: { type: [String, Number], required: true }
});

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const formRef = ref(null);
const isLoading = ref(true);
const saving = ref(false);
const loadError = ref('');
const saveError = ref('');

const groups = ref([]);
const permissions = ref([]);
const allRoles = ref([]);

const roleOptions = ['Администратор', 'Инструктор', 'Обучаемый'];
const rankOptions = ['капитан', 'майор', 'старший лейтенант', 'лейтенант'];
const sfereOptions = [];

const form = reactive({
  fio: '',
  role: '',
  phonenumber: '',
  city: '',
  country: '',
  organization: '',
  position: '',
  rank: '',
  spfere: '',
  specialization: '',
  group_id: null,
  role_id: [],
  permission_id: []
});

const effectivePermissions = computed(() => {
  const directIds = new Set(form.permission_id);
  const rolePerms = allRoles.value
    .filter(r => form.role_id.includes(r.id))
    .flatMap(r => r.permissions || []);
  const merged = [...rolePerms];
  const seen = new Set();
  if (permissions.value.length > 0) {
    for (const p of permissions.value) {
      if (directIds.has(p.id) && !seen.has(p.id)) {
        if (!merged.find(m => m.id === p.id)) merged.push(p);
        seen.add(p.id);
      }
    }
    for (const p of merged) {
      if (directIds.has(p.id)) seen.add(p.id);
    }
    for (const p of permissions.value) {
      if (directIds.has(p.id) && !seen.has(p.id)) {
        merged.push(p);
        seen.add(p.id);
      }
    }
  }
  return merged;
});

const snackbar = reactive({
  show: false,
  text: '',
  color: 'success'
});

const rules = {
  required: value => !!value || t('app.validation.required')
};

async function fetchUser() {
  isLoading.value = true;
  loadError.value = '';
  try {
    const response = await api.get(`/api/users/${props.id}`);
    const user = response.data.data;
    if (user) {
      form.fio = user.fio || '';
      form.role = user.role || '';
      form.phonenumber = user.phonenumber || '';
      form.city = user.city || '';
      form.country = user.country || '';
      form.organization = user.organization || '';
      form.position = user.position || '';
      form.rank = user.rank || '';
      form.spfere = user.spfere || '';
      form.specialization = user.specialization || '';
      form.group_id = user.group_id || null;
      form.role_id = user.roles?.map(r => r.id) || [];
      form.permission_id = user.permissions?.map(p => p.id) || [];
    }
  } catch (err) {
    loadError.value = err.response?.data?.message || t('users.loadError');
  } finally {
    isLoading.value = false;
  }
}

async function fetchGroups() {
  try {
    const response = await api.get('/api/groups');
    groups.value = response.data.data || [];
  } catch (err) {
    console.error('Error fetching groups:', err);
  }
}

async function fetchPermissions() {
  try {
    const response = await api.get('/api/permissions');
    permissions.value = response.data.data || [];
  } catch (err) {
    console.error('Error fetching permissions:', err);
  }
}

async function fetchRoles() {
  try {
    const response = await api.get('/api/roles');
    allRoles.value = response.data.data || [];
  } catch (err) {
    console.error('Error fetching roles:', err);
  }
}

async function handleSave() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  saving.value = true;
  saveError.value = '';

  try {
    await api.put(`/api/users/${props.id}`, {
      fio: form.fio,
      role: form.role,
      phonenumber: form.phonenumber,
      city: form.city,
      country: form.country,
      organization: form.organization,
      position: form.position,
      rank: form.rank,
      spfere: form.spfere,
      specialization: form.specialization,
      group_id: form.group_id,
      role_id: form.role_id,
      permission_id: form.permission_id
    });
    snackbar.text = t('users.saveSuccess');
    snackbar.color = 'success';
    snackbar.show = true;
    setTimeout(() => router.push({ name: 'users.list' }), 1000);
  } catch (err) {
    saveError.value = err.response?.data?.message || t('users.saveError');
  } finally {
    saving.value = false;
  }
}

function cancel() {
  router.push({ name: 'users.list' });
}

onMounted(() => {
  Promise.all([fetchUser(), fetchGroups(), fetchPermissions(), fetchRoles()]);
});
</script>

<style scoped>
</style>
