<template>
  <div class="flex items-center justify-center bg-[--color-background] z-30">
    <div class="w-full max-w-md p-8 rounded-2xl bg-[--color-background-soft]">
      <div class="flex justify-between mb-6 gap-4">
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
    'w-1/2 py-2 rounded-xl text-sm font-bold transition duration-300 uppercase tracking-widest',
    'border-2',
    mode.value === type
      ? 'text-[#00F0FF] border-[#00F0FF] shadow-[0_0_10px_#00F0FF]'
      : 'text-[#FF4FCB] border-[#FF4FCB] hover:text-white hover:bg-[#FF4FCB] hover:shadow-[0_0_15px_#FF4FCB]'
  ]
}
</script>
