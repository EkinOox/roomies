<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-200">
    <div class="w-full max-w-md p-8 rounded-2xl shadow-neumorphism bg-gray-200">
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

const currentComponent = computed(() =>
  mode.value === 'login' ? Login : Register
)

function buttonClass(type) {
  return [
    'w-1/2 py-2 rounded-xl transition',
    mode.value === type
      ? 'bg-white shadow-inner text-gray-800'
      : 'bg-gray-200 shadow text-gray-500 hover:text-gray-700'
  ]
}
</script>

<style scoped>
.shadow-neumorphism {
  box-shadow: 8px 8px 16px #bebebe, -8px -8px 16px #ffffff;
}
</style>
