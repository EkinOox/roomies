import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../../src/stores/useAuthStore'

/**
 * Tests unitaires pour le store d'authentification
 * Teste la logique de gestion des tokens JWT et des données utilisateur
 */
describe('useAuthStore', () => {
  beforeEach(() => {
    // Configuration de Pinia pour chaque test
    setActivePinia(createPinia())

    // Reset du localStorage mock
    vi.clearAllMocks()
  })

  describe('Token Management', () => {
    it('should initialize with token from localStorage', () => {
      // Arrange - Mock localStorage AVANT la création du store
      const mockToken = 'mock.jwt.token'
      vi.mocked(localStorage.getItem).mockReturnValue(mockToken)

      // Mock de isTokenExpired pour éviter les erreurs de parsing
      const mockIsTokenExpired = vi.fn().mockReturnValue(false)

      // Act
      const store = useAuthStore()
      // Remplace temporairement la fonction isTokenExpired
      store.isTokenExpired = mockIsTokenExpired

      // Assert
      expect(localStorage.getItem).toHaveBeenCalledWith('token')
    })

    it('should set token and extract user data', () => {
      // Arrange
      const store = useAuthStore()

      // Token JWT avec payload encodé (base64)
      const mockPayload = {
        id: 1,
        email: 'test@example.com',
        avatar: 'img/avatar/1.png',
        roles: ['ROLE_USER']
      }

      // Création d'un token JWT factice
      const header = btoa(JSON.stringify({ typ: 'JWT', alg: 'HS256' }))
      const payload = btoa(JSON.stringify(mockPayload))
      const signature = 'fake-signature'
      const mockToken = `${header}.${payload}.${signature}`

      // Act
      store.setToken(mockToken)

      // Assert
      expect(store.token).toBe(mockToken)
      expect(store.userId).toBe(1)
      expect(store.email).toBe('test@example.com')
      expect(store.avatar).toBe('img/avatar/1.png')
      expect(localStorage.setItem).toHaveBeenCalledWith('token', mockToken)
    })

    it('should clear user data when setting null token', () => {
      // Arrange
      const store = useAuthStore()
      store.setToken('some.valid.token')

      // Act
      store.setToken(null)

      // Assert
      expect(store.token).toBeNull()
      expect(store.userId).toBeNull()
      expect(store.email).toBeNull()
      expect(store.avatar).toBeNull()
      expect(localStorage.removeItem).toHaveBeenCalledWith('token')
    })
  })

  describe('Authentication State', () => {
    it('should return false for isAuthenticated when no token', () => {
      // Arrange
      const store = useAuthStore()

      // Act & Assert
      expect(store.isAuthenticated).toBe(false)
    })

    it('should handle token expiration', () => {
      // Arrange
      const store = useAuthStore()
      const expiredPayload = {
        id: 1,
        email: 'test@example.com',
        exp: Math.floor(Date.now() / 1000) - 3600 // Expiré il y a 1 heure
      }

      const header = btoa(JSON.stringify({ typ: 'JWT', alg: 'HS256' }))
      const payload = btoa(JSON.stringify(expiredPayload))
      const expiredToken = `${header}.${payload}.signature`

      // Act
      store.setToken(expiredToken)

      // Assert
      expect(store.isAuthenticated).toBe(false)
      expect(store.isTokenExpired()).toBe(true)
    })
  })

  describe('User Management', () => {
    it('should update user data with setUser', () => {
      // Arrange
      const store = useAuthStore()
      const userData = {
        id: 2,
        username: 'testuser',
        email: 'user@test.com',
        avatar: 'img/avatar/2.png'
      }

      // Act
      store.setUser(userData)

      // Assert
      expect(store.userId).toBe(2)
      expect(store.username).toBe('testuser')
      expect(store.email).toBe('user@test.com')
      expect(store.avatar).toBe('img/avatar/2.png')
    })

    it('should update avatar with setAvatar', () => {
      // Arrange
      const store = useAuthStore()
      const newAvatar = 'img/avatar/5.png'

      // Act
      store.setAvatar(newAvatar)

      // Assert
      expect(store.avatar).toBe(newAvatar)
    })

    it('should clear all data on logout', () => {
      // Arrange
      const store = useAuthStore()
      store.setUser({
        id: 1,
        username: 'test',
        email: 'test@test.com',
        avatar: 'avatar.png'
      })

      // Act
      store.logout()

      // Assert
      expect(store.token).toBeNull()
      expect(store.userId).toBeNull()
      expect(store.username).toBeNull()
      expect(store.email).toBeNull()
    })
  })
})
