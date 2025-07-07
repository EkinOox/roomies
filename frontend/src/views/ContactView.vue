<template>
  <div class="max-w-3xl mx-auto mt-12 p-6 bg-[#1e293b] rounded-xl shadow-lg text-white">
    <h1 class="text-3xl font-bold mb-6 text-center">📬 Contactez-nous</h1>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block mb-1 text-sm">Nom complet</label>
        <InputText v-model="form.name" placeholder="Votre nom"
                   class="w-full rounded-lg px-4 py-2" />
      </div>

      <div>
        <label class="block mb-1 text-sm">Adresse e-mail</label>
        <InputText v-model="form.email" placeholder="email@example.com"
                   class="w-full rounded-lg px-4 py-2" />
      </div>

      <div>
        <label class="block mb-1 text-sm">Message</label>
        <Textarea v-model="form.message" rows="5" placeholder="Écrivez votre message ici..."
                  class="w-full rounded-lg px-4 py-2" />
      </div>

      <Button label="Envoyer le message"
              type="submit"
              icon="pi pi-send"
              class="mt-4 w-full justify-center bg-neonPink hover:bg-pink-600 text-white" />
    </form>

    <p v-if="sent" class="mt-6 text-green-400 text-center">✅ Merci pour votre message !</p>
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

  // Reset et feedback
  sent.value = true
  form.value = { name: '', email: '', message: '' }

  setTimeout(() => (sent.value = false), 5000)
}
</script>

<style scoped>
.bg-neonPink {
  background-color: #ec4899;
}
</style>
