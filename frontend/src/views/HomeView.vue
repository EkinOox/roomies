<template>


    <!-- Main Content -->
    <main class="z-10 relative px-6 md:px-16 pt-24 pb-32 text-center">
      <!-- Hero Section -->
      <section>
        <Motion
          tag="h1"
          :initial="{ opacity: 0, y: 40 }"
          :animate="{ opacity: 1, y: 0 }"
          transition="{ duration: 1.2, easing: 'ease-in-out' }"
          class="text-6xl md:text-7xl font-bold text-neonBlue mb-6"
        >
          Play. Chat. Connect.
        </Motion>
        <Motion
          tag="p"
          :initial="{ opacity: 0 }"
          :animate="{ opacity: 1 }"
          transition="{ delay: 0.3, duration: 1 }"
          class="text-xl text-gray-300 mb-10"
        >
          Rejoins Roomies pour jouer à des mini-jeux en temps réel avec tes amis !
        </Motion>
        <Motion
          tag="button"
          :whileHover="{ scale: 1.05, boxShadow: '0px 0px 16px #FF00CC' }"
          transition="{ duration: 0.3 }"
          class="bg-neonPink text-white px-8 py-3 rounded-full shadow-lg font-semibold"
        >
          Commencer
        </Motion>
      </section>

      <!-- Top Games Section -->
      <section class="mt-28">
        <h3 class="text-3xl font-semibold text-white mb-12">Top jeux</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
          <Motion
            v-for="game in games"
            :key="game.title"
            tag="div"
            :initial="{ opacity: 0, y: 30 }"
            :whileInView="{ opacity: 1, y: 0 }"
            viewport="{ once: true }"
            transition="{ duration: 0.6, delay: 0.1 }"
            class="p-6 rounded-2xl border-2 border-neonBlue bg-gradient-to-br from-[#0F172A]/60 to-[#1F2937]/60 backdrop-blur-sm shadow-lg hover:shadow-neon transition transform hover:-translate-y-1"
          >
            <h4 class="text-2xl font-bold text-neonBlue mb-2">{{ game.title }}</h4>
            <p class="text-gray-400 text-sm">{{ game.description }}</p>
          </Motion>
        </div>
      </section>

      <!-- Why Roomies -->
      <section class="mt-28 max-w-3xl mx-auto text-center">
        <Motion
          tag="h3"
          :initial="{ opacity: 0, scale: 0.95 }"
          :whileInView="{ opacity: 1, scale: 1 }"
          viewport="{ once: true }"
          transition="{ duration: 0.8 }"
          class="text-2xl font-semibold text-white mb-4"
        >
          Pourquoi Roomies ?
        </Motion>
        <Motion
          tag="p"
          :initial="{ opacity: 0 }"
          :whileInView="{ opacity: 1 }"
          viewport="{ once: true }"
          transition="{ delay: 0.3 }"
          class="text-gray-400"
        >
          Roomies, c’est plus qu’une plateforme de jeux. C’est un espace pour créer du lien, discuter en temps réel et partager des moments fun, où que tu sois.
        </Motion>
      </section>
    </main>

    <!-- Footer -->
    <footer class="text-center py-10 text-sm text-gray-500 border-t border-gray-700 z-10 relative">
      &copy; {{ new Date().getFullYear() }} Roomies. Tous droits réservés.
    </footer>
</template>

<script setup lang="ts">
import { Motion } from '@motionone/vue'
import Navigation from '../components/Navigation.vue'

const games = [
  {
    title: 'Morpion',
    description: 'Affronte tes amis dans une partie classique de morpion.',
  },
  {
    title: 'Mémo',
    description: 'Teste ta mémoire avec ce jeu rapide et coloré.',
  },
  {
    title: 'Quiz Rapide',
    description: 'Réponds à des questions en temps réel avec d’autres joueurs.',
  },
];
</script>


