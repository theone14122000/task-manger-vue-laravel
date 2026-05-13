import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import HomeView from '@/views/HomeView.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import ProfileView from '@/views/ProfileView.vue'

const routes = [
  { path: '/login', component: LoginView },
  { path: '/register', component: RegisterView },
  { path: '/', component: HomeView, meta: { requiresAuth: true } },
  { path: '/admin',component: AdminDashboard},
  {path: '/profile',component: ProfileView,meta: { requiresAuth: true }}
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return '/login'
  }

  return true
})

export default router