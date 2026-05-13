<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/axios'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')

const error = ref('')

const register = async () => {
  error.value = ''

  try {
    const response = await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', response.data.token)

    router.push('/')

  } catch (err: any) {

    // Laravel validation errors
    if (err.response?.data?.errors) {

      const errors = err.response.data.errors

      if (errors.name) {
        error.value = errors.name[0]
      }
      else if (errors.email) {
        error.value = errors.email[0]
      }
      else if (errors.password) {
        error.value = errors.password[0]
      }

    } else {
      error.value = 'Registration failed'
    }
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200 flex items-center justify-center p-6">

    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md border border-gray-100">

      <h1 class="text-4xl font-bold text-center text-gray-800 mb-2">
        Create Account
      </h1>

      <p class="text-center text-gray-500 mb-8">
        Start managing your tasks
      </p>

      <!-- ERROR -->
      <div
        v-if="error"
        class="bg-red-100 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-5"
      >
        {{ error }}
      </div>

      <div class="space-y-5">

        <input
          v-model="name"
          type="text"
          placeholder="Username"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <input
          v-model="email"
          type="email"
          placeholder="Email"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <input
          v-model="password"
          type="password"
          placeholder="Password"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <button
          @click="register"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition"
        >
          Register
        </button>

      </div>

      <p class="text-center text-gray-500 mt-6">
        Already have an account?

        <router-link
          to="/login"
          class="text-blue-600 font-medium hover:underline"
        >
          Login
        </router-link>

      </p>

    </div>

  </div>
</template>