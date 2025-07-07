import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AuthView from '../views/AuthView.vue'
import ProfileView from '../views/ProfileView.vue'
import RoomList from '../views/RoomListView.vue'
import Room from '../views/RoomView.vue'
import ContactView from '@/views/ContactView.vue'
import { useAuthStore } from '@/stores/useAuthStore'

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
    {
      path: '/rooms',
      name: 'RoomList',
      component: RoomList,
      meta: { requiresAuth: true },
    },
    {
      path: '/rooms/:id',
      name: 'RoomView',
      component: Room,
      meta: { requiresAuth: true },
    },
    {
      path: '/rooms/game/:slug',
      name: 'roomsByGame',
      component: RoomList,
      meta: { requiresAuth: true },
    },
    {
      path: '/contact',
      name: 'contact',
      component: ContactView,
    },
  ],
})

// Guard global
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const auth = useAuthStore()

  // Redirection si connecté et sur la page /auth
  if (to.name === 'auth' && token) {
    return next({ name: 'home' })
  }

  // Redirection si non connecté et route protégée
  if (to.meta.requiresAuth && (!auth.token || auth.isTokenExpired())) {
    auth.logout()
    return next({ name: 'auth' })
  }

  next()
})

export default router
