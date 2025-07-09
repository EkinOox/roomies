<!-- components/FavoriteModal.vue -->
<template>
  <!-- Conteneur plein écran avec effet flou en arrière-plan pour simuler une modale -->
  <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm">

    <!-- Boîte principale de la modale avec design sombre, arrondi et ombre turquoise -->
    <div class="bg-[#1f1f2e] p-6 rounded-2xl shadow-[0_0_30px_rgba(0,255,255,0.3)] max-w-4xl w-full relative">

      <!-- Titre de la modale -->
      <h2 class="text-xl font-bold mb-4 text-neonBlue">Ajouter un jeu favori</h2>

      <!-- Grille responsive contenant la liste des jeux disponibles -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-h-[400px] overflow-y-auto">

        <!-- Boucle d'affichage des jeux passés via la prop 'games' -->
        <div
          v-for="game in games"
          :key="game.id"
          class="bg-[#2b2b3c] p-4 rounded-xl cursor-pointer border border-neonBlue/20 hover:shadow-[0_0_12px_rgba(0,255,255,0.3)] transition"
          @click="$emit('add', game.id)"><!-- Émet un événement 'add' avec l'id du jeu quand on clique dessus -->
          <!-- Image du jeu -->
          <img :src="game.image" alt="Image" class="w-full h-32 object-cover rounded mb-2" />

          <!-- Nom du jeu -->
          <h3 class="text-lg font-semibold text-neonBlue truncate">{{ game.name }}</h3>

          <!-- Description du jeu (limité à 3 lignes) -->
          <p class="text-sm text-gray-400 line-clamp-3">{{ game.description }}</p>
        </div>
      </div>

      <!-- Bouton pour fermer la modale -->
      <div class="mt-6 flex justify-end">
        <button @click="$emit('close')" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-xl">
          Fermer
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>

// Déclare la propriété attendue : une liste de jeux
defineProps({ games: Array })

// Déclare les événements personnalisés que ce composant peut émettre : 'add' pour ajouter un jeu, 'close' pour fermer la modale
defineEmits(['add', 'close'])
</script>
