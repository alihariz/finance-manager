import { createRouter, createWebHistory } from 'vue-router'
import MyLogin from '../views/MyLogin.vue'
import MyDashboard from '@/views/MyDashboard.vue'
import MyRegister from '@/views/MyRegister.vue'
import MyStatistics from '@/views/MyStatistics.vue'

const routes = [
  { path: '/', component: MyLogin }, // Default route to login page
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: MyDashboard
  },
  {
    path: '/statistics',
    name: 'Statistics',
    component: MyStatistics
  },
  {
    path: '/register',
    name: 'Register',
    component: MyRegister
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router