<template>
  <form @submit.prevent="register" class="space-y-5">
    <h2 class="text-2xl font-bold text-center text-gray-700">Inscription</h2>

    <div>
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="w-full p-3 my-4 rounded-xl bg-gray-200 shadow-inner text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
        required
      />
    </div>

    <div>
      <input
        v-model="username"
        type="text"
        placeholder="Nom d'utilisateur"
        class="w-full p-3 my-4 rounded-xl bg-gray-200 shadow-inner text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
        required
      />
    </div>

    <div>
      <input
        v-model="password"
        type="password"
        placeholder="Mot de passe"
        class="w-full p-3 my-4 rounded-xl bg-gray-200 shadow-inner text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
        required
      />
    </div>

    <button type="submit" class="bg-blue-500 text-white font-semibold py-3 px-6 rounded-xl shadow hover:bg-blue-600 transition w-full">
      S'inscrire
    </button>

    <p v-if="error" class="text-red-500 text-sm text-center mt-2">
      {{ error }}
    </p>

    <p v-if="success" class="text-green-600 text-sm text-center mt-2">
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
    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message
    } else {
      error.value = "Erreur lors de l'inscription"
    }
  }
}
</script>
