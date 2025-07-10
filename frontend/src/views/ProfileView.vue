<template>
  <div v-if="profile" class="min-h-screen p-6 text-text-light max-w-4xl mx-auto">
    <!-- Bienvenue & Avatar -->
    <section
      class="flex items-center gap-6 mb-10 p-6 rounded-3xl border border-neonPurple/50 bg-[#1c1c2b] shadow-[0_0_20px_rgba(168,85,247,0.3)] animate-pulse-slow">
      <div class="relative">
        <img :src="avatarPreview || profile.avatar" alt="Avatar"
          class="w-24 h-24 rounded-full object-cover border-4 border-neonBlue shadow-[0_0_15px_rgba(0,255,255,0.5)]" />
        <button v-if="isEditing" @click="openAvatarModal"
          class="absolute bottom-0 right-0 bg-neonPink text-white rounded-full p-1 shadow-[0_0_8px_rgba(255,0,170,0.6)]">
          🎨
        </button>
      </div>
      <div>
        <h1 class="text-4xl font-extrabold text-neonBlue drop-shadow-[0_0_8px_rgba(0,255,255,0.6)]">
          Salut {{ profile.username }},
        </h1>
        <p class="text-gray-300 text-lg">Bienvenue dans ton espace rétro ✨</p>
      </div>
    </section>

    <!-- Modal avatar -->
    <div v-if="showAvatarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
      <div class="bg-[#1f1f2e] p-6 rounded-2xl shadow-[0_0_30px_rgba(255,0,255,0.3)] max-w-2xl w-full relative">
        <h2 class="text-xl font-bold mb-4 text-neonPurple">Choisis ton avatar</h2>
        <div class="grid grid-cols-4 gap-4">
          <img v-for="img in avatarOptions" :key="img" :src="`/img/avatar/${img}`" :alt="img"
            class="w-20 h-20 rounded-full cursor-pointer border-4 transition duration-300" :class="{
              'border-neonBlue shadow-[0_0_10px_rgba(0,255,255,0.5)]': selectedAvatar === `/img/avatar/${img}`,
              'border-transparent': selectedAvatar !== `/img/avatar/${img}`
            }" @click="selectAvatar(`/img/avatar/${img}`)" />
        </div>
        <div class="mt-6 flex justify-end gap-4">
          <button @click="closeAvatarModal" class="px-4 py-2 bg-gray-500 text-white rounded-xl">Annuler</button>
          <button @click="confirmAvatar"
            class="px-4 py-2 bg-neonPink text-white rounded-xl shadow-[0_0_12px_rgba(255,0,170,0.6)]">Valider</button>
        </div>
      </div>
    </div>

    <!-- Modal Ajout Jeu Favori -->
    <FavoriteModal v-if="showAddGameModal" :games="allGames" @add="addFavoriteFromList"
      @close="showAddGameModal = false" />

    <!-- Infos Perso -->
    <section class="bg-[#1a1a2e] p-8 rounded-3xl mb-10 border border-neonPink/30 shadow-[0_0_25px_rgba(255,0,170,0.3)]">
      <h2
        class="text-3xl font-bold mb-6 text-neonPurple drop-shadow-[0_0_10px_rgba(168,85,247,0.6)] border-b border-neonPurple/30 pb-2">
        🔝 Mes Informations
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-base text-gray-200">
        <div>
          <label class="block font-semibold mb-2">Nom d'utilisateur</label>
          <div v-if="!isEditing" class="bg-[#2d2d44] p-3 rounded-md shadow-inner border border-neonBlue/10 select-none">
            {{ profile.username }}
          </div>
          <input v-else v-model="form.username" placeholder="Nom d'utilisateur"
            class="w-full p-3 rounded-md border border-neonBlue bg-[#1c1c2b] text-white focus:ring-2 focus:ring-neonPurple" />
        </div>

        <div>
          <label class="block font-semibold mb-2">Email</label>
          <div v-if="!isEditing" class="bg-[#2d2d44] p-3 rounded-md shadow-inner border border-neonBlue/10 select-none">
            {{ profile.email }}
          </div>
          <input v-else type="email" v-model="form.email"
            class="w-full p-3 rounded-md border border-neonBlue bg-[#1c1c2b] text-white focus:ring-2 focus:ring-neonPurple" />
        </div>

        <div v-if="isEditing">
          <label class="block font-semibold mb-2">Nouveau mot de passe</label>
          <input type="password" v-model="form.password" placeholder="Laisse vide si inchangé"
            class="w-full p-3 rounded-md border border-neonBlue bg-[#1c1c2b] text-white" />
        </div>

        <div v-if="isEditing">
          <label class="block font-semibold mb-2">Confirmer</label>
          <input type="password" v-model="confirmPassword"
            class="w-full p-3 rounded-md border border-neonBlue bg-[#1c1c2b] text-white" />
        </div>
      </div>

      <div class="mt-8 flex gap-4">
        <button v-if="!isEditing" @click="enableEdit"
          class="px-6 py-3 bg-neonPink text-white rounded-xl hover:shadow-pinkGlow transition duration-200">
          Modifier
        </button>
        <div v-else class="flex gap-4">
          <button @click="submitChanges"
            class="px-6 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition">Enregistrer</button>
          <button @click="cancelEdit"
            class="px-6 py-3 bg-gray-400 text-white rounded-xl hover:bg-gray-500 transition">Annuler</button>
        </div>
      </div>
    </section>

    <!-- Favoris -->
    <section
      class="border border-neonBlue/40 rounded-3xl p-8 bg-gradient-to-br from-[#1c1c2b] to-[#111120] shadow-[0_0_30px_rgba(0,255,255,0.15)]">
      <h2
        class="text-3xl font-bold mb-6 text-neonPurple drop-shadow-[0_0_10px_rgba(168,85,247,0.6)] border-b border-neonPurple/30 pb-2">
        🎮 Mes Jeux Favoris
      </h2>
      <div v-if="favorites.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="game in favorites" :key="game.id"
          class="relative bg-gradient-to-br from-[#222232] to-[#1b1b2a] text-white rounded-2xl p-5 border border-neonBlue/20 shadow-[0_0_25px_rgba(0,255,255,0.2)] hover:shadow-[0_0_30px_rgba(255,0,255,0.3)] transition duration-300">
          <button @click="removeFavorite(game.id)"
            class="absolute top-3 right-3 bg-gray-300 hover:bg-red-200 text-white p-1 rounded-full shadow-lg transition-transform hover:scale-110"
            title="Retirer des favoris">
            ❌
          </button>
          <img :src="game.image" alt="Image du jeu"
            class="w-full h-44 object-cover rounded-lg border-2 border-neonBlue/40 shadow-inner mb-4" />
          <h3 class="text-lg font-bold text-neonBlue mb-1 tracking-wide drop-shadow-[0_0_5px_rgba(0,255,255,0.5)]">
            {{ game.name }}
          </h3>
          <p class="text-sm text-gray-300 leading-relaxed">{{ game.description }}</p>
        </div>

        <div @click="showAddGameModal = true"
          class="flex flex-col justify-center items-center border border-dashed border-neonBlue hover:bg-[#1e293b] transition rounded-xl p-6 text-neonBlue">
          <div
            class="text-5xl mb-2 text-neonBlue group-hover:text-neonPink transition duration-200 drop-shadow-[0_0_6px_rgba(0,255,255,0.5)]">
            <i class="pi pi-plus-circle text-5xl mb-2"></i>
          </div>
          <p class="text-center text-sm text-gray-300 group-hover:text-white">
            Ajouter un nouveau jeu favoris
          </p>
        </div>
      </div>
      <div v-else class="text-center">
        <p class="text-gray-400 italic mt-6 mb-6">Tu n’as pas encore de jeux favoris.</p>
        <button @click="showAddGameModal = true"
          class="flex flex-col justify-center items-center border border-dashed border-neonBlue hover:bg-[#1e293b] transition rounded-xl p-6 text-neonBlue">
          <i class="pi pi-plus-circle text-3xl mb-2"></i>
          <span>Ajouter un jeu</span>
        </button>
      </div>
    </section>
  </div>

  <Toast position="top-right" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'
