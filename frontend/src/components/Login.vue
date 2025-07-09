<template>
  <!-- Formulaire de connexion avec gestion de l'envoi via la méthode login -->
  <form @submit.prevent="login"
    class="space-y-6 max-w-md mx-auto mt-20 p-8 rounded-2xl bg-gradient-to-br from-[#0f172a]/90 to-[#1e293b]/90 backdrop-blur-xl border border-neonBlue/30 shadow-[0_0_25px_rgba(0,255,255,0.2)] neon-box transition">
    <!-- Titre du formulaire -->
    <h2 class="text-3xl font-extrabold text-center text-[#00F0FF] drop-shadow-[0_0_8px_#00F0FF]">
      Connexion
    </h2>

    <!-- Champ Email avec liaison bidirectionnelle vers la variable email -->
    <div>
      <!-- Récupère et met à jour la valeur email -->
      <!-- Type email pour validation HTML -->
      <!-- Texte indicatif dans le champ -->
      <input v-model="email" type="email" placeholder="Email"
        class="neon-input w-full px-4 py-3 rounded-md bg-[#1e293b] text-white placeholder-white/60 outline-none border border-white/10 focus:ring-2 focus:ring-[#00F0FF]/60 transition"
        required />
    </div>

    <!-- Champ Mot de passe avec liaison bidirectionnelle vers password -->
    <div>
      <!-- Récupère et met à jour la valeur password -->
      <!-- Masque le texte -->
      <input v-model="password" type="password" placeholder="Mot de passe" class="neon-input w-full px-4 py-3 rounded-md bg-[#1e293b] text-white placeholder-white/60 outline-none border
      border-white/10 focus:ring-2 focus:ring-[#FF4FCB]/60 transition" required />
    </div>

    <!-- Bouton pour soumettre le formulaire -->
    <button type="submit" class="w-full px-4 py-3 font-semibold rounded-md text-white
             bg-gradient-to-br from-[#FF4FCB] to-[#00F0FF]
             shadow-[0_0_25px_rgba(0,255,255,0.6)]
             hover:scale-105 transition-transform duration-300 animate-float">
      Se connecter
    </button>

    <!-- Affichage du message d'erreur si la variable error est définie -->
    <p v-if="error" class="text-[#FF4FCB] text-sm text-center mt-2">
      {{ error }}
    </p>

    <!-- Affichage du message de succès si success est vrai -->
    <p v-if="success" class="text-[#00F0FF] text-sm text-center mt-2">
      Connexion réussie !
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'              // Import des refs réactives de Vue
import axios from 'axios'              // Import axios pour requêtes HTTP
import { useRouter } from 'vue-router'// Import pour navigation programmatique
import { useAuthStore } from '@/stores/useAuthStore' // Store de gestion auth
import { toast } from 'vue3-toastify' // Notifications toast
import 'vue3-toastify/dist/index.css' // Styles toast

// Variables réactives liées au formulaire
const email = ref('')
const password = ref('')
const error = ref('')
const success = ref(false)

// Accès au router pour naviguer
const router = useRouter()
// Accès au store d'authentification
const auth = useAuthStore()

// Fonction async déclenchée au submit du formulaire
const login = async () => {
  error.value = ''     // Reset erreur
  success.value = false// Reset succès

  try {
    // Requête POST login vers API avec email + password
    const response = await axios.post('http://localhost:8000/login', {
      email: email.value,
      password: password.value,
    })

    // Récupération du token JWT dans la réponse
    const token = response.data.token
    // Stockage du token dans le store
    auth.setToken(token)

    // Requête pour récupérer les infos du profil avec token en header
    const profileResponse = await axios.get('http://localhost:8000/api/users/profile', {
      headers: {
        Authorization: `Bearer ${token}`, // Authentification Bearer
      },
    })

    // Stockage des infos utilisateur dans le store
    auth.setUser(profileResponse.data)

    // Indique que la connexion a réussi
    success.value = true
    // Reset champs formulaire
    email.value = ''
    password.value = ''
    // Notification succès
    toast.success("Vous êtes connecté !")
    // Redirection vers la page d'accueil
    router.push('/')
  } catch (e) {
    // Gestion des erreurs : message d'erreur API ou message générique
    error.value = e.response?.data?.message || 'Identifiants invalides'
    toast.error("Identifiants invalides")
  }
}
</script>
