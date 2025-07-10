<template>
  <!-- Formulaire d'inscription stylisé -->
  <!-- Empêche rechargement et lance la fonction register -->
  <form @submit.prevent="register"
    class="space-y-6 max-w-md mx-auto p-8 rounded-2xl bg-[#111827] neon-box shadow-xl transition">
    <!-- Titre -->
    <h2 class="text-3xl font-extrabold text-center text-[#00F0FF] drop-shadow-[0_0_5px_#00F0FF]">
      Inscription
    </h2>

    <!-- Champ Email -->
    <div>
      <!-- Liaison avec variable email -->
      <input v-model="email" type="email" placeholder="Email" class="neon-input" required />
    </div>

    <!-- Champ Nom d'utilisateur -->
    <div>
      <!-- Liaison avec variable username -->
      <input v-model="username" type="text" placeholder="Nom d'utilisateur" class="neon-input" required />
    </div>

    <!-- Champ Mot de passe -->
    <div>
      <!-- Liaison avec variable password -->
      <input v-model="password" type="password" placeholder="Mot de passe" class="neon-input" required />
    </div>

    <!-- Bouton d'inscription -->
    <button type="submit" class="w-full px-4 py-3 font-semibold rounded-md text-white
             bg-gradient-to-br from-[#FF4FCB] to-[#00F0FF]
             shadow-[0_0_25px_rgba(0,255,255,0.6)]
             hover:scale-105 transition-transform duration-300 animate-float">
      S'inscrire
    </button>

    <!-- Affichage d'erreur si présente -->
    <p v-if="error" class="text-[#FF4FCB] text-sm text-center mt-2">
      {{ error }}
    </p>

    <!-- Message de succès -->
    <p v-if="success" class="text-[#00F0FF] text-sm text-center mt-2">
      Inscription réussie !
    </p>
  </form>

  <Toast position="top-right" />
</template>

<script setup>
// Import des fonctions nécessaires
import { ref } from 'vue'
import axios from 'axios'
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast'

// Variables réactives pour stocker les données du formulaire et états
const email = ref('')
const username = ref('')
const password = ref('')
const error = ref('')
const success = ref(false)
const toast = useToast()

// Fonction asynchrone appelée lors de la soumission du formulaire
const register = async () => {
  // Reset des messages d'erreur et succès
  error.value = ''
  success.value = false

  try {
    // Requête POST vers l'API pour enregistrer l'utilisateur
    await axios.post('http://localhost:8000/register', {
      email: email.value,
      username: username.value,
      password: password.value,
    })

    // Si succès : reset des champs et affichage message succès
    success.value = true
    email.value = ''
    username.value = ''
    password.value = ''
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Inscription réussie '
    })
  } catch (e) {
    // Debug en console si erreur axios
    console.log('Erreur Axios:', e.response);

    // Gestion avancée des erreurs selon la réponse API
    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message
    } else if (e.response && typeof e.response.data === 'string') {
      error.value = e.response.data
    } else {
      error.value = "Erreur lors de l'inscription"
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: "Erreur lors de l'inscription",
      })
    }
  }
}
</script>
