<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12" class="d-flex align-center justify-space-between">
        <h1 class="text-h4">{{ t('groups.title') }}</h1>
        <v-btn color="primary" variant="tonal" @click="dialog = true">
          <v-icon start>mdi-plus</v-icon>
          {{ t('groups.add') }}
        </v-btn>
      </v-col>
    </v-row>

    <v-row v-if="isLoading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" />
      </v-col>
    </v-row>

    <v-row v-else-if="allGroups.length === 0">
      <v-col cols="12">
        <v-alert type="info" variant="tonal">{{ t('groups.noGroups') }}</v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-table>
          <thead>
            <tr>
              <th class="text-left">{{ t('groups.id') }}</th>
              <th class="text-left">{{ t('groups.name') }}</th>
              <th class="text-left">{{ t('groups.description') }}</th>
              <th class="text-left">{{ t('groups.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="group in allGroups" :key="group.id">
              <td>{{ group.id }}</td>
              <td>{{ group.groupname }}</td>
              <td>{{ group.groupdescription }}</td>
              <td>
                <v-btn color="primary" variant="tonal" size="small" class="mr-2"
                  :to="{ name: 'groups.edit', params: { id: group.id } }">
                  {{ t('groups.edit') }}
                </v-btn>
                <v-btn color="secondary" variant="tonal" size="small" class="mr-2"
                  :to="{ name: 'groups.learning', params: { id: group.id } }">
                  {{ t('groups.learning') }}
                </v-btn>
                <v-btn color="error" variant="tonal" size="small" @click="confirmDelete(group)">
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
        <v-card-title>{{ t('groups.createTitle') }}</v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-text-field v-model="form.name" :label="t('groups.name')"
              :rules="[v => !!v || t('app.validation.required')]" variant="outlined" density="compact" class="mb-3" />
            <v-textarea v-model="form.description" :label="t('groups.description')"
              variant="outlined" density="compact" rows="3" />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">{{ t('common.cancel') }}</v-btn>
          <v-btn color="primary" :loading="isSubmitting" @click="submitForm">{{ t('common.save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>{{ t('app.confirm.title') }}</v-card-title>
        <v-card-text>{{ t('app.confirm.message', { name: groupToDelete?.groupname }) }}</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">{{ t('app.common.cancel') }}</v-btn>
          <v-btn color="error" variant="tonal" :loading="isDeleting" @click="deleteGroup">
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
import { useUserStore } from '@/stores/user.store';

const { t } = useI18n();
const userStore = useUserStore();

const dialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const groupToDelete = ref(null);
const form = ref({ name: '', description: '' });

const allGroups = userStore.allGroups;
const isLoading = userStore.isLoading;

async function fetchGroups() {
  await userStore.fetchGroups();
}

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;
  isSubmitting.value = true;
  try {
    await userStore.createGroup({ groupname: form.value.name, groupdescription: form.value.description });
    dialog.value = false;
    form.value = { name: '', description: '' };
  } catch (err) {
    console.error(err);
  } finally {
    isSubmitting.value = false;
  }
}

function confirmDelete(group) {
  groupToDelete.value = group;
  deleteDialog.value = true;
}

async function deleteGroup() {
  if (!groupToDelete.value) return;
  isDeleting.value = true;
  try {
    await userStore.deleteGroup(groupToDelete.value.id);
    deleteDialog.value = false;
    groupToDelete.value = null;
  } catch (err) {
    console.error(err);
  } finally {
    isDeleting.value = false;
  }
}

onMounted(() => { fetchGroups(); });
</script>
