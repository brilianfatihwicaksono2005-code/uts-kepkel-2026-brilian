import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import HousingUnitView from '@/views/HousingUnitView.vue'
import MaintenanceView from '@/views/MaintenanceView.vue'
import PlacementView from '@/views/PlacementView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/housing-units',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true },
    },
    {
      path: '/housing-units',
      name: 'housing-units',
      component: HousingUnitView,
      meta: { requiresAuth: true },
    },
    {
      path: '/maintenance',
      name: 'maintenance',
      component: MaintenanceView,
      meta: { requiresAuth: true },
    },
    {
      path: '/placements',
      name: 'placements',
      component: PlacementView,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  if (to.name === 'login' && token) {
    return { name: 'housing-units' }
  }

  return true
})

export default router