<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('rbac.permissions') }}</h1>
        <v-btn color="primary" variant="tonal" @click="dialog = true">
          <v-icon start>mdi-plus</v-icon>
          {{ t('rbac.addPermission') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="permissions.length === 0">
      <v-col cols="12">
        <v-alert type="info" variant="tonal">{{ t('rbac.noPermissions') }}</v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">ID</th>
              <th class="text-left">{{ t('rbac.permissionName') }}</th>
              <th class="text-left">{{ t('rbac.description') }}</th>
              <th class="text-left">{{ t('app.common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="perm in permissions" :key="perm.id">
              <td>{{ perm.id }}</td>
              <td>{{ perm.name }}</td>
              <td>{{ perm.description }}</td>
              <td>
                <v-btn size="small" color="primary" variant="tonal" class="mr-2"
                  @click="editPermission(perm)">
                  <v-icon start>mdi-pencil</v-icon>
                  {{ t('app.common.edit') }}
                </v-btn>
                <v-btn size="small" color="error" variant="tonal" @click="confirmDelete(perm)">
                  <v-icon start>mdi-delete</v-icon>
                  {{ t('app.common.delete') }}
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="500">
      <v-card>
        <v-card-title>{{ editingPermission ? t('rbac.editPermission') : t('rbac.createPermission') }}</v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-text-field v-model="form.name" :label="t('rbac.permissionName')"
              :rules="[v => !!v || t('app.validation.required')]" variant="outlined" density="compact" class="mb-3" />
            <v-text-field v-model="form.description" :label="t('rbac.description')"
              :rules="[v => !!v || t('app.validation.required')]" variant="outlined" density="compact" class="mb-3" />
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
        <v-card-text>{{ t('rbac.confirmDeletePermission', { name: permissionToDelete?.name }) }}</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">{{ t('app.common.cancel') }}</v-btn>
          <v-btn color="error" variant="tonal" :loading="isDeleting" @click="deletePermission">
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

const permissions = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const dialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const editingPermission = ref(null);
const permissionToDelete = ref(null);
const form = ref({ name: '', description: '' });

async function fetchPermissions() {
  isLoading.value = true;
  try {
    const response = await api.get('/api/permissions');
    permissions.value = response.data.data || response.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
}

function editPermission(perm) {
  editingPermission.value = perm;
  form.value = { name: perm.name || '', description: perm.description || '' };
  dialog.value = true;
}

function closeDialog() {
  dialog.value = false;
  editingPermission.value = null;
  form.value = { name: '', description: '' };
}

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;
  isSubmitting.value = true;
  try {
    if (editingPermission.value) {
      const response = await api.put(`/api/permissions/${editingPermission.value.id}`, {
        name: form.value.name,
        description: form.value.description
      });
      const updated = response.data.data || response.data;
      const idx = permissions.value.findIndex(p => p.id === editingPermission.value.id);
      if (idx !== -1) permissions.value[idx] = updated;
    } else {
      const response = await api.post('/api/permissions', {
        name: form.value.name,
        description: form.value.description
      });
      const created = response.data.data || response.data;
      permissions.value.unshift(created);
    }
    closeDialog();
  } catch (err) {
    console.error(err);
  } finally {
    isSubmitting.value = false;
  }
}

function confirmDelete(perm) {
  permissionToDelete.value = perm;
  deleteDialog.value = true;
}

async function deletePermission() {
  if (!permissionToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/api/permissions/${permissionToDelete.value.id}`);
    permissions.value = permissions.value.filter(p => p.id !== permissionToDelete.value.id);
    deleteDialog.value = false;
    permissionToDelete.value = null;
  } catch (err) {
    console.error(err);
  } finally {
    isDeleting.value = false;
  }
}

onMounted(() => { fetchPermissions(); });
</script>
