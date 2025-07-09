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
        <InputText v-model="form.name" placeholder="Votre nom"
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-2 text-sm font-medium text-cyan-200">Adresse e-mail</label>
        <InputText v-model="form.email" placeholder="email@example.com"
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Message -->
      <div>
        <label class="block mb-2 text-sm font-medium text-cyan-200">Message</label>
        <Textarea v-model="form.message" rows="5" placeholder="Écrivez votre message ici..."
          class="w-full rounded-xl px-4 py-2 text-black focus:ring-2 focus:ring-cyan-400" />
      </div>

      <!-- Bouton envoyer -->
      <Button label="Envoyer le message" type="submit" icon="pi pi-send"
        class="w-full justify-center bg-[#00F0FF] hover:bg-cyan-400 text-black font-bold rounded-xl py-3 transition-all duration-300" />
    </form>

    <!-- Message de confirmation -->
    <p v-if="sent" class="mt-6 text-green-400 text-center animate-fade-in">
      ✅ Merci pour votre message !
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'

const form = ref({
  name: '',
  email: '',
  message: ''
})

const sent = ref(false)

function handleSubmit() {
  if (!form.value.name || !form.value.email || !form.value.message) {
    alert("Merci de remplir tous les champs.")
    return
  }

  // Simulation d'envoi
  console.log("Message envoyé :", form.value)

  // Affiche le message de succès
  sent.value = true

  // Réinitialise le formulaire
  form.value = { name: '', email: '', message: '' }

  // Masque le message après 5 secondes
  setTimeout(() => (sent.value = false), 5000)
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
