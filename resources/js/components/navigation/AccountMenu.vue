<template>
  <div>
    <v-menu>
      <template v-slot:activator="{ props }">
        <p v-if="authStore.accessToken">
          <v-avatar
            color="grey-darken-3"
            image="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortCurly&accessoriesType=Prescription02&hairColor=Black&facialHairType=Blank&clotheType=Hoodie&clotheColor=White&eyeType=Default&eyebrowType=DefaultNatural&mouthType=Default&skinColor=Light"
          ></v-avatar>
          <v-btn @click="show = !show" v-bind="props" min-width="200">
            {{ authStore.user?.fio }}
            <v-icon>{{ show ? "mdi-chevron-down" : "mdi-chevron-up" }}</v-icon>
          </v-btn>
        </p>
      </template>

      <v-list dense nav variant="text">
        <v-list-item
          v-for="([title, icon, link], i) in items"
          :key="i"
          :title="title"
          :to="link"
          :prepend-icon="icon"
        >
        </v-list-item>
      </v-list>
    </v-menu>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.store';

const { t } = useI18n();
const authStore = useAuthStore();

const show = ref(true);

const items = [
  [t('app.menu.profile'), 'mdi-account-multiple-outline', '/my-account'],
  [t('app.menu.settings'), 'mdi-account-multiple-outline', '/settings'],
  [t('app.menu.logout'), 'mdi-account-multiple-outline', '/logout'],
];
</script>
