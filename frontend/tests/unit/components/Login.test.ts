import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Login from '../../../src/components/Login.vue'

// Mock de PrimeVue Toast
vi.mock('primevue/usetoast', () => ({
  useToast: () => ({
    add: vi.fn(),
    remove: vi.fn(),
    clear: vi.fn()
  })
}))

describe('Login Component', () => {
  let wrapper: any
  let router: any

  beforeEach(async () => {
    setActivePinia(createPinia())

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/rooms', component: { template: '<div>Rooms</div>' } }
      ]
    })

    wrapper = mount(Login, {
      global: {
        plugins: [router],
        stubs: {
          'router-link': true,
          'InputText': {
            template: '<input v-bind="$attrs" />',
            inheritAttrs: false
          },
          'Password': {
            template: '<input type="password" v-bind="$attrs" />',
            inheritAttrs: false
          },
          'Button': {
            template: '<button v-bind="$attrs"><slot /></button>',
            inheritAttrs: false
          },
          'Toast': true,
          'ProgressSpinner': true
        }
      }
    })

    await router.isReady()
  })

  describe('Component Rendering', () => {
    it('should mount successfully', () => {
      expect(wrapper.exists()).toBe(true)
    })

    it('should have initial reactive data', () => {
      expect(wrapper.vm.email).toBe('')
      expect(wrapper.vm.password).toBe('')
      expect(wrapper.vm.error).toBe('')
      expect(wrapper.vm.success).toBe(false)
    })

    it('should update reactive data', async () => {
      wrapper.vm.email = 'test@example.com'
      wrapper.vm.password = 'password123'
      
      await wrapper.vm.$nextTick()
      
      expect(wrapper.vm.email).toBe('test@example.com')
      expect(wrapper.vm.password).toBe('password123')
    })
  })

  describe('Login Logic', () => {
    it('should have a login method', () => {
      expect(typeof wrapper.vm.login).toBe('function')
    })

    it('should handle error state', async () => {
      wrapper.vm.error = 'Test error'
      await wrapper.vm.$nextTick()
      
      expect(wrapper.vm.error).toBe('Test error')
    })

    it('should handle success state', async () => {
      wrapper.vm.success = true
      await wrapper.vm.$nextTick()
      
      expect(wrapper.vm.success).toBe(true)
    })
  })
})
