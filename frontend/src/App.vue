<script setup lang="ts">
import { RouterView } from 'vue-router'
import Navigation from './components/Navigation.vue'
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-[#0A0A1A] via-[#0A0F2C] to-[#1A0033] text-white relative overflow-hidden z-0">
    <div class="absolute inset-0 z-0 pointer-events-none">
      <img src="/neon-bg.png" alt="buttons" class="w-full h-full object-cover animate-float" />
    </div>

    <header class="z-10 relative px-8 py-6 flex justify-center flex-col items-center">
      <Navigation />
      <div class="flex items-center">
        <img src="/logo.png" class=" h-70" alt="Roomies Logo" />
      </div>
    </header>
    <main class="px-4">
      <RouterView />
    </main>
  </div>


</template>

<style scoped>
@keyframes float {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-10px);
  }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}
</style>
