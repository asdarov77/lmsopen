<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('contacts.title') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card variant="outlined">
          <v-card-item>
            <template v-slot:prepend>
              <v-avatar color="primary">
                <v-icon color="white">mdi-email</v-icon>
              </v-avatar>
            </template>
            <v-card-title>{{ t('contacts.contactInfo') }}</v-card-title>
            <v-card-subtitle>{{ t('contacts.subtitle') }}</v-card-subtitle>
          </v-card-item>

          <v-card-text>
            <v-list density="compact">
              <v-list-item
                prepend-icon="mdi-email"
                title="lms@example.com"
                subtitle="Email"
              />
              <v-list-item
                prepend-icon="mdi-phone"
                title="+7 (123) 456-78-90"
                subtitle="Телефон"
              />
              <v-list-item
                prepend-icon="mdi-map-marker"
                title="г. Москва, ул. Примерная, д. 1"
                subtitle="Адрес"
              />
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card variant="outlined">
          <v-card-item>
            <template v-slot:prepend>
              <v-avatar color="info">
                <v-icon color="white">mdi-information</v-icon>
              </v-avatar>
            </template>
            <v-card-title>{{ t('contacts.aboutTitle') }}</v-card-title>
          </v-card-item>
          <v-card-text>
            <p class="text-body-1">
              {{ t('contacts.aboutText') }}
            </p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
