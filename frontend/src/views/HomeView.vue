<template>
  <main class="relative z-20 px-6 md:px-16 pt-24 pb-32 text-center">
    <!-- Section principale avec slogan -->
    <section class="relative">
      <h1
        class="text-5xl md:text-6xl font-extrabold text-neonBlue mb-6 drop-shadow-[0_0_25px_rgba(0,255,255,0.8)]"
      >
        Play. Chat. Connect.
      </h1>

      <p class="text-lg md:text-xl text-gray-300 mb-10 max-w-2xl mx-auto">
        Rejoins Roomies pour jouer à des mini-jeux en temps réel avec tes amis !
      </p>

      <a href="/rooms">
        <button
          class="w-1/4 px-4 py-3 font-semibold rounded-md text-white
            bg-gradient-to-br from-[#FF4FCB] to-[#00F0FF]
            shadow-[0_0_25px_rgba(0,255,255,0.6)]
            hover:scale-105 transition-transform duration-300 animate-float"
        >
          Commencer
        </button>
      </a>
    </section>

    <!-- Arrière-plan néon radial -->
    <div
      class="absolute top-0 left-1/2 transform -translate-x-1/2 -z-10 w-[800px] h-[800px] rounded-full blur-3xl opacity-50 animate-gradient-x"
      style="background: radial-gradient(circle, rgba(0,255,247,0.2) 0%, transparent 70%)"
    ></div>

    <!-- Séparateur lumineux -->
    <div
      class="w-2/3 h-1 mx-auto my-24 bg-gradient-to-r from-neonPink via-neonPurple to-neonBlue rounded-full animate-glow"
    ></div>

    <!-- Section "Top jeux" -->
    <section>
      <h3
        class="text-3xl font-bold text-white mb-12 drop-shadow-[0_0_10px_rgba(255,255,255,0.4)]"
      >
        🎮 Top jeux
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <div
          v-for="game in games"
          :key="game.title"
          class="p-6 rounded-3xl border border-white/10 bg-[#0f172a]/60 backdrop-blur-md
            shadow-[inset_4px_4px_10px_rgba(255,255,255,0.05),_inset_-4px_-4px_10px_rgba(0,0,0,0.2),_0_0_20px_rgba(0,255,255,0.4)]
            hover:shadow-[0_0_25px_rgba(0,255,255,0.6)] transform hover:-translate-y-1.5 transition duration-300"
        >
          <h4
            class="text-2xl font-bold text-neonBlue mb-2 drop-shadow-[0_0_10px_rgba(0,255,255,0.6)]"
          >
            {{ game.title }}
          </h4>
          <p class="text-gray-400 text-sm">{{ game.description }}</p>
        </div>
      </div>
    </section>

    <!-- Section Communauté -->
    <section class="mt-40 text-center relative">
      <h3
        class="text-4xl md:text-5xl font-bold text-neonPurple mb-6 drop-shadow-[0_0_18px_rgba(168,85,247,0.7)] animate-pulse"
      >
        🌟 Rejoins la Communauté Roomies
      </h3>
      <p class="text-gray-300 max-w-2xl mx-auto text-lg mb-12">
        Des milliers de joueurs connectés. Discute, crée des salons privés et découvre des jeux exclusifs chaque
        semaine.
      </p>

      <div class="flex flex-wrap justify-center gap-8">
        <div
          class="glass-card border border-neonBlue/30 text-white px-8 py-6 rounded-xl w-64 shadow-[0_0_20px_rgba(0,255,255,0.2)] hover:scale-105 transition-transform"
        >
          <p
            class="text-neonBlue font-extrabold text-3xl drop-shadow-[0_0_10px_rgba(0,255,255,0.5)]"
          >
            +2K
          </p>
          <p class="text-gray-400 text-sm mt-2">Joueurs actifs</p>
        </div>
        <div
          class="glass-card border border-neonPink/30 text-white px-8 py-6 rounded-xl w-64 shadow-[0_0_20px_rgba(255,0,255,0.2)] hover:scale-105 transition-transform"
        >
          <p
            class="text-neonPink font-extrabold text-3xl drop-shadow-[0_0_10px_rgba(255,0,255,0.5)]"
          >
            +150
          </p>
          <p class="text-gray-400 text-sm mt-2">Parties en cours</p>
        </div>
        <div
          class="glass-card border border-neonPurple/30 text-white px-8 py-6 rounded-xl w-64 shadow-[0_0_20px_rgba(168,85,247,0.3)] hover:scale-105 transition-transform"
        >
          <p
            class="text-neonPurple font-extrabold text-3xl drop-shadow-[0_0_10px_rgba(168,85,247,0.5)]"
          >
            100%
          </p>
          <p class="text-gray-400 text-sm mt-2">Gratuit & en temps réel</p>
        </div>
      </div>
    </section>

    <!-- Section Call to Action -->
    <section class="mt-36">
      <div
        class="relative bg-gradient-to-br from-neonPink/30 via-neonPurple/20 to-neonBlue/30 border border-neonPurple/50 py-12 px-10 rounded-3xl shadow-[0_0_40px_rgba(168,85,247,0.3)] max-w-3xl mx-auto backdrop-blur-xl"
      >
        <!-- Halo lumineux -->
        <div
          class="absolute -top-4 -left-4 w-20 h-20 bg-neonBlue/30 rounded-full blur-2xl animate-pulse opacity-40"
        ></div>
        <div
          class="absolute -bottom-6 -right-6 w-24 h-24 bg-neonPink/30 rounded-full blur-2xl animate-ping opacity-30"
        ></div>

        <h3
          class="text-4xl font-extrabold text-white mb-4 text-center drop-shadow-[0_0_12px_rgba(255,255,255,0.2)]"
        >
          🚀 Prêt à jouer ?
        </h3>
        <p class="text-gray-300 mb-8 text-center text-lg">
          Crée ton compte et entre dans l’univers Roomies en quelques secondes.
        </p>
        <div class="flex justify-center">
          <a href="/auth">
            <button
              class="w-full px-4 py-3 font-semibold rounded-md text-white
              bg-gradient-to-br from-[#FF4FCB] to-[#00F0FF]
              shadow-[0_0_25px_rgba(0,255,255,0.6)]
              hover:scale-105 transition-transform duration-300 animate-float"
            >
              Rejoindre la communauté
            </button>
          </a>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer
    class="text-center py-10 text-sm text-gray-500 border-t border-gray-700 z-10 relative mt-20"
  >
    &copy; {{ new Date().getFullYear() }} Roomies. Tous droits réservés.
  </footer>
</template>

<script setup lang="ts">
const games = [
  {
    title: "Morpion",
    description: "Affronte tes amis dans une partie classique de morpion.",
  },
  {
    title: "Mémo",
    description: "Teste ta mémoire avec ce jeu rapide et coloré.",
  },
  {
    title: "Quiz Rapide",
    description:
      "Réponds à des questions en temps réel avec d’autres joueurs.",
  },
];
</script>

<style scoped>
@keyframes glow {
  0%,
  100% {
    box-shadow: 0 0 10px #ff00cc, 0 0 20px #00ffff;
  }

  50% {
    box-shadow: 0 0 20px #ff00cc, 0 0 30px #00ffff;
  }
}

.animate-glow {
  animation: glow 2.5s infinite ease-in-out;
  will-change: transform, opacity;
}

@keyframes gradient-x {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 10s ease infinite;
  will-change: transform, opacity;
}

.glass-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 0 25px rgba(255, 255, 255, 0.05),
    inset 2px 2px 5px rgba(255, 255, 255, 0.05),
    inset -2px -2px 5px rgba(0, 0, 0, 0.2);
  will-change: transform, opacity;
}
</style>
