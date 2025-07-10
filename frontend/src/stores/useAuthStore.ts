// stores/useAuthStore.ts
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const isAuthenticated = computed(() => !!token.value && !isTokenExpired())

  const userId = ref<number | null>(null)
  const username = ref<string | null>(null)
  const email = ref<string | null>(null)
  const avatar = ref<string | null>(null)
  const roles = ref<string[]>([]) // Ajout des r�les

  function setToken(newToken: string | null) {
    token.value = newToken

    if (newToken) {
      localStorage.setItem('token', newToken)

      try {
        const payloadBase64 = newToken.split('.')[1]
        const payload = JSON.parse(atob(payloadBase64))

        userId.value = payload.id
        username.value = payload.username
        email.value = payload.email
        avatar.value = payload.avatar
        roles.value = payload.roles || [] // Extraction des r�les du token
      } catch (e) {
        console.error('Erreur de décodage du token JWT :', e)
        logout()
      }
    } else {
      localStorage.removeItem('token')
      username.value = null
      email.value = null
      userId.value = null
      avatar.value = null
      roles.value = [] // Reset des r�les
    }
  }

  function setUser(user: { id: number; username: string; email: string; avatar: string }) {
    userId.value = user.id
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
    userId.value = null
    avatar.value = null
    roles.value = [] // Reset des r�les
  }

  function isTokenExpired(): boolean {
    if (!token.value) return true

    try {
      const payloadBase64 = token.value.split('.')[1]
      const payload = JSON.parse(atob(payloadBase64))
      const exp = payload.exp
      const now = Math.floor(Date.now() / 1000)
      return exp < now
    } catch (e) {
      return true
    }
  }

  // Computed pour v�rifier si l'utilisateur est admin
  const isAdmin = computed(() => {
    return roles.value.includes('ROLE_ADMIN')
  })

  return {
    token,
    isAuthenticated,
    username,
    email,
    avatar,
    userId,
    roles, // Exposition des r�les
    isAdmin, // Exposition du computed isAdmin
    setToken,
    setUser,
    setAvatar,
    logout,
    isTokenExpired,
  }
})
