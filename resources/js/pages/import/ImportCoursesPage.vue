<template>
  <v-container fluid class="import-courses-container">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 mb-4">{{ t('importCourses.title') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-airplane</v-icon>
            {{ t('importClasses.classes') }}
          </v-card-title>
          
          <v-card-text>
            <v-row class="mb-2">
              <v-col cols="6">
                <h3 class="text-subtitle-1">{{ t('importClasses.inDatabase') }}</h3>
                <v-list density="compact" v-if="dbAircrafts.length">
                  <v-list-item
                    v-for="aircraft in dbAircrafts"
                    :key="aircraft.id"
                  >
                    <v-list-item-title>{{ aircraft.path }}</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ t('importClasses.coursesCount', { count: aircraft.courses?.length || 0 }) }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
                <v-alert v-else type="info" density="compact" class="mt-2">
                  {{ t('importClasses.noClassesInDb') }}
                </v-alert>
              </v-col>
              <v-col cols="6">
                <h3 class="text-subtitle-1">{{ t('importClasses.inStorage') }}</h3>
                <v-list density="compact" v-if="fsAircrafts.length">
                  <v-list-item
                    v-for="aircraft in fsAircrafts"
                    :key="aircraft"
                    :class="{ 'bg-grey-lighten-3': selectedFsAircraft === aircraft }"
                    @click="selectFsAircraft(aircraft)"
                  >
                    <v-list-item-title>{{ aircraft }}</v-list-item-title>
                    <template v-slot:append>
                      <v-btn
                        v-if="!aircraftExistsInDb(aircraft)"
                        icon="mdi-plus"
                        size="small"
                        color="success"
                        @click.stop="importClass(aircraft)"
                        :loading="importingClass === aircraft"
                      ></v-btn>
                      <v-chip v-else size="x-small" color="warning">{{ t('importClasses.inDatabase') }}</v-chip>
                    </template>
                  </v-list-item>
                </v-list>
                <v-alert v-else type="info" density="compact" class="mt-2">
                  {{ t('importClasses.noClassesInStorage') }}
                </v-alert>
              </v-col>
            </v-row>
            
            <v-btn
              color="primary"
              class="mt-2"
              block
              @click="refreshAll"
              :loading="loading"
            >
              <v-icon start>mdi-refresh</v-icon>
              {{ t('importClasses.refresh') }}
            </v-btn>
            
            <v-divider class="my-4"></v-divider>
            
            <v-btn
              color="error"
              block
              @click="confirmClearDatabase"
              :loading="clearingDb"
            >
              <v-icon start>mdi-delete-outline</v-icon>
              {{ t('importClasses.clearDatabase') }}
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-cog</v-icon>
            {{ t('importClasses.importSettings') }}
          </v-card-title>
          
          <v-card-text>
            <v-select
              v-model="selectedDbAircraft"
              :items="dbAircrafts"
              item-title="path"
              item-value="path"
              :label="t('importClasses.selectClass')"
              :return-object="false"
              density="compact"
              variant="outlined"
              class="mb-4"
            ></v-select>
            
            <v-switch
              v-model="clearDb"
              :label="t('importClasses.clearDbBeforeImport')"
              color="error"
              :hint="t('importClasses.clearDbHint')"
              persistent-hint
            ></v-switch>
            
            <v-divider class="my-4"></v-divider>
            
            <div v-if="selectedDbAircraft" class="text-center">
              <p class="text-h6 mb-2">{{ t('importClasses.selectedClass', { class: getSelectedAircraftPath() }) }}</p>
              <v-btn
                color="success"
                size="large"
                @click="startImport"
                :loading="importing"
                :disabled="!selectedDbAircraft"
              >
                <v-icon start>mdi-import</v-icon>
                {{ t('importClasses.startImport') }}
              </v-btn>
            </div>
            
            <v-alert v-else type="info" class="mt-4">
              {{ t('importClasses.selectClassPrompt') }}
            </v-alert>
          </v-card-text>
        </v-card>
        
        <v-card v-if="importResult" class="mt-4">
          <v-card-title :class="importResult.success ? 'bg-success' : 'bg-error'">
            <v-icon class="mr-2">{{ importResult.success ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ t('importClasses.importResult') }}
          </v-card-title>
          
          <v-card-text>
            <v-list density="compact">
              <v-list-item>
                <v-list-item-title>{{ t('importClasses.coursesCreated') }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.courses_loaded || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>{{ t('importClasses.categoriesCreated') }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.categories_created || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>{{ t('importClasses.structuresCreated') }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.aukstructures_created || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>{{ t('importClasses.questionsImported') }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.questions_imported || 0 }}</v-chip>
                </template>
              </v-list-item>
              
              <v-list-item>
                <v-list-item-title>{{ t('importClasses.answersImported') }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip color="primary">{{ importResult.result?.answers_imported || 0 }}</v-chip>
                </template>
              </v-list-item>
            </v-list>
            
            <v-alert
              v-if="importResult.result?.errors?.length"
              type="warning"
              class="mt-2"
            >
              <div class="text-subtitle-1">{{ t('importClasses.errors') }}</div>
              <ul>
                <li v-for="(error, idx) in importResult.result.errors.slice(0, 5)" :key="idx">
                  {{ error }}
                </li>
              </ul>
              <div v-if="importResult.result.errors.length > 5">
                {{ t('importClasses.moreErrors', { count: importResult.result.errors.length - 5 }) }}
              </div>
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const loading = ref(false);
const clearingDb = ref(false);
const importing = ref(false);
const importingClass = ref(null);
const fsAircrafts = ref([]);
const dbAircrafts = ref([]);
const selectedFsAircraft = ref(null);
const selectedDbAircraft = ref(null);
const clearDb = ref(false);
const importResult = ref(null);

async function refreshAll() {
  await Promise.all([
    refreshFsAircrafts(),
    refreshDbAircrafts()
  ]);
}

async function refreshFsAircrafts() {
  loading.value = true;
  try {
    const response = await api.get('/api/aircrafts/showclassesfs');
    fsAircrafts.value = response.data || [];
  } catch (err) {
    console.error('Ошибка загрузки классов из хранилища:', err);
  } finally {
    loading.value = false;
  }
}

async function refreshDbAircrafts() {
  try {
    const response = await api.get('/api/aircrafts');
    dbAircrafts.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Ошибка загрузки классов из БД:', err);
  }
}

function selectFsAircraft(aircraft) {
  selectedFsAircraft.value = aircraft;
}

function aircraftExistsInDb(path) {
  return dbAircrafts.value.some(a => a.path === path);
}

function getSelectedAircraftPath() {
  if (!selectedDbAircraft.value) return '';
  return selectedDbAircraft.value || '';
}

async function importClass(path) {
  importingClass.value = path;
  
  try {
    const response = await api.post('/api/aircrafts', {
      title: path,
      path: path
    });
    await refreshDbAircrafts();
  } catch (err) {
    console.error('Ошибка импорта класса:', err);
  } finally {
    importingClass.value = null;
  }
}

async function startImport() {
  if (!selectedDbAircraft.value) return;
  
  importing.value = true;
  importResult.value = null;
  
  try {
    const selectedPath = selectedDbAircraft.value;
    
    // Find the aircraft object in dbAircrafts array by path
    const aircraft = dbAircrafts.value.find(a => a.path === selectedPath);
    
    if (!aircraft) {
      throw new Error('Выбранный класс не найден');
    }
    
    const result = await api.post(`/api/aircrafts/scan/${aircraft.id}/load`, {
      force: clearDb.value
    });

    importResult.value = {
      success: true,
      result: result.data
    };

  } catch (err) {
    console.error('Ошибка импорта:', err);
    importResult.value = {
      success: false,
      result: { errors: [err.message] }
    };
  } finally {
    importing.value = false;
  }
}

function confirmClearDatabase() {
  if (confirm(t('importClasses.clearDbConfirm'))) {
    clearDatabase();
  }
}

async function clearDatabase() {
  clearingDb.value = true;
  
  try {
    await api.post('/api/aircrafts/clear');
    await refreshAll();
  } catch (err) {
    console.error('Ошибка очистки базы данных:', err);
  } finally {
    clearingDb.value = false;
  }
}

onMounted(() => {
  refreshAll();
});
</script>

<style scoped>
.import-courses-container {
  padding: 20px;
}
</style>