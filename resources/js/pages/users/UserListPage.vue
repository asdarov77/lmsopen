<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('users.title') }}</h1>
      </v-col>
    </v-row>

    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="4">
        <v-text-field
          v-model="search"
          :label="t('app.common.search')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          clearable
        />
      </v-col>
      <v-col cols="12" sm="6" md="4" class="d-flex align-center">
        <v-btn
          color="success"
          prepend-icon="mdi-plus"
          @click="createDialog = true"
        >
          {{ t('users.add') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-progress-circular
          v-if="userStore.isLoading"
          indeterminate
          color="primary"
          class="d-block mx-auto my-8"
        />

        <v-alert
          v-else-if="userStore.error"
          type="error"
          variant="tonal"
          closable
          @click:close="userStore.clearError()"
        >
          {{ userStore.error }}
        </v-alert>

        <v-card v-else variant="outlined">
          <v-table density="comfortable">
            <thead>
              <tr>
                <th class="text-left">ID</th>
                <th class="text-left">{{ t('users.fio') }}</th>
                <th class="text-left">{{ t('users.assignedRoles') }}</th>
                <th class="text-left">{{ t('users.permissions') }}</th>
                <th class="text-left">{{ t('users.group') }}</th>
                <th class="text-left">{{ t('users.role') }}</th>
                <th class="text-left">{{ t('app.common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="_user in filteredUsers" :key="_user.id">
                <td>{{ _user.id }}</td>
                <td>
                  <router-link
                    :to="{ name: 'users.edit', params: { id: _user.id } }"
                  >
                    {{ _user.fio }}
                  </router-link>
                </td>
                <td>
                  <v-chip
                    v-for="role in _user.roles"
                    :key="role.id || role.name"
                    size="x-small"
                    color="primary"
                    variant="tonal"
                    class="mr-1"
                  >
                    {{ role.name }}
                  </v-chip>
                  <span v-if="!_user.roles?.length">—</span>
                </td>
                <td>
                  <v-chip
                    v-for="perm in _user.permissions"
                    :key="perm.id || perm.name"
                    size="x-small"
                    color="success"
                    variant="tonal"
                    class="mr-1"
                  >
                    {{ perm.name }}
                  </v-chip>
                  <span v-if="!_user.permissions?.length">—</span>
                </td>
                <td>{{ _user.group?.groupname || _user.group || '' }}</td>
                <td>{{ _user.role }}</td>
                <td>
                  <v-btn
                    size="small"
                    color="primary"
                    variant="tonal"
                    class="mr-2"
                    :to="{ name: 'users.edit', params: { id: _user.id } }"
                    prepend-icon="mdi-pencil"
                  >
                    {{ t('app.common.edit') }}
                  </v-btn>
                  <v-btn
                    size="small"
                    color="secondary"
                    variant="tonal"
                    class="mr-2"
                    :to="{ name: 'users.change-password', params: { id: _user.id } }"
                    prepend-icon="mdi-lock"
                  >
                    {{ t('users.changePassword') }}
                  </v-btn>
                  <v-btn
                    size="small"
                    color="error"
                    variant="tonal"
                    prepend-icon="mdi-delete"
                    @click="confirmDelete(_user)"
                  >
                    {{ t('app.common.delete') }}
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('app.common.confirm') }}</v-card-title>
        <v-card-text>
          {{ t('users.deleteConfirm', { fio: deleteTarget?.fio || deleteTarget?.id }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="error" variant="text" @click="deleteDialog = false">
            {{ t('app.common.cancel') }}
          </v-btn>
          <v-btn color="primary" variant="tonal" @click="deleteUser">
            {{ t('app.common.delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="createDialog" max-width="500" persistent>
      <v-card>
        <v-card-title>{{ t('users.add') }}</v-card-title>
        <v-card-text>
          <v-form ref="createFormRef" @submit.prevent="handleCreate">
            <v-text-field
              v-model="createForm.fio"
              :label="t('auth.fio')"
              :rules="[rules.required]"
              variant="outlined"
              density="compact"
              class="mb-3"
            />

            <v-text-field
              v-model="createForm.password"
              :label="t('auth.password')"
              :type="showPassword ? 'text' : 'password'"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword"
              :rules="[rules.required, rules.minLength]"
              variant="outlined"
              density="compact"
              class="mb-3"
            />

            <v-text-field
              v-model="createForm.password_confirmation"
              :label="t('auth.passwordConfirmation')"
              :type="showPassword ? 'text' : 'password'"
              :rules="[rules.required, rules.matchPassword]"
              variant="outlined"
              density="compact"
              class="mb-3"
            />

            <v-select
              v-model="createForm.role"
              :label="t('users.role')"
              :items="roles"
              item-title="name"
              item-value="name"
              variant="outlined"
              density="compact"
              class="mb-3"
            />

            <v-select
              v-model="createForm.role_id"
              :label="t('users.selectRoles')"
              :items="roles"
              item-title="name"
              item-value="id"
              variant="outlined"
              density="compact"
              class="mb-3"
              multiple
              clearable
            />

            <v-select
              v-model="createForm.group_id"
              :label="t('users.group')"
              :items="userStore.allGroups"
              item-title="groupname"
              item-value="id"
              variant="outlined"
              density="compact"
              class="mb-3"
              clearable
            />

          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="closeCreateDialog">
            {{ t('app.common.cancel') }}
          </v-btn>
          <v-btn
            color="primary"
            variant="tonal"
            :loading="createLoading"
            @click="handleCreate"
          >
            {{ t('app.common.save') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
import { useI18n } from 'vue-i18n';
import { useUserStore } from '@/stores/user.store';
import api from '@/services/api.service';

const { t } = useI18n();
const userStore = useUserStore();

const search = ref('');
const deleteDialog = ref(false);
const deleteTarget = ref(null);

const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
});

// Create dialog
const createDialog = ref(false);
const createFormRef = ref(null);
const showPassword = ref(false);
const roles = ref([]);

const createLoading = ref(false);

const createForm = reactive({
  fio: '',
  password: '',
  password_confirmation: '',
  role: '',
  role_id: [],
  group_id: null,
});

const rules = {
  required: value => !!value || t('app.validation.required'),
  minLength: value => (value && value.length >= 6) || t('app.validation.minLength', { min: 6 }),
  matchPassword: value => value === createForm.password || t('app.validation.passwordMismatch')
};

const filteredUsers = computed(() => {
  if (!search.value) return userStore.users;
  const q = search.value.toLowerCase();
  return userStore.users.filter(u =>
    u.fio?.toLowerCase().includes(q) ||
    u.role?.toLowerCase().includes(q) ||
    u.organization?.toLowerCase().includes(q)
  );
});

function confirmDelete(_user) {
  deleteTarget.value = _user;
  deleteDialog.value = true;
}

async function deleteUser() {
  if (!deleteTarget.value) return;
  try {
    await userStore.deleteUser(deleteTarget.value.id);
    snackbar.value = { show: true, text: t('users.deleteSuccess'), color: 'success' };
  } catch {
    snackbar.value = { show: true, text: t('users.deleteError'), color: 'error' };
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

function closeCreateDialog() {
  createDialog.value = false;
  createForm.fio = '';
  createForm.password = '';
  createForm.password_confirmation = '';
  createForm.role = '';
  createForm.role_id = [];
  createForm.group_id = null;
}

async function handleCreate() {
  const { valid } = await createFormRef.value.validate();
  if (!valid) return;

  createLoading.value = true;
  try {
    const res = await userStore.createUser({
      fio: createForm.fio,
      password: createForm.password,
      password_confirmation: createForm.password_confirmation,
      role: createForm.role || undefined,
      group_id: createForm.group_id || undefined,
    });

    const newUser = res.data?.user || res.data?.data || res.data;
    const userId = newUser?.id;
    if (userId && createForm.role_id.length > 0) {
      try {
        await api.post(`/api/users/${userId}/roles`, { role_id: createForm.role_id });
      } catch { }
    }

    snackbar.value = { show: true, text: t('users.createSuccess'), color: 'success' };
    closeCreateDialog();
    userStore.fetchUsers();
  } catch (err) {
    snackbar.value = {
      show: true,
      text: err.response?.data?.message || t('users.createError'),
      color: 'error'
    };
  } finally {
    createLoading.value = false;
  }
}

async function fetchRoles() {
  try {
    const response = await api.get('/api/roles');
    roles.value = response.data.data || response.data;
  } catch {
    roles.value = [];
  }
}

onMounted(() => {
  userStore.fetchUsers();
  userStore.fetchPermissions();
  if (userStore.allGroups.length === 0) {
    userStore.fetchGroups();
  }
  fetchRoles();
});
</script>
