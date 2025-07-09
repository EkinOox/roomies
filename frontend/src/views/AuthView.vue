<template>
  <!-- Conteneur principal centré avec fond personnalisé et un z-index élevé -->
  <div class="flex items-center justify-center bg-[--color-background] z-30">

    <!-- Carte contenant les boutons et le formulaire (login/register) -->
    <div class="w-full max-w-md p-8 rounded-2xl bg-[--color-background-soft]">

      <!-- Ligne des boutons Connexion / Inscription -->
      <div class="flex justify-between mb-6 gap-4">

        <!-- Bouton pour passer en mode Connexion -->
        <!-- Classe conditionnelle selon le mode actif -->
        <button @click="mode = 'login'" :class="buttonClass('login')">
          Connexion
        </button>

        <!-- Bouton pour passer en mode Inscription -->
        <button @click="mode = 'register'" :class="buttonClass('register')">
          Inscription
        </button>
      </div>

      <!-- Composant dynamique qui affiche soit Login.vue soit Register.vue selon `mode` -->
      <component :is="currentComponent" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Login from '../components/Login.vue'        // Composant de connexion
import Register from '../components/Register.vue'  // Composant d'inscription

// Variable réactive qui contient le mode actuel ("login" ou "register")
const mode = ref('login')

// Composant à afficher dynamiquement selon le mode sélectionné
const currentComponent = computed(() => (mode.value === 'login' ? Login : Register))

// Fonction qui retourne les classes CSS à appliquer à un bouton selon s’il est actif ou non
function buttonClass(type) {
  return [
    'w-1/2 py-2 rounded-xl text-sm font-bold transition duration-300 uppercase tracking-widest',
    'border-2',
    mode.value === type
      // Bouton actif : couleur bleue + effet lumineux
      ? 'text-[#00F0FF] border-[#00F0FF] shadow-[0_0_10px_#00F0FF]'
      // Bouton inactif : rose + effets au survol
      : 'text-[#FF4FCB] border-[#FF4FCB] hover:text-white hover:bg-[#00F0FF] hover:shadow-[0_0_15px_#00F0FF] hover:border-[#00F0FF]'
  ]
}
</script>

<style>
/* Animation pour créer un effet de flottement (peut être utilisée ailleurs via la classe `animate-float`) */
@keyframes float {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-6px);
  }
}

/* Classe utilitaire pour appliquer l'animation de flottement */
.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>
