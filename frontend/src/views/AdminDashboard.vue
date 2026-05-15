<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '../api/axios'
import Navbar from '../components/Navbar.vue'
import { useToast } from 'vue-toastification'
import draggable from 'vuedraggable'

const toast = useToast()

const tasks = ref<any[]>([])
const filteredTasks = ref<any[]>([])

const todoTasks = ref<any[]>([])
const progressTasks = ref<any[]>([])
const completedTasks = ref<any[]>([])

const title = ref('')
const priority = ref('medium')
const status = ref('todo')
const dueDate = ref('')

const editingId = ref<number | null>(null)

const search = ref('')
const filter = ref('all')

const currentPage = ref(1)
const lastPage = ref(1)

const splitTasks = () => {

  let filtered = [...tasks.value]

  filtered = filtered.filter((task: any) =>
    (task.title || '')
      .toLowerCase()
      .includes(search.value.toLowerCase())
  )

  if (filter.value === 'completed') {
    filtered = filtered.filter(
      (task: any) =>
        task.completed == 1 ||
        task.status === 'completed'
    )
  }

  if (filter.value === 'pending') {
    filtered = filtered.filter(
      (task: any) =>
        task.completed == 0 &&
        task.status !== 'completed'
    )
  }

  filtered.sort((a: any, b: any) =>
    (a.position || 0) - (b.position || 0)
  )

  filteredTasks.value = filtered

  todoTasks.value = filtered.filter(
    (task: any) =>
      task.status === 'todo' &&
      task.completed == 0
  )

  progressTasks.value = filtered.filter(
    (task: any) =>
      task.status === 'progress' &&
      task.completed == 0
  )

  completedTasks.value = filtered.filter(
    (task: any) =>
      task.completed == 1 ||
      task.status === 'completed'
  )
}

watch([search, filter], () => {
  splitTasks()
})

const fetchTasks = async (page = 1) => {

  try {

    const response = await api.get(`/admin/tasks?page=${page}`)

    if (response.data.data) {

      tasks.value = response.data.data

      currentPage.value = response.data.current_page || 1

      lastPage.value = response.data.last_page || 1

    } else if (Array.isArray(response.data)) {

      tasks.value = response.data

    } else {

      tasks.value = []
    }

    splitTasks()

  } catch (error: any) {

    console.log('FETCH ERROR', error)

    tasks.value = []
  }
}

const saveTaskOrder = async () => {

  try {

    const orderedTasks = [

      ...todoTasks.value.map((task, index) => ({
        id: task.id,
        status: 'todo',
        position: index
      })),

      ...progressTasks.value.map((task, index) => ({
        id: task.id,
        status: 'progress',
        position: index
      })),

      ...completedTasks.value.map((task, index) => ({
        id: task.id,
        status: 'completed',
        position: index
      }))
    ]

    await api.post('/tasks/reorder', {
      tasks: orderedTasks
    })

    toast.success('Order Saved')

  } catch (error) {

    console.log('REORDER ERROR', error)
  }
}

const onTodoChange = async () => {

  todoTasks.value.forEach((task: any) => {
    task.status = 'todo'
    task.completed = 0
  })

  await saveTaskOrder()
}

const onProgressChange = async () => {

  progressTasks.value.forEach((task: any) => {
    task.status = 'progress'
    task.completed = 0
  })

  await saveTaskOrder()
}

const onCompletedChange = async () => {

  completedTasks.value.forEach((task: any) => {
    task.status = 'completed'
    task.completed = 1
  })

  await saveTaskOrder()
}

const createTask = async () => {

  if (!title.value.trim()) return

  try {

    await api.post('/tasks', {
      title: title.value,
      priority: priority.value,
      status: status.value,
      due_date: dueDate.value,
      completed: status.value === 'completed'
    })

    title.value = ''
    priority.value = 'medium'
    status.value = 'todo'
    dueDate.value = ''

    fetchTasks(currentPage.value)

    toast.success('Task Created')

  } catch (error) {

    console.log('CREATE ERROR', error)
  }
}

const startEdit = (task: any) => {

  editingId.value = task.id

  title.value = task.title
  priority.value = task.priority
  status.value = task.status
  dueDate.value = task.due_date || ''
}

