<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../api/axios'
import Navbar from '../components/Navbar.vue'
import { useToast } from 'vue-toastification'

const tasks = ref<any[]>([])

const title = ref('')
const priority = ref('medium')

const editingId = ref<number | null>(null)

const search = ref('')
const filter = ref('all')

// pagination
const currentPage = ref(1)
const lastPage = ref(1)
// TOAST
const toast = useToast()


const filteredTasks = computed(() => {

  return tasks.value.filter((task: any) => {

    const matchesSearch =
      task.title
        .toLowerCase()
        .includes(search.value.toLowerCase())

    let matchesFilter = true

    if (filter.value === 'completed') {
      matchesFilter = task.completed == 1
    }

    else if (filter.value === 'pending') {
      matchesFilter = task.completed == 0
    }

    return matchesSearch && matchesFilter
  })
})

const fetchTasks = async (page = 1) => {

  try {

    const response = await api.get(`/admin/tasks?page=${page}`)

    tasks.value = Array.isArray(response.data)
      ? response.data
      : response.data.data ?? []

    currentPage.value = response.data.current_page ?? page

    lastPage.value = response.data.last_page ?? 1

  } catch (error) {

    console.log('FETCH ERROR', error)
  }
}

const createTask = async () => {

  if (!title.value.trim()) return

  try {

    await api.post('/tasks', {
      title: title.value,
      priority: priority.value
    })

    title.value = ''
    priority.value = 'medium'

    fetchTasks(currentPage.value)
    toast.success('Task Created')
  } catch (error) {

    console.log('CREATE ERROR', error)
  }
}

const deleteTask = async (id: number) => {

  if (!confirm('Delete this task?')) return

  try {

    await api.delete(`/tasks/${id}`)

    fetchTasks(currentPage.value)

  toast.success('Task Deleted')
} catch (error) {

    console.log('DELETE ERROR', error)
  }
}

const startEdit = (task: any) => {
  
  editingId.value = task.id

  title.value = task.title

  priority.value = task.priority
}

const updateTask = async () => {

  try {

    await api.put(`/tasks/${editingId.value}`, {
      title: title.value,
      priority: priority.value
    })

    editingId.value = null
    title.value = ''
    priority.value = 'medium'

    fetchTasks(currentPage.value)
    toast.success('Updated')
  } catch (error) {

    console.log('UPDATE ERROR', error)
  }
}

const toggleTask = async (task: any) => {

  const oldStatus = task.completed

  task.completed = task.completed ? 0 : 1

  try {

    await api.patch(`/tasks/${task.id}/toggle`)

    fetchTasks(currentPage.value)

  } catch (error) {

    task.completed = oldStatus

    console.log('TOGGLE ERROR', error)
  }
}

const changePage = (page: number) => {

  if (page < 1 || page > lastPage.value) return

  fetchTasks(page)
}

onMounted(() => {
  fetchTasks()
})
</script>

<template>

  <Navbar
    :isAdmin="true"
    :showSearch="true"
    :search="search"
    :filter="filter"
    @update:search="search = $event"
    @update:filter="filter = $event"
  />

  <div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200 p-8">

    <div class="max-w-7xl mx-auto">

      <div class="mb-10">

        <h1 class="text-4xl font-bold text-gray-800">
          Admin Dashboard
        </h1>

        <p class="text-gray-500 mt-2">
          Manage all user tasks
        </p>

      </div>

      <div class="bg-white rounded-3xl shadow-lg p-6 mb-8">

        <div class="flex flex-col md:flex-row gap-4">

          <input
            v-model="title"
            type="text"
            placeholder="Enter task title..."
            class="flex-1 border border-gray-200 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />

          <select
            v-model="priority"
            class="bg-white border border-gray-300 rounded-2xl px-4 py-3"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>

          <button
            v-if="editingId === null"
            @click="createTask"
            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl transition"
          >
            Create
          </button>

          <button
            v-else
            @click="updateTask"
            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-2xl transition"
          >
            Update
          </button>

        </div>

      </div>

      <div
        v-if="filteredTasks.length === 0"
        class="bg-white rounded-3xl shadow-lg p-10 text-center text-gray-500"
      >
        No tasks found
      </div>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
      >

        <div
          v-for="task in filteredTasks"
          :key="task.id"
          class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 hover:scale-[1.02] transition"
        >

          <div class="flex items-center justify-between mb-5">

            <span
              class="text-xs font-semibold px-3 py-1 rounded-full"
              :class="task.completed
                ? 'bg-green-100 text-green-700'
                : 'bg-yellow-100 text-yellow-700'"
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

          </div>

          <h2 class="text-2xl font-bold text-gray-800 mb-5">
            {{ task.title }}
          </h2>

          <div class="flex items-center gap-4 mb-6">

            <img
              :src="task.user?.avatar
                ? `http://127.0.0.1:8000/storage/${task.user.avatar}`
                : `https://ui-avatars.com/api/?name=${task.user?.name}`"
              class="w-14 h-14 rounded-full object-cover border-4 border-white shadow-md"
            />

            <div>

              <p class="text-sm text-gray-500">
                Created by
              </p>

              <h3 class="font-semibold text-gray-800">
                {{ task.user?.name }}
              </h3>

            </div>

          </div>

          <div class="flex gap-3">

            <button
              @click="startEdit(task)"
              class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-2xl transition"
            >
              Edit
            </button>

            <button
              @click="toggleTask(task)"
              :class="[
                'flex-1 text-white py-3 rounded-2xl transition',
                task.completed
                  ? 'bg-yellow-500 hover:bg-yellow-600'
                  : 'bg-green-500 hover:bg-green-600'
              ]"
            >
              {{ task.completed ? 'Undo' : 'Complete' }}
            </button>

            <button
              @click="deleteTask(task.id)"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl transition"
            >
              Delete
            </button>

          </div>

        </div>

      </div>

      <div
        v-if="lastPage > 1"
        class="flex justify-center items-center gap-4 mt-10"
      >

        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="bg-gray-200 px-5 py-2 rounded-xl disabled:opacity-50"
        >
          Previous
        </button>

        <span class="font-semibold text-gray-700">
          Page {{ currentPage }} of {{ lastPage }}
        </span>

        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage === lastPage"
          class="bg-blue-600 text-white px-5 py-2 rounded-xl disabled:opacity-50"
        >
          Next
        </button>

      </div>

    </div>

  </div>

</template>
