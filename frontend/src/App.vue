<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'

const darkMode = ref(false)

onMounted(() => {

  const savedTheme =
    localStorage.getItem('theme')

  darkMode.value =
    savedTheme === 'dark'

  updateTheme()
})

const updateTheme = () => {

  if (darkMode.value) {

    document.documentElement.classList.add('dark')

    localStorage.setItem('theme', 'dark')

  } else {

    document.documentElement.classList.remove('dark')

    localStorage.setItem('theme', 'light')
  }
}

watch(darkMode, () => {

  updateTheme()
})
</script>

<template>

  <router-view />

 <button
  @click="darkMode = !darkMode"
  class="
    fixed top-5 right-5 z-50
    flex items-center gap-2
    px-4 py-2
    rounded-full
    bg-white dark:bg-gray-900
    border border-gray-200 dark:border-gray-700
    shadow-lg
    transition-all duration-300
    hover:scale-105
  "
>

  <!-- ICON -->
  <span
    class="text-xl transition-transform duration-500"
    :class="darkMode ? 'rotate-180' : 'rotate-0'"
  >
    {{ darkMode ? '☀️' : '🌙' }}
  </span>

  <!-- TEXT -->
  <span
    class="
      text-sm font-semibold
      text-gray-800 dark:text-white
    "
  >
    {{ darkMode ? 'Light Mode' : 'Dark Mode' }}
  </span>

</button>

</template>