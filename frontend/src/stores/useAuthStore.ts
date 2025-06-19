// stores/useAuthStore.ts
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const isAuthenticated = computed(() => !!token.value)

  const username = ref<string | null>(null)
  const email = ref<string | null>(null)
  const avatar = ref<string | null>(null)

  function setToken(newToken: string | null) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('token', newToken)
    } else {
      localStorage.removeItem('token')
    }
  }

  function setUser(user: { username: string, email: string, avatar: string }) {
    username.value = user.username
    email.value = user.email
    avatar.value = user.avatar
  }

  function setAvatar(newAvatar: string) {
    avatar.value = newAvatar
  }

  function logout() {
    setToken(null)
    username.value = null
    email.value = null
    avatar.value = null
  }

  return {
    token,
    isAuthenticated,
    username,
    email,
    avatar,
    setToken,
    setUser,
    setAvatar,
    logout,
  }
})