import FavoriteModal from '@/components/FavoriteModal.vue'
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast'

const auth = useAuthStore()

const profile = ref(null)
const isEditing = ref(false)
const form = ref({ username: '', email: '', password: '' })
const confirmPassword = ref('')
const avatarPreview = ref(null)
const showAvatarModal = ref(false)
const selectedAvatar = ref(null)
const avatarOptions = Array.from({ length: 9 }, (_, i) => `${i + 1}.png`)
const showAddGameModal = ref(false)
const allGames = ref([])
const favorites = ref([])
const toast = useToast()

const fetchProfile = async () => {
  const { data } = await axios.get('http://localhost:8000/api/users/profile', {
    headers: { Authorization: `Bearer ${auth.token}` }
  })
  profile.value = data
  auth.setUser(data)
}

onMounted(async () => {
  try {
    await fetchProfile()
    const { data: gamesData } = await axios.get('http://localhost:8000/api/games', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    allGames.value = gamesData

    const { data: favData } = await axios.get('http://localhost:8000/api/users/favorites', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    favorites.value = favData
  } catch (err) {
    console.error('Erreur lors du chargement :', err)
  }
})

const enableEdit = () => {
  form.value = {
    username: profile.value.username,
    email: profile.value.email,
    password: ''
  }
  confirmPassword.value = ''
  avatarPreview.value = profile.value.avatar
  selectedAvatar.value = profile.value.avatar
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  avatarPreview.value = null
  selectedAvatar.value = null
}

const submitChanges = async () => {
  if (form.value.password && form.value.password !== confirmPassword.value) {
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: "Les mots de passe ne correspondent pas.",
    })
    return
  }
  const payload = {
    username: form.value.username,
    email: form.value.email,
    avatar: avatarPreview.value
  }
  if (form.value.password) payload.password = form.value.password

  try {
    const { data } = await axios.post('http://localhost:8000/api/users/update', payload, {
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })
    if (data.token) auth.setToken(data.token)
    profile.value = data.user
    isEditing.value = false
    avatarPreview.value = null
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Profil mis à jour avec succès !'
    })
  } catch (err) {
    console.error('Erreur mise à jour profil :', err)
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: "Échec de la mise à jour du profil.",
    })
  }
}

