<template>
  <form
    @submit.prevent="register"
    class="space-y-6 max-w-md mx-auto p-8 rounded-2xl bg-[#111827] neon-box shadow-xl transition"
  >
    <h2 class="text-3xl font-extrabold text-center text-[#00F0FF] drop-shadow-[0_0_5px_#00F0FF]">
      Inscription
    </h2>

    <div>
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="neon-input"
        required
      />
    </div>

    <div>
      <input
        v-model="username"
        type="text"
        placeholder="Nom d'utilisateur"
        class="neon-input"
        required
      />
    </div>

    <div>
      <input
        v-model="password"
        type="password"
        placeholder="Mot de passe"
        class="neon-input"
        required
      />
    </div>

    <button
      type="submit"
      class="neon-button bg-[#FF4FCB] hover:bg-pink-500"
    >
      S'inscrire
    </button>

    <p v-if="error" class="text-[#FF4FCB] text-sm text-center mt-2">
      {{ error }}
    </p>

    <p v-if="success" class="text-[#00F0FF] text-sm text-center mt-2">
      Inscription réussie !
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const username = ref('')
const password = ref('')
const error = ref('')
const success = ref(false)

const register = async () => {
  error.value = ''
  success.value = false
  try {
    await axios.post('http://localhost:8000/register', {
      email: email.value,
      username: username.value,
      password: password.value,
    })
    success.value = true
    email.value = ''
    username.value = ''
    password.value = ''
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'inscription"
  }
}
</script>
