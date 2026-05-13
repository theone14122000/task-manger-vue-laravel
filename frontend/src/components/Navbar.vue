<script setup lang="ts">
import { useRouter } from 'vue-router'
import api from '../api/axios'
import { useToast } from 'vue-toastification'

const router = useRouter()
// TOAST
const toast = useToast()

const props = defineProps({
  isAdmin: Boolean,

  showSearch: {
    type: Boolean,
    default: false
  },

  search: {
    type: String,
    default: ''
  },

  filter: {
    type: String,
    default: 'all'
  }
})

const emit = defineEmits([
  'update:search',
  'update:filter'
])

const logout = async () => {

  try {

    await api.post('/logout')

    // REMOVE TOKEN
    localStorage.removeItem('token')

    // SUCCESS MESSAGE
    toast.success('Logout successful')

    // REDIRECT
    router.push('/login')

  } catch (error) {

    console.log(error)
  }
}
</script>

<template>

  <nav
    :class="[
      'sticky top-0 z-50 shadow-xl px-8 py-4',
      isAdmin
        ? 'bg-black text-white'
        : 'bg-blue-600 text-white'
    ]"
  >

    <div class="max-w-7xl mx-auto flex items-center justify-between gap-6">

      <!-- LEFT -->
      <div class="flex items-center gap-6">

        <h1 class="text-3xl font-bold">
          TaskFlow
        </h1>

        <!-- USER -->
        <template v-if="!isAdmin">

          <router-link
            to="/"
            class="hover:text-gray-300 transition font-medium"
          >
            Dashboard
          </router-link>

        </template>

        <!-- ADMIN -->
        <template v-else>

          <router-link
            to="/admin"
            class="hover:text-gray-300 transition font-medium"
          >
            Admin Panel
          </router-link>

        </template>

        <router-link
          to="/profile"
          class="hover:text-gray-300 transition font-medium"
        >
          My Profile
        </router-link>

      </div>

      <!-- SEARCH -->
      <div
        v-if="showSearch"
        class="flex items-center gap-3 flex-1 justify-center"
      >

        <input
          :value="search"
          @input="emit('update:search', ($event.target as HTMLInputElement).value)"
          type="text"
          placeholder="Search tasks..."
          class="bg-white text-black px-5 py-3 rounded-2xl w-[320px] focus:outline-none shadow-md"
        />

        <button
          @click="emit('update:filter', 'all')"
          :class="[
            'px-4 py-2 rounded-xl font-medium transition',
            filter === 'all'
              ? 'bg-white text-blue-600'
              : 'bg-blue-500 text-white'
          ]"
        >
          All
        </button>

        <button
          @click="emit('update:filter', 'completed')"
          :class="[
            'px-4 py-2 rounded-xl font-medium transition',
            filter === 'completed'
              ? 'bg-green-100 text-green-700'
              : 'bg-blue-500 text-white'
          ]"
        >
          Completed
        </button>

        <button
          @click="emit('update:filter', 'pending')"
          :class="[
            'px-4 py-2 rounded-xl font-medium transition',
            filter === 'pending'
              ? 'bg-yellow-100 text-yellow-700'
              : 'bg-blue-500 text-white'
          ]"
        >
          Pending
        </button>

      </div>

      <!-- RIGHT -->
      <button
        @click="logout"
        :class="[
          'px-5 py-2 rounded-xl transition font-medium',
          isAdmin
            ? 'bg-red-600 hover:bg-red-700 text-white'
            : 'bg-white text-blue-600 hover:bg-gray-100'
        ]"
      >
        Logout
      </button>

    </div>

  </nav>

</template>