const openAvatarModal = () => {
  selectedAvatar.value = avatarPreview.value || profile.value.avatar
  showAvatarModal.value = true
}
const closeAvatarModal = () => showAvatarModal.value = false
const selectAvatar = (avatar) => selectedAvatar.value = avatar
const confirmAvatar = () => {
  avatarPreview.value = selectedAvatar.value
  closeAvatarModal()
}

const addFavoriteFromList = async (gameId) => {
  try {
    await axios.post(`http://localhost:8000/api/users/favorites/${gameId}`, {}, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    const { data: favData } = await axios.get('http://localhost:8000/api/users/favorites', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    favorites.value = favData
    showAddGameModal.value = false
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Jeu ajouté aux favoris.'
    })
  } catch (err) {
    console.error('Erreur ajout favori :', err)
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: "Impossible d’ajouter ce jeu aux favoris.",
    })
  }
}

const removeFavorite = async (gameId) => {
  try {
    await axios.delete(`http://localhost:8000/api/users/favorites/${gameId}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    const { data: favData } = await axios.get('http://localhost:8000/api/users/favorites', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    favorites.value = favData
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Jeu retiré des favoris.'
      })
  } catch (err) {
    console.error("Erreur retrait favori :", err)
     toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: "Impossible de retirer ce jeu des favoris.",
    })
  }
}
</script>
