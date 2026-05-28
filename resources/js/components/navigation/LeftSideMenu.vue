<template>
  <v-list v-model:opened="open">
    <v-list-item
      prepend-icon="mdi-home"
      :title="t('app.menu.home')"
      to="/"
    />

    <v-list-item
      prepend-icon="mdi-book-open-variant"
      :title="t('app.menu.courses')"
      to="/courses"
    />

    <v-list-item
      v-if="authStore.hasPermission('manage-users') || authStore.hasRole('Инструктор')"
      prepend-icon="mdi-shape-outline"
      :title="t('app.menu.categories')"
      to="/categories"
    />

    <v-list-group
      value="questions"
      v-if="authStore.hasPermission('manage-users') || authStore.hasRole('Инструктор')"
    >
      <template v-slot:activator="{ props }">
        <v-list-item
          v-bind="props"
          prepend-icon="mdi-help-circle-outline"
          :title="t('app.menu.questions')"
        />
      </template>
      <v-list-item
        prepend-icon="mdi-database"
        :title="t('app.menu.questionsBank')"
        to="/questions-bank"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-file-document-edit"
        :title="t('app.menu.examine')"
        to="/examine"
        @click.stop
      />
    </v-list-group>

    <v-list-group
      value="files"
      v-if="authStore.hasPermission('manage-users') || authStore.hasRole('Инструктор')"
    >
      <template v-slot:activator="{ props }">
        <v-list-item
          v-bind="props"
          prepend-icon="mdi-file-cloud"
          :title="t('app.menu.files')"
        />
      </template>
      <v-list-item
        prepend-icon="mdi-folder-multiple"
        :title="t('app.menu.fileManager')"
        to="/files"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-upload"
        :title="t('app.menu.upload')"
        to="/files/upload"
        @click.stop
      />
    </v-list-group>

    <v-list-item
      prepend-icon="mdi-calendar"
      :title="t('app.menu.calendar')"
      to="/calendar"
    />

    <v-list-group
      value="settings"
      v-if="authStore.hasPermission('manage-users') || authStore.hasRole('Инструктор')"
    >
      <template v-slot:activator="{ props }">
        <v-list-item
          v-bind="props"
          prepend-icon="mdi-cog"
          :title="t('app.menu.settings')"
        />
      </template>
      <v-list-item
        v-if="authStore.hasPermission('manage-users')"
        prepend-icon="mdi-tune"
        :title="t('app.menu.generalSettings')"
        to="/settings/general"
      />
      <v-list-item
        prepend-icon="mdi-tune"
        :title="t('app.menu.generalSettings')"
        to="/settings/general"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-star"
        :title="t('app.menu.grades')"
        to="/settings/grades"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-cog-outline"
        :title="t('app.menu.allSettings')"
        to="/settings/all"
        @click.stop
      />
      <v-list-item
        v-if="authStore.hasPermission('manage-users')"
        prepend-icon="mdi-cog-outline"
        :title="t('app.menu.allSettings')"
        to="/settings/all"
      />
    </v-list-group>

    <v-list-group
      value="users"
      v-if="authStore.hasPermission('manage-users')"
    >
      <template v-slot:activator="{ props }">
        <v-list-item
          v-bind="props"
          prepend-icon="mdi-account-group"
          :title="t('app.menu.users')"
        />
      </template>
      <v-list-item
        prepend-icon="mdi-account-multiple"
        :title="t('app.menu.list')"
        to="/users"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-account-plus"
        :title="t('app.menu.createUser')"
        to="/users"
        @click.stop
      />
    </v-list-group>

    <v-list-item
      v-if="authStore.hasPermission('manage-users')"
      prepend-icon="mdi-account-multiple-outline"
      :title="t('app.menu.groups')"
      to="/groups"
    />
    <v-list-item
      v-if="authStore.hasPermission('manage-users')"
      prepend-icon="mdi-airplane"
      :title="t('app.menu.aircrafts')"
      to="/aircrafts"
    />
    <v-list-item
      v-if="authStore.hasPermission('manage-users')"
      prepend-icon="mdi-download"
      :title="t('app.menu.importCourses')"
      to="/import-courses"
    />

    <v-list-group
      value="rbac"
      v-if="authStore.hasPermission('manage-users')"
    >
      <template v-slot:activator="{ props }">
        <v-list-item
          v-bind="props"
          prepend-icon="mdi-shield-account"
          :title="t('app.menu.rbac')"
        />
      </template>
      <v-list-item
        prepend-icon="mdi-shield-key"
        :title="t('app.menu.roles')"
        to="/roles"
        @click.stop
      />
      <v-list-item
        prepend-icon="mdi-shield-check"
        :title="t('app.menu.permissions')"
        to="/permissions"
        @click.stop
      />
    </v-list-group>

    <v-list-item
      v-if="authStore.hasPermission('manage-users') || authStore.hasRole('Обучаемый')"
      prepend-icon="mdi-school"
      :title="t('app.menu.myLearning')"
      to="/user/learning"
    />

    <v-list-item
      prepend-icon="mdi-phone"
      :title="t('app.menu.contacts')"
      to="/contacts"
    />

    <logout-app />
  </v-list>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';
import LogoutApp from '@/components/navigation/LogoutApp.vue';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const open = ref(['questions', 'files', 'settings', 'users', 'rbac']);

function navigateTo(path) {
  router.push(path);
}
</script>
