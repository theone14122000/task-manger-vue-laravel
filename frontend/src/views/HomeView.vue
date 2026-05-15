<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '../api/axios'
import Navbar from '../components/Navbar.vue'
import { useToast } from 'vue-toastification'

const tasks = ref<any[]>([])
const title = ref('')

const search = ref('')
const filter = ref('all')

// status
const status = ref('todo')

// due dates
const dueDate = ref('')

// priority
const priority = ref('medium')

// toast
const toast = useToast()

// pagination
const currentPage = ref(1)
const perPage = ref(5)

// dark mode
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

    await api.post('/tasks', {
      title: title.value,
      priority: priority.value,
      status: status.value,
      due_date: dueDate.value
    })

    title.value = ''
    priority.value = 'medium'
    status.value = 'todo'
    dueDate.value = ''

    fetchTasks()

    toast.success('Task Created Successfully')

  } catch (error) {

    console.log(error)
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

const filteredTasks = computed(() => {

  return tasks.value.filter((task: any) => {

    const matchesSearch =
      task.title
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

  <!-- PAGE -->
  <div
    class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200 dark:from-gray-950 dark:via-gray-900 dark:to-black py-10 px-6"
  >

    <div class="max-w-5xl mx-auto">

      <!-- TOP DASHBOARD -->
      <div
        class="bg-white/80 dark:bg-gray-900/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700 p-8"
      >

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-8">

          <div>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">
              My Tasks
            </h1>

            <p class="text-gray-500 dark:text-gray-400 mt-2">
              Organize your work efficiently
            </p>
          </div>

          <div class="flex items-center gap-4">

            <!-- STATS -->
            <div class="hidden md:flex gap-4">

              <div
                class="bg-blue-100 dark:bg-blue-900/40 px-5 py-3 rounded-2xl text-center"
              >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Total
                </p>

                <h2 class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                  {{ tasks.length }}
                </h2>
              </div>

              <div
                class="bg-green-100 dark:bg-green-900/40 px-5 py-3 rounded-2xl text-center"
              >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Completed
                </p>

                <h2 class="text-2xl font-bold text-green-700 dark:text-green-300">
                  {{ completedTasksCount }}
                </h2>
              </div>

            </div>

          </div>

        </div>

        <!-- ADD TASK -->
        <div class="flex gap-4 mb-8">

          <input
            v-model="title"
            type="text"
            placeholder="What needs to be done?"
            class="flex-1 bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl px-5 py-4 text-gray-700 dark:text-white focus:outline-none focus:ring-4 focus:ring-blue-200"
          />

          <select
            v-model="priority"
            class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="low">
              Low
            </option>

            <option value="medium">
              Medium
            </option>

            <option value="high">
              High
            </option>
          </select>

          <select
            v-model="status"
            class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="todo">
              Todo
            </option>

            <option value="progress">
              In Progress
            </option>
          </select>

          <input
            v-model="dueDate"
            type="date"
            class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          />

          <button
            @click="createTask"
            class="bg-blue-600 hover:bg-blue-700 text-white px-8 rounded-2xl font-semibold shadow-lg transition"
          >
            Add Task
          </button>
        </div>

        <!-- EMPTY -->
        <div
          v-if="filteredTasks.length === 0"
          class="text-center py-20"
        >

          <h2 class="text-2xl font-bold text-gray-700 dark:text-white">
            No tasks found
          </h2>

          <p class="text-gray-500 dark:text-gray-400 mt-2">
            Start by creating your first task
          </p>

        </div>

        <!-- TASKS -->
        <div
          v-else
          class="grid gap-5"
        >

          <div
            v-for="task in paginatedTasks"
            :key="task.id"
            class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-3xl p-5 shadow-md hover:shadow-xl transition-all duration-300"
          >

            <div class="flex items-center justify-between">

              <!-- LEFT -->
              <div class="flex items-center gap-4">

                <input
                  type="checkbox"
                  :checked="task.completed"
                  @change="toggleTask(task.id)"
                  class="w-5 h-5 accent-blue-600"
                />

                <div>

                  <h2
                    :class="[
                      'text-lg font-semibold',
                      task.completed
                        ? 'line-through text-gray-400'
                        : 'text-gray-800 dark:text-white'
                    ]"
                  >
                    {{ task.title }}
                  </h2>

                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    <b> Due:
                    <u>{{ task.due_date || 'No deadline' }}</u></b>
                  </p>

                  <p class="text-sm text-gray-400">
                    Task ID: #{{ task.id }}
                  </p>

                </div>

              </div>

              <!-- RIGHT -->
              <div class="flex items-center gap-3">

                <span
                  :class="[
                    'px-4 py-2 rounded-xl text-sm font-medium',
                    task.completed
                      ? 'bg-green-100 text-green-700'
                      : 'bg-yellow-100 text-yellow-700'
                  ]"
                >
                  {{ task.completed ? 'Completed' : 'Pending' }}
                </span>

                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',

                    task.priority === 'high'
                      ? 'bg-red-100 text-red-700'

                      : task.priority === 'medium'
                        ? 'bg-yellow-100 text-yellow-700'

                        : 'bg-green-100 text-green-700'
                  ]"
                >
                  {{ task.priority }}
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

                <button
                  @click="toggleTask(task.id)"
                  class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl font-medium transition"
                >
                  {{ task.completed ? 'Undo' : 'Complete' }}
                </button>

                <button
                  @click="deleteTask(task.id)"
                  class="bg-red-100 hover:bg-red-200 text-red-600 px-4 py-2 rounded-xl font-medium transition"
                >
                  Delete
                </button>

              </div>

            </div>

          </div>

        </div>

        <!-- PAGINATION -->
        <div
          v-if="lastPage > 1"
          class="flex items-center justify-center gap-4 mt-8"
        >

          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3 rounded-2xl transition"
          >
            Previous
          </button>

          <span class="text-gray-600 dark:text-gray-300 font-medium">
            Page {{ currentPage }} of {{ lastPage }}
          </span>

          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3 rounded-2xl transition"
          >
            Next
          </button>

        </div>

      </div>

    </div>

  </div>

</template>
