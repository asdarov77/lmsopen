<template>
  <v-date-picker
    v-model="picked"
    show-adjacent-months
  />
</template>

<script setup>
import { ref, watch } from 'vue'

const picked = ref(new Date())

const emit = defineEmits(['update:modelValue'])

watch(picked, (val) => {
  emit('update:modelValue', val)
})
</script>
