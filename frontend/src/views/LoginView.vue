<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

import api from '../api/axios'

const router = useRouter()

// TOAST
const toast = useToast()

// FORM DATA
const email = ref('')
const password = ref('')

// ERROR MESSAGE
const error = ref('')

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

const login = async () => {

  try {

    // API REQUEST
    const response = await api.post('/login', {

      email: email.value,
      password: password.value
    })

    // SAVE TOKEN
    localStorage.setItem('token', response.data.token)

    // SUCCESS TOAST
    toast.success('Login successful')

    // REDIRECT AFTER DELAY
    setTimeout(() => {

      if (response.data.user.is_admin) {

        router.push('/admin')

      } else {

        router.push('/')
      }

    }, 1000)

  } catch (err: any) {

    error.value =
      err.response?.data?.message || 'Login failed'

    // ERROR TOAST
    toast.error(error.value)
  }
}
</script>

<template>

  <div
    class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200 flex items-center justify-center p-6"
  >

    <!-- CARD -->
    <div
      class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md border border-gray-100"
    >

      <!-- TITLE -->
      <h1 class="text-4xl font-bold text-center text-gray-800 mb-2">
        Welcome Back
      </h1>

      <p class="text-center text-gray-500 mb-8">
        Login to continue
      </p>

      <!-- ERROR -->
      <div
        v-if="error"
        class="bg-red-100 text-red-600 px-4 py-3 rounded-xl mb-4"
      >
        {{ error }}
      </div>

      <!-- FORM -->
      <div class="space-y-5">

        <!-- EMAIL -->
        <input
          v-model="email"
          type="email"
          placeholder="Email"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <!-- PASSWORD -->
        <input
          v-model="password"
          type="password"
          placeholder="Password"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <!-- LOGIN BUTTON -->
        <button
          @click="login"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition"
        >
          Login
        </button>

      </div>

      <!-- REGISTER -->
      <p class="text-center text-gray-500 mt-6">

        Don't have an account?

        <router-link
          to="/register"
          class="text-blue-600 font-medium hover:underline"
        >
          Register
        </router-link>

      </p>

    </div>

  </div>

</template>