const updateTask = async () => {

  try {

    await api.put(`/tasks/${editingId.value}`, {
      title: title.value,
      priority: priority.value,
      status: status.value,
      due_date: dueDate.value,
      completed: status.value === 'completed'
    })

    editingId.value = null

    title.value = ''
    priority.value = 'medium'
    status.value = 'todo'
    dueDate.value = ''

    fetchTasks(currentPage.value)

    toast.success('Task Updated')

  } catch (error) {

    console.log('UPDATE ERROR', error)
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

const changePage = (page: number) => {

  if (
    page < 1 ||
    page > lastPage.value
  ) return

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

  <div
    class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200 dark:from-gray-950 dark:via-gray-900 dark:to-black p-8"
  >

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="mb-10">

        <h1 class="text-4xl font-bold text-gray-800 dark:text-white">
          Admin Dashboard
        </h1>

        <p class="text-sm text-gray-500 dark:text-gray-400">
          Manage all user tasks
        </p>

      </div>

      <!-- FORM -->
      <div
        class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg p-6 mb-8"
      >

        <div class="flex flex-col md:flex-row gap-4">

          <input
            v-model="title"
            type="text"
            placeholder="Enter task title..."
            class="flex-1 bg-white dark:bg-gray-800 text-black dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-4"
          />

          <select
            v-model="priority"
            class="bg-white dark:bg-gray-800 text-black dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>

          <select
            v-model="status"
            class="bg-white dark:bg-gray-800 text-black dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          >
            <option value="todo">Todo</option>
            <option value="progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>

          <input
            v-model="dueDate"
            type="date"
            class="bg-white dark:bg-gray-800 text-black dark:text-white border border-gray-300 dark:border-gray-700 rounded-2xl px-4 py-3"
          />

          <button
            v-if="editingId === null"
            @click="createTask"
            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl"
          >
            Create
          </button>

          <button
            v-else
            @click="updateTask"
            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-2xl"
          >
            Update
          </button>

        </div>

      </div>

      <!-- EMPTY -->
      <div
        v-if="filteredTasks.length === 0"
        class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg p-10 text-center text-gray-500 dark:text-gray-400"
      >
        No tasks found
      </div>

      <!-- BOARD -->
      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-3 gap-6"
      >

        <!-- TODO -->
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 shadow-lg">

          <h2 class="text-xl font-bold mb-5 text-gray-700 dark:text-white">
            Todo
          </h2>

          <draggable
            v-model="todoTasks"
            group="tasks"
            item-key="id"
            @change="onTodoChange"
            class="space-y-4 min-h-[300px]"
          >

            <template #item="{ element }">

              <div
                class="rounded-2xl p-5 border shadow-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700"
              >

                <div class="flex justify-between mb-4">

                  <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
                    {{ element.status }}
                  </span>

                  <span
                    :class="[
                      'text-xs px-3 py-1 rounded-full',

                      element.priority === 'high'
                        ? 'bg-red-100 text-red-700'
                        : element.priority === 'medium'
                          ? 'bg-yellow-100 text-yellow-700'
                          : 'bg-green-100 text-green-700'
                    ]"
                  >
                    {{ element.priority }}
                  </span>

                </div>

                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-3">
                  {{ element.title }}
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                  Due:
                  {{ element.due_date || 'No deadline' }}
                </p>

                <div
                  class="flex items-center gap-3 mb-5 bg-slate-100 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-2xl px-3 py-2"
                >

                  <img
                    :src="element.user?.avatar
                      ? `http://127.0.0.1:8000/storage/${element.user.avatar}`
                      : `https://ui-avatars.com/api/?name=${element.user?.name || 'User'}`"
                    class="w-9 h-9 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow"
                  />

                  <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    Created by
                    <span class="font-semibold text-gray-800 dark:text-white">
                      {{ element.user?.name || 'Unknown User' }}
                    </span>
                  </p>

                </div>

                <div class="flex gap-2">

                  <button
                    @click="startEdit(element)"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-xl"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteTask(element.id)"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl"
                  >
                    Delete
                  </button>

                </div>

              </div>

            </template>

          </draggable>

        </div>

        <!-- PROGRESS -->
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 shadow-lg">

          <h2 class="text-xl font-bold mb-5 text-blue-700 dark:text-blue-300">
            In Progress
          </h2>

          <draggable
            v-model="progressTasks"
            group="tasks"
            item-key="id"
            @change="onProgressChange"
            class="space-y-4 min-h-[300px]"
          >

            <template #item="{ element }">

              <div
                class="rounded-2xl p-5 border shadow-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700"
              >

                <div class="flex justify-between mb-4">

                  <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full">
                    {{ element.status }}
                  </span>

                  <span
                    :class="[
                      'text-xs px-3 py-1 rounded-full',

                      element.priority === 'high'
                        ? 'bg-red-100 text-red-700'
                        : element.priority === 'medium'
                          ? 'bg-yellow-100 text-yellow-700'
                          : 'bg-green-100 text-green-700'
                    ]"
                  >
                    {{ element.priority }}
                  </span>

                </div>

                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-3">
                  {{ element.title }}
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                  Due:
                  {{ element.due_date || 'No deadline' }}
                </p>

                <div
                  class="flex items-center gap-3 mb-5 bg-slate-100 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-2xl px-3 py-2"
                >

                  <img
                    :src="element.user?.avatar
                      ? `http://127.0.0.1:8000/storage/${element.user.avatar}`
                      : `https://ui-avatars.com/api/?name=${element.user?.name || 'User'}`"
                    class="w-9 h-9 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow"
                  />

                  <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    Created by
                    <span class="font-semibold text-gray-800 dark:text-white">
                      {{ element.user?.name || 'Unknown User' }}
                    </span>
                  </p>

                </div>

                <div class="flex gap-2">

                  <button
                    @click="startEdit(element)"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-xl"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteTask(element.id)"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl"
                  >
                    Delete
                  </button>

                </div>

              </div>

            </template>

          </draggable>

        </div>

        <!-- COMPLETED -->
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 shadow-lg">

          <h2 class="text-xl font-bold mb-5 text-green-700 dark:text-green-300">
            Completed
          </h2>

          <draggable
            v-model="completedTasks"
            group="tasks"
            item-key="id"
            @change="onCompletedChange"
            class="space-y-4 min-h-[300px]"
          >

            <template #item="{ element }">

              <div
                class="rounded-2xl p-5 border shadow-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700"
              >

                <div class="flex justify-between mb-4">

                  <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full">
                    completed
                  </span>

                  <span
                    :class="[
                      'text-xs px-3 py-1 rounded-full',

                      element.priority === 'high'
                        ? 'bg-red-100 text-red-700'
                        : element.priority === 'medium'
                          ? 'bg-yellow-100 text-yellow-700'
                          : 'bg-green-100 text-green-700'
                    ]"
                  >
                    {{ element.priority }}
                  </span>

                </div>

                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-3">
                  {{ element.title }}
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                  Due:
                  {{ element.due_date || 'No deadline' }}
                </p>

                <div
                  class="flex items-center gap-3 mb-5 bg-slate-100 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-2xl px-3 py-2"
                >

                  <img
                    :src="element.user?.avatar
                      ? `http://127.0.0.1:8000/storage/${element.user.avatar}`
                      : `https://ui-avatars.com/api/?name=${element.user?.name || 'User'}`"
                    class="w-9 h-9 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow"
                  />

                  <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    Created by
                    <span class="font-semibold text-gray-800 dark:text-white">
                      {{ element.user?.name || 'Unknown User' }}
                    </span>
                  </p>

                </div>

                <div class="flex gap-2">

                  <button
                    @click="startEdit(element)"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-xl"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteTask(element.id)"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl"
                  >
                    Delete
                  </button>

                </div>

              </div>

            </template>

          </draggable>

        </div>

      </div>

      <!-- PAGINATION -->
      <div
        v-if="lastPage > 1"
        class="flex justify-center items-center gap-4 mt-10"
      >

        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-white px-5 py-2 rounded-xl disabled:opacity-50"
        >
          Previous
        </button>

        <span class="font-semibold text-gray-700 dark:text-gray-200">
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
