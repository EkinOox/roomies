<template>
  <form @submit.prevent="login"
    class="space-y-6 max-w-md mx-auto p-8 rounded-2xl shadow-xl bg-[var(--color-background-soft)] transition">
    <h2 class="text-3xl font-bold text-center text-[var(--color-primary)]">Connexion</h2>

    <div>
      <input v-model="email" type="email" placeholder="Email"
        class="w-full p-3 rounded-xl bg-[var(--color-background-mute)] text-[var(--color-text)] placeholder-gray-400 dark:placeholder-gray-500 shadow-inner focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition"
        required />
    </div>

    <div>
      <input v-model="password" type="password" placeholder="Mot de passe"
        class="w-full p-3 rounded-xl bg-[var(--color-background-mute)] text-[var(--color-text)] placeholder-gray-400 dark:placeholder-gray-500 shadow-inner focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition"
        required />
    </div>

    <button type="submit"
      class="w-full py-3 px-6 rounded-xl font-semibold text-white bg-[var(--color-primary)] hover:bg-teal-500 transition shadow-md">
      Se connecter
    </button>

    <p v-if="error" class="text-[var(--color-secondary)] text-sm text-center mt-2">
      {{ error }}
    </p>

    <p v-if="success" class="text-[var(--color-primary)] text-sm text-center mt-2">
      Connexion réussie !
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

const email = ref('')
const password = ref('')
const error = ref('')
const success = ref(false)

const router = useRouter()
const auth = useAuthStore()

const login = async () => {
  error.value = ''
  success.value = false
  try {
    const response = await axios.post('http://localhost:8000/login', {
      email: email.value,
      password: password.value,
    })

    auth.setToken(response.data.token)

    success.value = true
    email.value = ''
    password.value = ''

    router.push('/')

  } catch (e) {
    error.value = e.response?.data?.message || 'Identifiants invalides'
  }
}
</script>
