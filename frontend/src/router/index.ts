import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AuthView from '../views/AuthView.vue'
import ProfileView from '../views/ProfileView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/auth',
      name: 'auth',
      component: AuthView,
    },

    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: { requiresAuth: true },
    },
  ],
})

// Guard global
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.name === 'auth' && token) {
    next({ name: 'home' })
  } else {
    next()
  }

  if (to.meta.requiresAuth && !token) {
    next({ name: 'auth' })
  }
})

export default router
