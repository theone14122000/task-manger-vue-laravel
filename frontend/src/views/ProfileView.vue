<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../api/axios'
import Navbar from '../components/Navbar.vue'

const user = ref<any>(null)

const name = ref('')
const email = ref('')

const currentPassword = ref('')
const newPassword = ref('')

const avatar = ref<File | null>(null)

const success = ref('')
const error = ref('')

const isAdmin = ref(false)

const fetchUser = async () => {
  try {

    const response = await api.get('/me')

    user.value = response.data

    name.value = response.data.name
    email.value = response.data.email

    isAdmin.value = response.data.is_admin === 1

  } catch (err) {
    console.log(err)
  }
}

const handleFile = (e: any) => {
  avatar.value = e.target.files[0]
}

const updateProfile = async () => {

  success.value = ''
  error.value = ''

  try {

    const formData = new FormData()

    formData.append('name', name.value)
    formData.append('email', email.value)

    if (currentPassword.value) {
      formData.append('current_password', currentPassword.value)
    }

    if (newPassword.value) {
      formData.append('new_password', newPassword.value)
    }

    if (avatar.value) {
      formData.append('avatar', avatar.value)
    }

    const response = await api.post(
      '/profile/update',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    user.value = response.data.user

    success.value = 'Profile updated successfully'

    currentPassword.value = ''
    newPassword.value = ''

  } catch (err: any) {

    error.value =
      err.response?.data?.message ||
      'Profile update failed'
  }
}

onMounted(() => {
  fetchUser()
})
</script>

<template>

  <Navbar :isAdmin="isAdmin" />

  <div class="min-h-screen bg-gray-100 py-10 px-4">

    <div class="max-w-3xl mx-auto">

      <div class="bg-white rounded-3xl shadow-xl overflow-hidden">

        <!-- HEADER -->
        <div
          class="bg-gradient-to-r from-blue-600 to-indigo-700 p-10 text-white"
        >

          <div class="flex items-center gap-6">

            <img
              :src="user?.avatar
                ? `http://127.0.0.1:8000/storage/${user.avatar}`
                : `https://ui-avatars.com/api/?name=${user?.name}`"
              class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-lg"
            />

            <div>

              <h1 class="text-4xl font-bold">
                {{ user?.name }}
              </h1>

              <p class="text-blue-100 mt-2 text-lg">
                {{ user?.email }}
              </p>

            </div>

          </div>

        </div>

        <!-- BODY -->
        <div class="p-10">

          <!-- SUCCESS -->
          <div
            v-if="success"
            class="bg-green-100 border border-green-300 text-green-700 px-5 py-4 rounded-2xl mb-6"
          >
            {{ success }}
          </div>

          <!-- ERROR -->
          <div
            v-if="error"
            class="bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl mb-6"
          >
            {{ error }}
          </div>

          <div class="space-y-6">

            <!-- NAME -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700">
                Full Name
              </label>

              <input
                v-model="name"
                type="text"
                class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- EMAIL -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700">
                Email Address
              </label>

              <input
                v-model="email"
                type="email"
                class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- CURRENT PASSWORD -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700">
                Current Password
              </label>

              <input
                v-model="currentPassword"
                type="password"
                placeholder="Required to change password"
                class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- NEW PASSWORD -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700">
                New Password
              </label>

              <input
                v-model="newPassword"
                type="password"
                placeholder="Leave blank if not changing"
                class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- AVATAR -->
            <div>
              <label class="block mb-2 font-semibold text-gray-700">
                Profile Image
              </label>

              <input
                type="file"
                @change="handleFile"
                class="w-full border border-gray-300 rounded-2xl px-5 py-4 bg-white"
              />
            </div>

            <!-- BUTTON -->
            <button
              @click="updateProfile"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl text-lg font-semibold transition duration-300 shadow-lg"
            >
              Update Profile
            </button>

          </div>

        </div>

      </div>

    </div>

  </div>

</template>