<script setup lang="ts">
import { ref } from 'vue'

const title = ref('')
const emit = defineEmits(['create'])

const submit = () => {
  if (!title.value) return
  emit('create', title.value)
  title.value = ''
}
</script>

<template>
  <div class="flex gap-2">
    <input
      v-model="title"
      class="border p-2 flex-1"
      placeholder="New task..."
    />

    <button
      @click="submit"
      class="bg-blue-500 text-white px-4"
    >
      Add
    </button>
  </div>
</template>