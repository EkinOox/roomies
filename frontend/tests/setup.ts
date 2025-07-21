import { vi } from 'vitest'
import { config } from '@vue/test-utils'

/**
 * Configuration globale pour les tests Vue.js
 * Mocks et stubs nécessaires pour les tests
 */

// Mock du localStorage pour les tests
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
  length: 0,
  key: vi.fn()
}

// Mock des modules globaux
Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
  writable: true
})
global.localStorage = localStorageMock

// Configuration des stubs pour Vue Test Utils
config.global.stubs = {
  // Stub pour le router Vue
  'router-link': {
    template: '<a><slot /></a>',
    props: ['to']
  },
  'router-view': true,

  // Stub pour les icônes PrimeIcons
  'i': true,
  
  // Stub pour les composants PrimeVue
  'InputText': true,
  'Password': true,
  'Button': true,
  'Toast': true,
  'ProgressSpinner': true
}

// Mock des plugins globaux
config.global.mocks = {
  $toast: {
    add: vi.fn(),
    remove: vi.fn(),
    clear: vi.fn()
  }
}

// Provide des injections globales
config.global.provide = {
  toast: {
    add: vi.fn(),
    remove: vi.fn(),
    clear: vi.fn()
  }
}

// Mock d'Axios pour éviter les vraies requêtes HTTP
vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ data: {} })),
    post: vi.fn(() => Promise.resolve({ data: {} })),
    put: vi.fn(() => Promise.resolve({ data: {} })),
    delete: vi.fn(() => Promise.resolve({ data: {} })),
  }
}))

// Mock de Socket.IO
vi.mock('socket.io-client', () => ({
  io: vi.fn(() => ({
    on: vi.fn(),
    emit: vi.fn(),
    connect: vi.fn(),
    disconnect: vi.fn()
  }))
}))

// Mock des toasts pour éviter les erreurs
vi.mock('vue3-toastify', () => ({
  toast: {
    success: vi.fn(),
    error: vi.fn(),
    warning: vi.fn(),
    info: vi.fn()
  }
}))

// Mock de PrimeVue Toast
vi.mock('primevue/usetoast', () => ({
  useToast: vi.fn(() => ({
    add: vi.fn(),
    remove: vi.fn(),
    clear: vi.fn()
  }))
}))
