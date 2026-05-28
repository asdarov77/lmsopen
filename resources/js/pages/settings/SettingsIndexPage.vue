<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('settings.title') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="4">
        <v-card
          variant="outlined"
          class="mb-4"
          :to="{ name: 'settings.grade-boundaries' }"
          hover
        >
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" size="x-large">mdi-chart-bar</v-icon>
            </template>
            <v-card-title class="text-body-1">{{ t('settings.gradeBoundaries') }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ t('settings.gradeBoundariesDescription') }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card
          variant="outlined"
          class="mb-4"
          :to="{ name: 'settings.general' }"
          hover
        >
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" size="x-large">mdi-cog</v-icon>
            </template>
            <v-card-title class="text-body-1">{{ t('settings.general') }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ t('settings.generalDescription') }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card
          variant="outlined"
          class="mb-4"
          :to="{ name: 'settings.notifications' }"
          hover
        >
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" size="x-large">mdi-bell-outline</v-icon>
            </template>
            <v-card-title class="text-body-1">{{ t('settings.notifications') }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ t('settings.notificationsDescription') }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card
          variant="outlined"
          class="mb-4"
          :to="{ name: 'settings.email' }"
          hover
        >
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" size="x-large">mdi-email-outline</v-icon>
            </template>
            <v-card-title class="text-body-1">{{ t('settings.email') }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ t('settings.emailDescription') }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card
          variant="outlined"
          class="mb-4"
          :to="{ name: 'settings.security' }"
          hover
        >
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" size="x-large">mdi-shield-outline</v-icon>
            </template>
            <v-card-title class="text-body-1">{{ t('settings.security') }}</v-card-title>
            <v-card-subtitle class="text-caption">
              {{ t('settings.securityDescription') }}
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
