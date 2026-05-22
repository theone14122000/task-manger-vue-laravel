<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '../api/axios'
import Navbar from '../components/Navbar.vue'
import { useToast } from 'vue-toastification'

const attachment = ref<File | null>(null)
const fileInputKey = ref(0)

const tasks = ref<any[]>([])
const title = ref('')

const search = ref('')
const filter = ref('all')

const status = ref('todo')
const dueDate = ref('')
const priority = ref('medium')

const toast = useToast()

const currentPage = ref(1)
const perPage = ref(6)

const darkMode = ref(false)

const updateTheme = () => {

  if (darkMode.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const toggleDarkMode = () => {

  darkMode.value = !darkMode.value

  updateTheme()
}

const fetchTasks = async () => {

  try {

    const response = await api.get('/tasks')

    tasks.value = Array.isArray(response.data)
      ? response.data
      : response.data.data ?? []

  } catch (error) {

    console.log(error)
  }
}

const createTask = async () => {

  if (!title.value.trim()) return

  try {

    const formData = new FormData()

    formData.append('title', title.value)
    formData.append('priority', priority.value)
    formData.append('status', status.value)
    formData.append('due_date', dueDate.value)

    if (attachment.value) {
      formData.append('attachment', attachment.value)
    }

    await api.post('/tasks', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    title.value = ''
    priority.value = 'medium'
    status.value = 'todo'
    dueDate.value = ''
    attachment.value = null
    fileInputKey.value++

    fetchTasks()

    toast.success('Task Created')

  } catch (error) {

    console.log('CREATE ERROR', error)
  }
}

const toggleTask = async (id: number) => {

  try {

    await api.patch(`/tasks/${id}/toggle`)

    fetchTasks()

  } catch (error) {

    console.log(error)
  }
}

const deleteTask = async (id: number) => {

  try {

    await api.delete(`/tasks/${id}`)

    fetchTasks()

    toast.success('Task Deleted Succesfully')

  } catch (error) {

    console.log(error)
  }
}

const completedTasksCount = computed(() => {
  return tasks.value.filter((task: any) => task.completed).length
})

const pendingTasksCount = computed(() => {
  return tasks.value.filter((task: any) => !task.completed).length
})

const filteredTasks = computed(() => {

  return tasks.value.filter((task: any) => {

    const matchesSearch =
      (task.title || '')
        .toLowerCase()
        .includes(search.value.toLowerCase())

    const matchesFilter =
      filter.value === 'all'
        ? true
        : filter.value === 'completed'
          ? task.completed
          : !task.completed

    return matchesSearch && matchesFilter
  })
})

const lastPage = computed(() => {
  return Math.ceil(filteredTasks.value.length / perPage.value)
})

const paginatedTasks = computed(() => {

  const start = (currentPage.value - 1) * perPage.value

  const end = start + perPage.value

  return filteredTasks.value.slice(start, end)
})

const changePage = (page: number) => {

  if (page < 1 || page > lastPage.value) return

  currentPage.value = page
}

watch([search, filter], () => {
  currentPage.value = 1
})

watch(filteredTasks, () => {

  if (currentPage.value > lastPage.value) {
    currentPage.value = lastPage.value || 1
  }
})

const handleFileUpload = (event: Event) => {

  const input = event.target as HTMLInputElement

  attachment.value = input.files?.[0] || null
}

onMounted(() => {

  fetchTasks()

  const savedTheme = localStorage.getItem('theme')

  if (!savedTheme) {
    localStorage.setItem('theme', 'light')
  }

  darkMode.value = localStorage.getItem('theme') === 'dark'

  updateTheme()
})
</script>

<template>
  <Navbar
    :isAdmin="false"
    :showSearch="true"
    :search="search"
    :filter="filter"
    @update:search="search = $event"
    @update:filter="filter = $event"
  />

  <div class="min-h-screen bg-slate-100 dark:bg-gray-950 px-4 py-8 md:px-8">

    <div class="max-w-7xl mx-auto space-y-6">

      <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

        <div>
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
            My Tasks
          </h1>

          <p class="text-gray-500 dark:text-gray-400 mt-2">
            Organize your work efficiently
          </p>
        </div>

        <button
          @click="toggleDarkMode"
          class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-5 py-3 rounded-2xl font-semibold shadow-sm"
        >
          {{ darkMode ? 'Light' : 'Dark' }}
        </button>

      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Total
          </p>

          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
            {{ tasks.length }}
          </h2>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
          <p class="text-sm text-green-500">
            Completed
          </p>

          <h2 class="text-3xl font-bold text-green-500 mt-1">
            {{ completedTasksCount }}
          </h2>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
          <p class="text-sm text-yellow-500">
            Pending
          </p>

          <h2 class="text-3xl font-bold text-yellow-500 mt-1">
            {{ pendingTasksCount }}
          </h2>
        </div>

      </div>

      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-5 shadow-sm">

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto_auto_auto_auto_auto] gap-3">

          <input
            v-model="title"
            type="text"
            placeholder="What needs to be done?"
            class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl px-4 py-3 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />

          <select
            v-model="priority"
            class="bg-slate-50 dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>

          <select
            v-model="status"
            class="bg-slate-50 dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="todo">Todo</option>
            <option value="progress">In Progress</option>
          </select>

          <input
            v-model="dueDate"
            type="date"
            class="bg-slate-50 dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3"
          />

          <input
            :key="fileInputKey"
            type="file"
            @change="handleFileUpload"
            class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3"
          />

          <button
            @click="createTask"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold shadow-sm transition"
          >
            Add Task
          </button>

        </div>

      </div>

      <div
        v-if="filteredTasks.length === 0"
        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-12 text-center"
      >
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
          No tasks found
        </h2>

        <p class="text-gray-500 dark:text-gray-400 mt-2">
          Start by creating your first task
        </p>
      </div>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
      >

        <div
          v-for="task in paginatedTasks"
          :key="task.id"
          class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 hover:shadow-xl transition"
        >

          <div class="flex items-center justify-between mb-5">

            <span
              :class="[
                'text-xs font-semibold px-3 py-1 rounded-full',
                task.completed
                  ? 'bg-green-100 text-green-700'
                  : 'bg-yellow-100 text-yellow-700'
              ]"
            >
              {{ task.completed ? 'Completed' : 'Pending' }}
            </span>

            <span
              :class="[
                'text-xs font-semibold px-3 py-1 rounded-full',
                task.priority === 'high'
                  ? 'bg-red-100 text-red-700'
                  : task.priority === 'medium'
                    ? 'bg-yellow-100 text-yellow-700'
                    : 'bg-green-100 text-green-700'
              ]"
            >
              {{ task.priority }}
            </span>

          </div>

          <h2
            :class="[
              'text-xl font-bold mb-3 break-words',
              task.completed
                ? 'line-through text-gray-400'
                : 'text-gray-900 dark:text-white'
            ]"
          >
            {{ task.title }}
          </h2>

          <div class="flex flex-wrap items-center gap-2 mb-4">

            <span class="text-sm text-gray-500 dark:text-gray-400">
              ID #{{ task.id }}
            </span>

            <span
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                task.status === 'todo'
                  ? 'bg-gray-100 text-gray-700'
                  : task.status === 'progress'
                    ? 'bg-blue-100 text-blue-700'
                    : 'bg-green-100 text-green-700'
              ]"
            >
              {{ task.status }}
            </span>

          </div>

          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Due:
            <span class="font-semibold text-gray-700 dark:text-gray-200">
              {{ task.due_date || 'No deadline' }}
            </span>
          </p>

          <div
            v-if="task.attachment"
            class="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-3 py-2 dark:border-blue-900 dark:bg-blue-950/40"
          >
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
              Attachment available
            </span>

            <a
              :href="`http://127.0.0.1:8000/storage/${task.attachment}`"
              target="_blank"
              class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200"
            >
              View Attachment
            </a>
          </div>

          <div class="flex gap-3">

            <button
              @click="toggleTask(task.id)"
              :class="[
                'flex-1 text-white py-3 rounded-2xl font-medium transition',
                task.completed
                  ? 'bg-yellow-500 hover:bg-yellow-600'
                  : 'bg-green-500 hover:bg-green-600'
              ]"
            >
              {{ task.completed ? 'Undo' : 'Complete' }}
            </button>

            <button
              @click="deleteTask(task.id)"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-medium transition"
            >
              Delete
            </button>

          </div>

        </div>

      </div>

      <div
        v-if="lastPage > 1"
        class="flex items-center justify-center gap-4 pt-2"
      >

        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3 rounded-xl transition"
        >
          Previous
        </button>

        <span class="text-gray-600 dark:text-gray-300 font-medium">
          Page {{ currentPage }} of {{ lastPage }}
        </span>

        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage === lastPage"
          class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3 rounded-xl transition"
        >
          Next
        </button>

      </div>

    </div>

  </div>

</template>
