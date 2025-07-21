import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../../src/stores/useAuthStore'

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('Basic Store Functionality', () => {
    it('should initialize with empty state', () => {
      const store = useAuthStore()
      
      expect(store.token).toBeNull()
      expect(store.userId).toBeNull()
      expect(store.email).toBeNull()
      expect(store.username).toBeNull()
      expect(store.avatar).toBeNull()
    })

    it('should update user data with setUser', () => {
      const store = useAuthStore()
      const userData = {
        id: 1,
        email: 'test@example.com',
        username: 'testuser',
        avatar: 'img/avatar/1.png'
      }

      store.setUser(userData)

      expect(store.userId).toBe(1)
      expect(store.email).toBe('test@example.com')
      expect(store.username).toBe('testuser')
      expect(store.avatar).toBe('img/avatar/1.png')
    })

    it('should clear data on logout', () => {
      const store = useAuthStore()
      
      // Set some data first
      store.setUser({
        id: 1,
        email: 'test@example.com',
        username: 'testuser',
        avatar: 'img/avatar/1.png'
      })

      // Logout should clear everything
      store.logout()

      expect(store.token).toBeNull()
      expect(store.userId).toBeNull()
      expect(store.email).toBeNull()
      expect(store.username).toBeNull()
      expect(store.avatar).toBeNull()
    })

    it('should return false for isAuthenticated when no token', () => {
      const store = useAuthStore()
      expect(store.isAuthenticated).toBe(false)
    })

    it('should update avatar with setAvatar', () => {
      const store = useAuthStore()
      const newAvatar = 'img/avatar/2.png'

      store.setAvatar(newAvatar)

      expect(store.avatar).toBe(newAvatar)
    })
  })
})
