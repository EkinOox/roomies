import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import ProfileView from '@/views/ProfileView.vue'
import { useAuthStore } from '@/stores/useAuthStore'

/**
 * Tests d'intégration pour ProfileView
 * Teste l'interaction entre le composant, le store et les services
 */
describe('ProfileView Integration', () => {
  let wrapper: any
  let router: any
  let authStore: any
  let mockAxios: any

  beforeEach(async () => {
    // Configuration de Pinia et du router
    setActivePinia(createPinia())

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/profile', component: ProfileView }
      ]
    })

    // Mock d'axios avec des réponses réalistes
    const axiosModule = await import('axios')
    mockAxios = vi.mocked(axiosModule.default)

    // Configuration du store avec un utilisateur authentifié
    authStore = useAuthStore()
    authStore.setToken('valid.jwt.token')
    authStore.setUser({
      id: 1,
      username: 'testuser',
      email: 'test@example.com',
      avatar: 'img/avatar/1.png'
    })

    await router.isReady()
  })

  describe('Profile Loading and Display', () => {
    it('should load and display user profile on mount', async () => {
      // Arrange - Mock de la réponse API profile
      mockAxios.get.mockImplementation((url) => {
        if (url.includes('/profile')) {
          return Promise.resolve({
            data: {
              id: 1,
              username: 'testuser',
              email: 'test@example.com',
              avatar: 'img/avatar/1.png'
            }
          })
        }
        if (url.includes('/games')) {
          return Promise.resolve({
            data: [
              { id: 1, name: 'Morpion', image: 'img/games/morpion.jpg' },
              { id: 2, name: '2048', image: 'img/games/2048.jpg' }
            ]
          })
        }
        if (url.includes('/favorites')) {
          return Promise.resolve({
            data: [
              { id: 1, name: 'Morpion', description: 'Jeu de stratégie', image: 'img/games/morpion.jpg' }
            ]
          })
        }
        return Promise.reject(new Error('Unhandled URL'))
      })

      // Act
      wrapper = mount(ProfileView, {
        global: {
          plugins: [router]
        }
      })

      await wrapper.vm.$nextTick()

      // Attendre que les requétes API se terminent
      await new Promise(resolve => setTimeout(resolve, 100))

      // Assert
      expect(mockAxios.get).toHaveBeenCalledWith(
        'http://localhost:8000/api/users/profile',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer valid.jwt.token'
          })
        })
      )

      expect(wrapper.vm.profile).toBeTruthy()
      expect(wrapper.vm.allGames).toHaveLength(2)
      expect(wrapper.vm.favorites).toHaveLength(1)
    })

    it('should handle API errors gracefully', async () => {
      // Arrange - Mock d'une erreur API
      mockAxios.get.mockRejectedValue({
        response: {
          status: 401,
          data: { message: 'Unauthorized' }
        }
      })

      // Act
      wrapper = mount(ProfileView, {
        global: {
          plugins: [router]
        }
      })

      await wrapper.vm.$nextTick()
      await new Promise(resolve => setTimeout(resolve, 100))

      // Assert - Le composant ne devrait pas crash
      expect(wrapper.vm.profile).toBeNull()
    })
  })

  describe('Profile Editing', () => {
    beforeEach(async () => {
      // Setup avec profil chargé
      mockAxios.get.mockImplementation((url) => {
        if (url.includes('/profile')) {
          return Promise.resolve({
            data: {
              id: 1,
              username: 'testuser',
              email: 'test@example.com',
              avatar: 'img/avatar/1.png'
            }
          })
        }
        return Promise.resolve({ data: [] })
      })

      wrapper = mount(ProfileView, {
        global: {
          plugins: [router]
        }
      })

      await wrapper.vm.$nextTick()
      await new Promise(resolve => setTimeout(resolve, 100))
    })

    it('should enable edit mode and update form data', async () => {
      // Act - Activer le mode édition
      await wrapper.vm.enableEdit()

      // Assert
      expect(wrapper.vm.isEditing).toBe(true)
      expect(wrapper.vm.form.username).toBe('testuser')
      expect(wrapper.vm.form.email).toBe('test@example.com')
    })

    it('should submit profile changes successfully', async () => {
      // Arrange
      mockAxios.post.mockResolvedValue({
        data: {
          user: {
            id: 1,
            username: 'newusername',
            email: 'newemail@example.com',
            avatar: 'img/avatar/2.png'
          },
          token: 'new.jwt.token'
        }
      })

      await wrapper.vm.enableEdit()

      // Modifier les données du formulaire
      wrapper.vm.form.username = 'newusername'
      wrapper.vm.form.email = 'newemail@example.com'
      wrapper.vm.avatarPreview = 'img/avatar/2.png'

      // Act
      await wrapper.vm.submitChanges()

      // Assert
      expect(mockAxios.post).toHaveBeenCalledWith(
        'http://localhost:8000/api/users/update',
        {
          username: 'newusername',
          email: 'newemail@example.com',
          avatar: 'img/avatar/2.png'
        },
        expect.any(Object)
      )

      expect(wrapper.vm.isEditing).toBe(false)
      expect(wrapper.vm.profile.username).toBe('newusername')
    })

    it('should handle password validation errors', async () => {
      // Arrange
      await wrapper.vm.enableEdit()
      wrapper.vm.form.password = 'password123'
      wrapper.vm.confirmPassword = 'differentpassword'

      // Act
      await wrapper.vm.submitChanges()

      // Assert - Ne devrait pas faire d'appel API
      expect(mockAxios.post).not.toHaveBeenCalled()
    })
  })

  describe('Favorites Management', () => {
    beforeEach(async () => {
      // Setup avec données de base
      mockAxios.get.mockImplementation((url) => {
        if (url.includes('/profile')) {
          return Promise.resolve({
            data: { id: 1, username: 'testuser', email: 'test@example.com', avatar: 'img/avatar/1.png' }
          })
        }
        if (url.includes('/games')) {
          return Promise.resolve({
            data: [
              { id: 1, name: 'Morpion', image: 'img/games/morpion.jpg' },
              { id: 2, name: '2048', image: 'img/games/2048.jpg' }
            ]
          })
        }
        if (url.includes('/favorites')) {
          return Promise.resolve({ data: [] })
        }
        return Promise.resolve({ data: [] })
      })

      wrapper = mount(ProfileView, {
        global: {
          plugins: [router]
        }
      })

      await wrapper.vm.$nextTick()
      await new Promise(resolve => setTimeout(resolve, 100))
    })

    it('should add game to favorites successfully', async () => {
      // Arrange
      mockAxios.post.mockResolvedValue({ data: { message: 'Added to favorites' } })
      mockAxios.get.mockImplementation((url) => {
        if (url.includes('/favorites')) {
          return Promise.resolve({
            data: [
              { id: 1, name: 'Morpion', description: 'Jeu de stratégie', image: 'img/games/morpion.jpg' }
            ]
          })
        }
        return Promise.resolve({ data: [] })
      })

      // Act
      await wrapper.vm.addFavoriteFromList(1)

      // Assert
      expect(mockAxios.post).toHaveBeenCalledWith(
        'http://localhost:8000/api/users/favorites/1',
        {},
        expect.any(Object)
      )
      expect(wrapper.vm.favorites).toHaveLength(1)
    })

    it('should remove game from favorites successfully', async () => {
      // Arrange - Commencer avec un favori
      wrapper.vm.favorites = [
        { id: 1, name: 'Morpion', description: 'Jeu de stratégie', image: 'img/games/morpion.jpg' }
      ]

      mockAxios.delete.mockResolvedValue({ data: { message: 'Removed from favorites' } })
      mockAxios.get.mockImplementation((url) => {
        if (url.includes('/favorites')) {
          return Promise.resolve({ data: [] })
        }
        return Promise.resolve({ data: [] })
      })

      // Act
      await wrapper.vm.removeFavorite(1)

      // Assert
      expect(mockAxios.delete).toHaveBeenCalledWith(
        'http://localhost:8000/api/users/favorites/1',
        expect.any(Object)
      )
      expect(wrapper.vm.favorites).toHaveLength(0)
    })
  })

  describe('Avatar Management', () => {
    beforeEach(async () => {
      // Setup avec profil
      mockAxios.get.mockResolvedValue({
        data: { id: 1, username: 'testuser', email: 'test@example.com', avatar: 'img/avatar/1.png' }
      })

      wrapper = mount(ProfileView, {
        global: {
          plugins: [router]
        }
      })

      await wrapper.vm.$nextTick()
      await new Promise(resolve => setTimeout(resolve, 100))
    })

    it('should open avatar modal and select new avatar', async () => {
      // Arrange
      await wrapper.vm.enableEdit()

      // Act - Ouvrir le modal d'avatar
      wrapper.vm.openAvatarModal()

      // Assert
      expect(wrapper.vm.showAvatarModal).toBe(true)
      expect(wrapper.vm.selectedAvatar).toBe('img/avatar/1.png')

      // Act - Sélectionner un nouvel avatar
      wrapper.vm.selectAvatar('img/avatar/3.png')
      wrapper.vm.confirmAvatar()

      // Assert
      expect(wrapper.vm.avatarPreview).toBe('img/avatar/3.png')
      expect(wrapper.vm.showAvatarModal).toBe(false)
    })

    it('should cancel avatar selection', async () => {
      // Arrange
      await wrapper.vm.enableEdit()
      wrapper.vm.openAvatarModal()
      wrapper.vm.selectAvatar('img/avatar/5.png')

      // Act
      wrapper.vm.closeAvatarModal()

      // Assert
      expect(wrapper.vm.showAvatarModal).toBe(false)
      expect(wrapper.vm.avatarPreview).toBe('img/avatar/1.png') // Reste inchangé
    })
  })
})
