<template>
  <div class="flex min-h-screen bg-background-light text-text-light">

    <!-- Main Content -->
    <main class="flex-1 p-10 overflow-auto">
      <!-- Hero Section -->
      <section class="text-center py-16">
        <h2 class="text-5xl font-bold mb-4">Play. Chat. Connect.</h2>
        <p class="text-lg text-gray-soft mb-8">
          Rejoins Roomies pour jouer à des mini-jeux en temps réel avec tes amis !
        </p>
        <button
          class="px-8 py-3 rounded-full shadow-neumorphism hover:shadow-inner-soft bg-background-light text-teal font-semibold transition transform hover:scale-105"
        >
          Commencer
        </button>
      </section>

      <!-- Top Games Section -->
      <section class="mt-20">
        <h3 class="text-3xl font-semibold text-center mb-10">Top jeux</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="game in games"
            :key="game.title"
            class="rounded-2xl p-6 bg-background-light shadow-neumorphism hover:shadow-inner-soft transition cursor-pointer"
          >
            <h4 class="text-xl font-bold text-teal mb-2">{{ game.title }}</h4>
            <p class="text-gray-soft text-sm">{{ game.description }}</p>
          </div>
        </div>
      </section>

      <!-- Why Section -->
      <section class="mt-24 text-center max-w-3xl mx-auto">
        <h3 class="text-2xl font-semibold mb-4">Pourquoi Roomies ?</h3>
        <p class="text-gray-soft text-base">
          Roomies, c’est plus qu’une plateforme de jeux. C’est un espace pour créer du lien, discuter en temps réel
          et partager des moments fun, où que tu sois.
        </p>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">

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
