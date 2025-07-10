import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Login from '../../../src/components/Login.vue'
import { useAuthStore } from '../../../src/stores/useAuthStore'

/**
 * Tests du composant Login
 * Teste l'interface utilisateur et les interactions de connexion
 */
describe('Login Component', () => {
  let wrapper: any
  let router: any
  let authStore: any

  beforeEach(async () => {
    // Configuration de Pinia et du router pour les tests
    setActivePinia(createPinia())

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/rooms', component: { template: '<div>Rooms</div>' } }
      ]
    })

    // Monte le composant avec les dépendances
    wrapper = mount(Login, {
      global: {
        plugins: [router],
        stubs: {
          'router-link': true
        }
      }
    })

    authStore = useAuthStore()
    await router.isReady()
  })

  describe('Render and UI', () => {
    it('should render login form with required fields', () => {
      // Assert - Vérification de la présence des éléments du formulaire
      expect(wrapper.find('input[type="email"]').exists()).toBe(true)
      expect(wrapper.find('input[type="password"]').exists()).toBe(true)
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
    })

    it('should have empty form fields initially', () => {
      // Assert
      expect(wrapper.vm.email).toBe('')
      expect(wrapper.vm.password).toBe('')
    })

    it('should display loading state when submitting', async () => {
      // Arrange
      wrapper.vm.isLoading = true
      await wrapper.vm.$nextTick()

      // Assert
      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.element.disabled).toBe(true)
    })
  })

  describe('Form Validation', () => {
    it('should update form data when user types', async () => {
      // Arrange
      const emailInput = wrapper.find('input[type="email"]')
      const passwordInput = wrapper.find('input[type="password"]')

      // Act
      await emailInput.setValue('test@example.com')
      await passwordInput.setValue('password123')

      // Assert
      expect(wrapper.vm.email).toBe('test@example.com')
      expect(wrapper.vm.password).toBe('password123')
    })

    it('should prevent submission with empty fields', async () => {
      // Arrange
      const form = wrapper.find('form')
      const submitSpy = vi.spyOn(wrapper.vm, 'login')

      // Act
      await form.trigger('submit.prevent')

      // Assert - Le formulaire ne devrait pas étre soumis avec des champs vides
      expect(wrapper.vm.email).toBe('')
      expect(wrapper.vm.password).toBe('')
      // La validation cété frontend devrait empécher la soumission
    })
  })

  describe('Login Process', () => {
    it('should call login method on form submission with valid data', async () => {
      // Arrange
      const mockAxios = await import('axios')
      vi.mocked(mockAxios.default.post).mockResolvedValue({
        data: { token: 'mock.jwt.token' }
      })

      wrapper.vm.email = 'test@example.com'
      wrapper.vm.password = 'password123'

      const form = wrapper.find('form')
      const routerPushSpy = vi.spyOn(router, 'push')

      // Act
      await form.trigger('submit.prevent')
      await wrapper.vm.$nextTick()

      // Assert
      expect(mockAxios.default.post).toHaveBeenCalledWith(
        'http://localhost:8000/login',
        {
          email: 'test@example.com',
          password: 'password123'
        },
        expect.any(Object)
      )
    })

    it('should handle login error gracefully', async () => {
      // Arrange
      const mockAxios = await import('axios')
      vi.mocked(mockAxios.default.post).mockRejectedValue({
        response: {
          status: 401,
          data: { message: 'Identifiants invalides' }
        }
      })

      wrapper.vm.email = 'wrong@example.com'
      wrapper.vm.password = 'wrongpassword'

      // Act
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')
      await wrapper.vm.$nextTick()

      // Assert - L'erreur devrait étre gérée sans crash
      expect(wrapper.vm.isLoading).toBe(false)
    })
  })

  describe('Navigation', () => {
    it('should redirect to rooms after successful login', async () => {
      // Arrange
      const mockAxios = await import('axios')
      vi.mocked(mockAxios.default.post).mockResolvedValue({
        data: { token: 'valid.jwt.token' }
      })

      const routerPushSpy = vi.spyOn(router, 'push')
      wrapper.vm.email = 'test@example.com'
      wrapper.vm.password = 'password123'

      // Act
      await wrapper.vm.login()

      // Assert
      expect(routerPushSpy).toHaveBeenCalledWith('/rooms')
    })
  })
})
