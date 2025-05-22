<template>
  <div class="min-h-screen flex items-center justify-center bg-[--color-background]">
    <div class="w-full max-w-md p-8 rounded-2xl bg-[--color-background-soft]">
      <div class="flex justify-between mb-6">
        <button
          @click="mode = 'login'"
          :class="buttonClass('login')"
        >
          Connexion
        </button>
        <button
          @click="mode = 'register'"
          :class="buttonClass('register')"
        >
          Inscription
        </button>
      </div>
      <component :is="currentComponent" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'

const mode = ref('login')
const currentComponent = computed(() => (mode.value === 'login' ? Login : Register))

function buttonClass(type) {
  return [
    'w-1/2 py-2 rounded-xl text-sm font-semibold transition duration-300',
    mode.value === type
      ? 'bg-white text-[--color-primary] shadow-inner'
      : 'bg-[--color-background] text-gray-500 hover:text-[--color-primary] hover:bg-white'
  ]
}
</script>
