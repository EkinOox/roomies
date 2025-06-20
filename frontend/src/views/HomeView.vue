<template>
  <main class="relative z-20 px-6 md:px-16 pt-24 pb-32 text-center">
    <!-- Hero Section -->
    <section class="relative">
      <Motion
        tag="h1"
        :initial="{ opacity: 0, y: 40 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 1.2, easing: 'ease-in-out' }"
        class="text-6xl md:text-7xl font-extrabold text-neonBlue mb-6 drop-shadow-[0_0_15px_rgba(0,255,255,0.6)]"
      >
        Play. Chat. Connect.
      </Motion>
      <Motion
        tag="p"
        :initial="{ opacity: 0 }"
        :animate="{ opacity: 1 }"
        :transition="{ delay: 0.3, duration: 1 }"
        class="text-xl text-gray-300 mb-10"
      >
        Rejoins Roomies pour jouer à des mini-jeux en temps réel avec tes amis !
      </Motion>
      <Motion
        tag="button"
        :whileHover="{ scale: 1.05, boxShadow: '0px 0px 16px #FF00CC' }"
        :transition="{ duration: 0.3 }"
        class="bg-neonPink text-white px-8 py-3 rounded-full shadow-[0_0_15px_#FF00CC] font-semibold animate-pulse"
      >
        Commencer
      </Motion>
    </section>

    <!-- Neon Background -->
    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -z-10 w-[800px] h-[800px] bg-gradient-radial from-[#00fff7]/10 to-transparent rounded-full blur-3xl opacity-40 animate-gradient-x"></div>

    <!-- Divider -->
    <div class="w-2/3 h-1 mx-auto my-24 bg-gradient-to-r from-neonPink via-neonPurple to-neonBlue rounded-full animate-glow"></div>

    <!-- Top Games Section -->
    <section>
      <h3 class="text-3xl font-bold text-white mb-12 drop-shadow-[0_0_10px_rgba(255,255,255,0.3)]">
        Top jeux
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <div
          v-for="game in games"
          :key="game.title"
          class="p-6 rounded-2xl border-2 border-neonBlue bg-gradient-to-br from-[#0F172A]/60 to-[#1F2937]/60 backdrop-blur-sm shadow-lg hover:shadow-neon transform hover:-translate-y-1 transition duration-300"
        >
          <Motion
            :initial="{ opacity: 0, y: 30 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.6 }"
          >
            <h4 class="text-2xl font-bold text-neonBlue mb-2">{{ game.title }}</h4>
            <p class="text-gray-400 text-sm">{{ game.description }}</p>
          </Motion>
        </div>
      </div>
    </section>

    <!-- Community Section -->
    <section class="mt-28">
      <h3 class="text-3xl font-bold text-neonPurple mb-8 drop-shadow-[0_0_10px_rgba(168,85,247,0.6)]">
        🌟 Communauté Roomies
      </h3>
      <p class="text-gray-300 max-w-xl mx-auto mb-10">
        Intègre une communauté dynamique de joueurs passionnés, discute en live, crée des salons privés et découvre des jeux chaque semaine.
      </p>
      <div class="flex flex-wrap justify-center gap-6">
        <div class="bg-[#1a1a2e] border border-neonBlue/20 p-6 rounded-xl shadow-[0_0_15px_rgba(0,255,255,0.2)]">
          <p class="text-neonBlue font-bold text-lg">+2K</p>
          <p class="text-gray-400 text-sm">Joueurs actifs</p>
        </div>
        <div class="bg-[#1a1a2e] border border-neonPink/20 p-6 rounded-xl shadow-[0_0_15px_rgba(255,0,170,0.2)]">
          <p class="text-neonPink font-bold text-lg">+150</p>
          <p class="text-gray-400 text-sm">Parties en cours</p>
        </div>
        <div class="bg-[#1a1a2e] border border-neonPurple/20 p-6 rounded-xl shadow-[0_0_15px_rgba(168,85,247,0.2)]">
          <p class="text-neonPurple font-bold text-lg">100%</p>
          <p class="text-gray-400 text-sm">Gratuit & en temps réel</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="mt-28">
      <Motion
        :initial="{ opacity: 0 }"
        :animate="{ opacity: 1 }"
        :transition="{ duration: 1 }"
      >
        <div class="bg-gradient-to-r from-neonPink/30 to-neonBlue/30 border border-neonPurple/50 py-10 px-8 rounded-3xl shadow-[0_0_25px_rgba(255,0,255,0.2)]">
          <h3 class="text-3xl font-bold text-white mb-4 z-20 relative">Prêt à jouer ?</h3>
          <p class="text-gray-300 mb-6">Crée un compte ou connecte-toi pour rejoindre l'aventure Roomies.</p>
          <button class="px-8 py-3 bg-neonBlue text-white font-semibold rounded-xl hover:shadow-[0_0_20px_rgba(0,255,255,0.8)] transition">
            Rejoindre la communauté
          </button>
        </div>
      </Motion>
    </section>
  </main>

  <!-- Footer -->
  <footer class="text-center py-10 text-sm text-gray-500 border-t border-gray-700 z-10 relative mt-20">
    &copy; {{ new Date().getFullYear() }} Roomies. Tous droits réservés.
  </footer>
</template>

<script setup lang="ts">
import { Motion } from '@motionone/vue'

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
]
</script>

<style scoped>
@keyframes glow {
  0%, 100% { box-shadow: 0 0 10px #FF00CC, 0 0 20px #00FFFF; }
  50% { box-shadow: 0 0 20px #FF00CC, 0 0 30px #00FFFF; }
}
.animate-glow {
  animation: glow 2.5s infinite ease-in-out;
}

@keyframes gradient-x {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 10s ease infinite;
}
</style>
