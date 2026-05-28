<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('settings.title') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="3">
        <v-tabs v-model="activeTab" direction="vertical">
          <v-tab
            v-for="(setting, index) in settings"
            :key="index"
          >
            {{ setting.category }}
          </v-tab>
        </v-tabs>
      </v-col>

      <v-col cols="12" md="9">
        <v-card variant="outlined">
          <v-card-text>
            <component :is="currentSettingComponent" />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

import GradeSettings from './GradeSettingsPage.vue'
import GeneralSettings from './GeneralSettingsPage.vue'

const { t } = useI18n()

const activeTab = ref(0)

const settings = ref([
  { category: t('settings.gradeBoundaries'), component: GradeSettings },
  { category: t('settings.general'), component: GeneralSettings }
])

const currentSettingComponent = computed(() => {
  return settings.value[activeTab.value]?.component || null
})
</script>

<style scoped>
.v-tabs {
  --v-tabs-height: auto;
}
</style>
