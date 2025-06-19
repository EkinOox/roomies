<template>
  <form
    @submit.prevent="login"
    class="space-y-6 max-w-md mx-auto p-8 rounded-2xl bg-[#111827] neon-box shadow-xl transition"
  >
    <h2 class="text-3xl font-extrabold text-center text-[#00F0FF] drop-shadow-[0_0_5px_#00F0FF]">
      Connexion
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
        v-model="password"
        type="password"
        placeholder="Mot de passe"
        class="neon-input"
        required
      />
    </div>

    <button
      type="submit"
      class="neon-button bg-[#FF4FCB] hover:bg-[#00F0FF]"
    >
      Se connecter
    </button>

    <p v-if="error" class="text-[#FF4FCB] text-sm text-center mt-2">
      {{ error }}
    </p>

    <p v-if="success" class="text-[#00F0FF] text-sm text-center mt-2">
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

    const token = response.data.token
    auth.setToken(token)

    // Ensuite on récupère le profil de l'utilisateur
    const profileResponse = await axios.get('http://localhost:8000/api/users/profile', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    // On met à jour le store avec les données utilisateur (dont l'avatar)
    auth.setUser(profileResponse.data)

    success.value = true
    email.value = ''
    password.value = ''

    // Enfin, on redirige
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Identifiants invalides'
  }
}

</script>
