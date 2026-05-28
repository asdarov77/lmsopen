<template>
  <div v-if="show">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth.store';

const props = defineProps({
  contentType: {
    type: String,
    default: '',
  },
  operations: {
    type: Array,
    default: () => [],
  },
});

const authStore = useAuthStore();

const show = computed(() => {
  if (!props.contentType) return true;
  return authStore.hasPermission(props.contentType);
});
</script>
