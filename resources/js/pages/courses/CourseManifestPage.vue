<template>
  <v-container fluid class="h-100">
    <v-progress-linear
      v-if="isLoading"
      color="primary"
      indeterminate
      location="top"
    />

    <v-row v-if="errorMessage">
      <v-col cols="12">
        <v-alert type="error" variant="tonal">
          {{ errorMessage }}
        </v-alert>
      </v-col>
    </v-row>

    <v-card v-else class="h-100" flat>
      <v-row no-gutters class="h-100">
        <v-col cols="3" class="border-e">
          <v-sheet class="h-100 pa-4" style="overflow-y: auto; background: #f5f5f5;">
            <v-sheet class="mb-4" elevation="2" rounded="lg">
              <div class="text-h6 text-center pa-3">{{ courseTitle }}</div>
            </v-sheet>

            <div v-if="aukstructures.length === 0" class="text-caption text-medium-emphasis text-center">
              {{ t('courses.noStructure') }}
            </div>

            <div
              v-for="(item, index) in aukstructures"
              :key="item.id"
              :id="'auk-' + item.id"
              :style="getItemStyle(item)"
              class="mt-2"
              @click="item.type === 3 ? loadContent(item.id) : null"
              @mouseover="item.type === 3 ? showHover(item.id) : null"
              @mouseleave="item.type === 3 ? hideHover(item.id) : null"
            >
              {{ item.title }}
            </div>
          </v-sheet>
        </v-col>

        <v-col cols="9">
          <v-sheet class="h-100 pa-4" style="overflow: auto; background: white;">
            <div v-if="!link" class="text-center text-medium-emphasis mt-10">
              {{ t('courses.selectTopic') }}
            </div>

            <iframe
              v-if="link"
              ref="iframeRef"
              :src="link"
              width="100%"
              :style="{ height: iframeHeight, border: 'none' }"
              @load="onIframeLoad"
              name="contentFrame"
            />
          </v-sheet>
        </v-col>
      </v-row>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api.service';

const { t } = useI18n();

const props = defineProps({
  idEdit: { type: Number, required: true },
  idCategory: { type: Number, default: null }
});

const isLoading = ref(false);
const errorMessage = ref('');

const courseTitle = ref('');
const aukstructures = ref([]);
const link = ref('');
const activeId = ref(null);

const iframeRef = ref(null);
const iframeHeight = ref('600px');

function getItemStyle(item) {
  const base = {
    cursor: item.type === 3 ? 'pointer' : 'default',
    opacity: item.type !== 3 ? 0.7 : 1,
    color: item.type !== 3 ? 'green' : 'inherit',
    fontSize: `${-5 * item.type + 30}px`,
    paddingLeft: `${(item.type - 1) * 10}px`,
    display: 'inline-block',
    wordWrap: 'break-word'
  };
  if (item.id === activeId.value) {
    base.background = 'lightgreen';
    base.fontWeight = 'bold';
  }
  return base;
}

function showHover(itemId) {
  const el = document.getElementById('auk-' + itemId);
  if (el && itemId !== activeId.value) {
    el.style.border = '2px dotted grey';
    el.style.borderRadius = '4px';
    el.style.background = '#D3D3D3';
    el.style.transform = 'scale(1.03)';
  }
}

function hideHover(itemId) {
  const el = document.getElementById('auk-' + itemId);
  if (el && itemId !== activeId.value) {
    el.style.border = 'none';
    el.style.background = 'none';
    el.style.transform = 'scale(1.0)';
  }
}

async function loadContent(itemId) {
  if (!itemId) return;

  activeId.value = itemId;
  isLoading.value = true;

  try {
    const response = await api.get(`/api/getlink/${itemId}`);
    link.value = response.data;
  } catch (error) {
    console.error('Error loading content:', error);
    errorMessage.value = t('errors.loadFailed');
  } finally {
    isLoading.value = false;
  }
}

function onIframeLoad() {
  if (!iframeRef.value) return;

  try {
    const doc = iframeRef.value.contentDocument || iframeRef.value.contentWindow.document;
    const height = doc.body.scrollHeight + 20;
    iframeHeight.value = `${height}px`;
  } catch (e) {
    iframeHeight.value = '800px';
  }
}

async function fetchStructure() {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await api.get('/api/courses/' + props.idEdit + '/link', {
      params: {
        course_id: props.idEdit,
        category_id: props.idCategory
      }
    });

    if (response.data && response.data[0]) {
      const data = response.data[0];
      courseTitle.value = data.title || '';
      aukstructures.value = data.aukstructures || [];

      const firstModule = aukstructures.value.find(item => item.type === 3);
      if (firstModule) {
        await loadContent(firstModule.id);
      }
    } else if (response.data) {
      const data = response.data;
      courseTitle.value = data.title || '';
      aukstructures.value = data.aukstructures || [];

      const firstModule = aukstructures.value.find(item => item.type === 3);
      if (firstModule) {
        await loadContent(firstModule.id);
      }
    }
  } catch (error) {
    console.error('Error fetching course structure:', error);
    errorMessage.value = t('errors.loadFailed');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchStructure();
});
</script>

<style scoped>
.h-100 {
  height: calc(100vh - 120px);
}

.border-e {
  border-right: 1px solid rgba(0, 0, 0, 0.12);
}
</style>
