<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('rbac.roles') }}</h1>
        <v-btn color="primary" variant="tonal" @click="dialog = true">
          <v-icon start>mdi-plus</v-icon>
          {{ t('rbac.addRole') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="roles.length === 0">
      <v-col cols="12">
        <v-alert type="info" variant="tonal">{{ t('rbac.noRoles') }}</v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">ID</th>
              <th class="text-left">{{ t('rbac.roleName') }}</th>
              <th class="text-left">{{ t('rbac.description') }}</th>
              <th class="text-left">{{ t('rbac.permissions') }}</th>
              <th class="text-left">{{ t('app.common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id">
              <td>{{ role.id }}</td>
              <td>{{ role.name }}</td>
              <td>{{ role.description }}</td>
              <td>
                <v-chip v-for="perm in role.permissions" :key="perm.id" size="x-small" class="mr-1">
                  {{ perm.description || perm.name }}
                </v-chip>
                <span v-if="!role.permissions?.length">—</span>
              </td>
              <td>
                <v-btn size="small" color="primary" variant="tonal" class="mr-2"
                  @click="editRole(role)">
                  <v-icon start>mdi-pencil</v-icon>
                  {{ t('app.common.edit') }}
                </v-btn>
                <v-btn size="small" color="error" variant="tonal" @click="confirmDelete(role)">
                  <v-icon start>mdi-delete</v-icon>
                  {{ t('app.common.delete') }}
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>{{ editingRole ? t('rbac.editRole') : t('rbac.createRole') }}</v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-text-field v-model="form.name" :label="t('rbac.roleName')"
              :rules="[v => !!v || t('app.validation.required')]" variant="outlined" density="compact" class="mb-3" />
            <v-text-field v-model="form.description" :label="t('rbac.description')"
              variant="outlined" density="compact" class="mb-3" />
            <v-autocomplete v-model="form.permission_ids" :items="allPermissions"
              item-title="description" item-value="id" :label="t('rbac.permissions')"
              variant="outlined" density="compact" multiple chips clearable />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="closeDialog">{{ t('app.common.cancel') }}</v-btn>
          <v-btn color="primary" :loading="isSubmitting" @click="submitForm">{{ t('app.common.save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('app.confirm.title') }}</v-card-title>
        <v-card-text>{{ t('rbac.confirmDeleteRole', { name: roleToDelete?.name }) }}</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">{{ t('app.common.cancel') }}</v-btn>
          <v-btn color="error" variant="tonal" :loading="isDeleting" @click="deleteRole">
            {{ t('app.common.delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const roles = ref([]);
const allPermissions = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const dialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const editingRole = ref(null);
const roleToDelete = ref(null);
const form = ref({ name: '', description: '', permission_ids: [] });

async function fetchRoles() {
  isLoading.value = true;
  try {
    const response = await api.get('/api/roles');
    roles.value = response.data.data || response.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
}

async function fetchPermissions() {
  try {
    const response = await api.get('/api/permissions');
    allPermissions.value = response.data.data || response.data || [];
  } catch (err) {
    console.error(err);
  }
}

function editRole(role) {
  editingRole.value = role;
  form.value = {
    name: role.name || '',
    description: role.description || '',
    permission_ids: role.permissions?.map(p => p.id) || []
  };
  dialog.value = true;
}

function closeDialog() {
  dialog.value = false;
  editingRole.value = null;
  form.value = { name: '', description: '', permission_ids: [] };
}

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;
  isSubmitting.value = true;
  try {
    if (editingRole.value) {
      const response = await api.put(`/api/roles/${editingRole.value.id}`, {
        name: form.value.name,
        description: form.value.description
      });
      const updated = response.data.data || response.data;
      if (form.value.permission_ids.length) {
        await api.post(`/api/roles/${updated.id}/permissions`, {
          permission_ids: form.value.permission_ids
        });
      }
      await fetchRoles();
    } else {
      const response = await api.post('/api/roles', {
        name: form.value.name,
        description: form.value.description
      });
      const created = response.data.data || response.data;
      if (form.value.permission_ids.length) {
        await api.post(`/api/roles/${created.id}/permissions`, {
          permission_ids: form.value.permission_ids
        });
      }
      await fetchRoles();
    }
    closeDialog();
  } catch (err) {
    console.error(err);
  } finally {
    isSubmitting.value = false;
  }
}

function confirmDelete(role) {
  roleToDelete.value = role;
  deleteDialog.value = true;
}

async function deleteRole() {
  if (!roleToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/api/roles/${roleToDelete.value.id}`);
    roles.value = roles.value.filter(r => r.id !== roleToDelete.value.id);
    deleteDialog.value = false;
    roleToDelete.value = null;
  } catch (err) {
    console.error(err);
  } finally {
    isDeleting.value = false;
  }
}

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
