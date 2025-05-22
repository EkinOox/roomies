<template>
  <form @submit.prevent="register" class="space-y-6 max-w-md mx-auto p-8 rounded-2xl shadow-xl bg-[var(--color-background-soft)] transition">
    <h2 class="text-3xl font-bold text-center text-[var(--color-primary)]">Inscription</h2>

    <div>
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="w-full p-3 rounded-xl bg-[var(--color-background-mute)] text-[var(--color-text)] placeholder-gray-400 dark:placeholder-gray-500 shadow-inner focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition"
        required
      />
    </div>

    <div>
      <input
        v-model="username"
        type="text"
        placeholder="Nom d'utilisateur"
        class="w-full p-3 rounded-xl bg-[var(--color-background-mute)] text-[var(--color-text)] placeholder-gray-400 dark:placeholder-gray-500 shadow-inner focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition"
        required
      />
    </div>

    <div>
      <input
        v-model="password"
        type="password"
        placeholder="Mot de passe"
        class="w-full p-3 rounded-xl bg-[var(--color-background-mute)] text-[var(--color-text)] placeholder-gray-400 dark:placeholder-gray-500 shadow-inner focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition"
        required
      />
    </div>

    <button
      type="submit"
      class="w-full py-3 px-6 rounded-xl font-semibold text-white bg-[var(--color-primary)] hover:bg-teal-500 transition shadow-md"
    >
      S'inscrire
    </button>

    <p v-if="error" class="text-[var(--color-secondary)] text-sm text-center mt-2">
      {{ error }}
    </p>

    <p v-if="success" class="text-[var(--color-primary)] text-sm text-center mt-2">
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
