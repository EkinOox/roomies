<script setup lang="ts">
import { RouterView } from 'vue-router'
import Navigation from './components/Navigation.vue'
</script>

<template>
  <Navigation />


  <main class="px-4">
    <RouterView />
  </main>
</template>
