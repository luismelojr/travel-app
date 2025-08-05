import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '@/services/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register', 
      component: () => import('@/pages/auth/RegisterPage.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/pages/DashboardPage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/travel-requests',
      name: 'travel-requests',
      component: () => import('@/pages/TravelRequestsPage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/travel-requests/new',
      name: 'create-travel-request',
      component: () => import('@/pages/CreateTravelRequestPage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/travel-requests/:id',
      name: 'travel-request-detail',
      component: () => import('@/pages/TravelRequestDetailPage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/pages/ProfilePage.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      redirect: '/dashboard'
    }
  ]
})

// Global navigation guards
router.beforeEach((to, _from, next) => {
  const isAuthenticated = authService.isAuthenticated()

  // Redirect to login if route requires auth and user is not authenticated
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' })
    return
  }

  // Redirect to dashboard if route requires guest and user is authenticated
  if (to.meta.requiresGuest && isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }

  next()
})

export default router