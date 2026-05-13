<script setup lang="ts">
defineProps(['task'])
const emit = defineEmits(['toggle', 'delete'])
</script>

<template>
  <div
    class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-xl transition duration-300 hover:-translate-y-1"
  >

    <!-- TOP ROW -->
    <div class="flex justify-between items-start">

      <!-- LEFT -->
      <div class="flex items-start gap-3">

        <!-- CHECKBOX -->
        <input
          type="checkbox"
          :checked="task.completed"
          @change="emit('toggle', task)"
          class="w-5 h-5 mt-1 accent-green-500 cursor-pointer"
        />

        <!-- TITLE + META -->
        <div>
          <h3
            class="text-lg font-semibold"
            :class="task.completed ? 'line-through text-gray-400' : 'text-gray-800'"
          >
            {{ task.title }}
          </h3>

          <p class="text-xs text-gray-400 mt-1">
            Task ID: #{{ task.id }}
          </p>
        </div>

      </div>

      <!-- STATUS BADGE -->
      <span
        class="text-xs px-3 py-1 rounded-full font-medium"
        :class="task.completed
          ? 'bg-green-100 text-green-600'
          : 'bg-yellow-100 text-yellow-600'"
      >
        {{ task.completed ? 'Completed' : 'Pending' }}
      </span>

    </div>

    <!-- ACTIONS -->
    <div class="flex justify-end mt-4">

      <button
        @click="emit('delete', task.id)"
        class="text-sm text-red-500 hover:text-red-700 font-medium"
      >
        Delete Task
      </button>

    </div>

  </div>
</template>