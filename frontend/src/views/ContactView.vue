<template>
  <!-- Conteneur principal centré avec largeur max -->
  <div
    class="max-w-2xl mx-auto mb-16 px-6 py-8 rounded-3xl shadow-2xl bg-gradient-to-br from-[#0f172a] to-[#1e293b] text-white">

    <!-- Titre -->
    <h1 class="text-4xl font-extrabold mb-8 text-center text-cyan-400 drop-shadow">
      📨 Contactez-nous
    </h1>

    <!-- Formulaire -->
    <form @submit.prevent="handleSubmit" class="space-y-6">

      <!-- Nom -->
      <div>
        <label class="block mb-2 text-sm font-medium text-cyan-200">Nom complet</label>
        <InputText v-model="form.name" placeholder="Votre nom" required
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-2 text-sm font-medium text-cyan-200">Adresse e-mail</label>
        <InputText v-model="form.email" placeholder="email@example.com" type="email" required
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Message -->
      <div>
        <label class="block mb-2 text-sm font-medium text-cyan-200">Message</label>
        <Textarea v-model="form.message" rows="5" placeholder="Écrivez votre message ici..." required
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Champ caché pour le nom du site -->
      <input type="hidden" v-model="form.siteName" />

      <!-- Bouton envoyer -->
      <Button label="Envoyer le message" type="submit" icon="pi pi-send" :loading="sending"
        class="w-full justify-center bg-[#00F0FF] hover:bg-cyan-400 text-black font-bold rounded-xl py-3 transition-all duration-300" />
    </form>

    <!-- Messages de feedback -->
    <p v-if="sent" class="mt-6 text-green-400 text-center animate-fade-in">
      ✅ Merci pour votre message ! Il a été envoyé avec succès.
    </p>

    <p v-if="error" class="mt-6 text-red-400 text-center animate-fade-in">
      ❌ Erreur lors de l'envoi : {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import emailjs from '@emailjs/browser'

const form = ref({
  name: '',
  email: '',
  message: '',
  siteName: 'Roomies'
})

const sent = ref(false)
const error = ref('')
const sending = ref(false)

// Configuration EmailJS depuis les variables d'environnement
const EMAILJS_SERVICE_ID = import.meta.env.VITE_EMAILJS_SERVICE_ID || 'service_xxxxxxx'
const EMAILJS_TEMPLATE_ID = import.meta.env.VITE_EMAILJS_TEMPLATE_ID || 'template_xxxxxxx'
const EMAILJS_PUBLIC_KEY = import.meta.env.VITE_EMAILJS_PUBLIC_KEY || 'your_public_key_here'
const TO_EMAIL = import.meta.env.VITE_TO_EMAIL || 'votre-email@gmail.com'

async function handleSubmit() {
  if (!form.value.name || !form.value.email || !form.value.message) {
    error.value = "Merci de remplir tous les champs."
    setTimeout(() => (error.value = ''), 5000)
    return
  }

  // Reset des messages
  sent.value = false
  error.value = ''
  sending.value = true

  try {
    // Préparer les données pour EmailJS
    const templateParams = {
      from_name: form.value.name,
      from_email: form.value.email,
      message: form.value.message,
      site_name: form.value.siteName, // Champ caché avec le nom du site
      to_email: TO_EMAIL, // Email de destination depuis les variables d'env
      website: 'Roomies - Plateforme de Jeux en Ligne',
      source_url: window.location.href,
      timestamp: new Date().toLocaleString('fr-FR'),
    }

    // Envoyer l'email via EmailJS
    const response = await emailjs.send(
      EMAILJS_SERVICE_ID,
      EMAILJS_TEMPLATE_ID,
      templateParams,
      EMAILJS_PUBLIC_KEY
    )

    console.log('Email envoyé avec succès:', response)

    // Affiche le message de succès
    sent.value = true

    // Réinitialise le formulaire
    form.value = { name: '', email: '', message: '', siteName: 'Roomies' }

    // Masque le message après 10 secondes
    setTimeout(() => (sent.value = false), 10000)

  } catch (err) {
    console.error('Erreur envoi email:', err)
    error.value = 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer.'
    setTimeout(() => (error.value = ''), 8000)
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
/* Animation douce pour afficher le message de succès */
@keyframes fade-in {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}
</style>
