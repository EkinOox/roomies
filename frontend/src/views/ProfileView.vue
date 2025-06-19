<template>
  <div v-if="profile" class="min-h-screen p-6 text-text-light max-w-4xl mx-auto">

    <!-- Bienvenue & Avatar -->
    <section class="flex items-center gap-6 mb-8">
      <div class="bg-white rounded-full p-1 shadow-md relative">
        <img :src="avatarPreview || profile.avatar" alt="Avatar" class="w-24 h-24 rounded-full object-cover" />
        <button v-if="isEditing" @click="openAvatarModal"
          class="absolute bottom-0 right-0 bg-neonPink text-white rounded-full p-1" title="Changer d'avatar">🎨</button>
      </div>
      <div>
        <h1 class="text-4xl font-bold text-neonBlue mb-1">Salut {{ profile.username }},</h1>
        <p class="text-white text-lg">Nous sommes heureux de te revoir !</p>
      </div>
    </section>

    <!-- Modal avatar -->
    <div v-if="showAvatarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-20">
      <div class="bg-white p-6 rounded-xl shadow-lg max-w-2xl w-full relative">
        <h2 class="text-xl font-bold mb-4 text-neonPurple">Choisis ton avatar</h2>
        <div class="grid grid-cols-4 gap-4">
          <img v-for="img in avatarOptions" :key="img" :src="`/img/avatar/${img}`" :alt="img"
            class="w-20 h-20 rounded-full cursor-pointer border-4" :class="{
              'border-neonBlue': selectedAvatar === `/img/avatar/${img}`,
              'border-transparent': selectedAvatar !== `/img/avatar/${img}`
            }" @click="selectAvatar(`/img/avatar/${img}`)" />
        </div>
        <div class="mt-6 flex justify-end gap-4">
          <button @click="closeAvatarModal" class="px-4 py-2 bg-gray-400 text-white rounded-xl">Annuler</button>
          <button @click="confirmAvatar" class="px-4 py-2 bg-neonPink text-white rounded-xl">Valider</button>
        </div>
      </div>
    </div>

    <!-- Informations personnelles -->
    <section class="bg-white neon-box p-8 rounded-3xl mb-8">
      <h2 class="text-2xl font-semibold mb-6 text-neonPurple">Mes Informations</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-base text-color-text">

        <!-- Nom d'utilisateur -->
        <div>
          <label class="block font-semibold mb-2" for="username">Nom d'utilisateur</label>
          <div v-if="!isEditing" class="bg-gray-light p-3 rounded-md select-none">{{ profile.username }}</div>
          <input v-else id="username" v-model="form.username"
            class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-neonPurple" />
        </div>

        <!-- Email -->
        <div>
          <label class="block font-semibold mb-2" for="email">Email</label>
          <div v-if="!isEditing" class="bg-gray-light p-3 rounded-md select-none">{{ profile.email }}</div>
          <input v-else id="email" type="email" v-model="form.email"
            class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-neonPurple" />
        </div>

        <!-- Mot de passe -->
        <div v-if="isEditing">
          <label class="block font-semibold mb-2" for="password">Nouveau mot de passe</label>
          <input id="password" type="password" v-model="form.password" placeholder="Laisse vide si inchangé"
            class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-neonPurple" />
        </div>

        <!-- Confirmation -->
        <div v-if="isEditing">
          <label class="block font-semibold mb-2" for="confirmPassword">Confirmer le mot de passe</label>
          <input id="confirmPassword" type="password" v-model="confirmPassword"
            class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-neonPurple" />
        </div>
      </div>

      <!-- Boutons -->
      <div class="mt-8 flex gap-4">
        <button v-if="!isEditing" @click="enableEdit"
          class="px-6 py-3 bg-neonPink text-white rounded-xl hover:shadow-pinkGlow transition">Modifier mes
          informations</button>
        <div v-else class="flex gap-4">
          <button @click="submitChanges"
            class="px-6 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition">Enregistrer</button>
          <button @click="cancelEdit"
            class="px-6 py-3 bg-gray-400 text-white rounded-xl hover:bg-gray-500 transition">Annuler</button>
        </div>
      </div>
    </section>

    <!-- Jeux favoris -->
    <section class="bg-white neon-box p-8 rounded-3xl">
      <h2 class="text-2xl font-semibold mb-6 text-neonPurple">Mes Jeux Favoris</h2>
      <div class="mt-4 mb-6">
        <button @click="showAddGameModal = true"
          class="px-4 py-2 bg-neonBlue text-white rounded-xl hover:shadow-blueGlow transition">➕ Ajouter un jeu
          favori</button>
      </div>
      <div v-if="profile.favoris?.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="game in profile.favoris" :key="game.id"
          class="bg-backgroundLight rounded-xl p-5 shadow-md hover:shadow-neon transition relative">
          <button @click="removeFavorite(game.id)"
            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
            title="Retirer des favoris">❌</button>
          <img :src="game.image" alt="Jeu" class="w-full h-44 object-cover rounded-md mb-4" />
          <h3 class="text-xl font-semibold text-neonBlue mb-2">{{ game.name }}</h3>
          <p class="text-sm text-graySoft">{{ game.description }}</p>
        </div>
      </div>
      <p v-else class="text-graySoft text-sm italic">Tu n’as pas encore de jeux favoris.</p>
    </section>

    <!-- Modal d'ajout de jeu favori -->
    <div v-if="showAddGameModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-20">
      <div class="bg-white p-6 rounded-xl shadow-lg max-w-2xl w-full relative">
        <h2 class="text-xl font-bold mb-4 text-neonPurple">Ajouter un jeu favori</h2>
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="block font-semibold mb-1">Nom du jeu</label>
            <input v-model="newGame.name" class="w-full p-2 border border-gray-300 rounded-md"
              placeholder="Nom du jeu" />
          </div>
          <div>
            <label class="block font-semibold mb-1">Description</label>
            <textarea v-model="newGame.description" class="w-full p-2 border border-gray-300 rounded-md"
              placeholder="Brève description"></textarea>
          </div>
          <div>
            <label class="block font-semibold mb-1">Image (URL)</label>
            <input v-model="newGame.image" class="w-full p-2 border border-gray-300 rounded-md"
              placeholder="https://..." />
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-4">
          <button @click="showAddGameModal = false" class="px-4 py-2 bg-gray-400 text-white rounded-xl">Annuler</button>
          <button @click="addFavorite" class="px-4 py-2 bg-neonBlue text-white rounded-xl">Ajouter</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'

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

const newGame = ref({
  name: '',
  description: '',
  image: ''
})


onMounted(async () => {
  try {
    const { data } = await axios.get('http://localhost:8000/api/users/profile', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    profile.value = data
    auth.setUser(data)
  } catch (err) {
    console.error('Erreur chargement profil :', err)
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
    alert('Les mots de passe ne correspondent pas.')
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
  } catch (err) {
    console.error('Erreur mise à jour profil :', err)
    alert("Échec de la mise à jour du profil.")
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

const removeFavorite = async (gameId) => {
  try {
    await axios.delete(`http://localhost:8000/api/users/favorites/${gameId}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    profile.value.favoris = profile.value.favoris.filter((g) => g.id !== gameId)
  } catch (err) {
    console.error("Erreur retrait favori :", err)
    alert("Impossible de retirer ce jeu des favoris.")
  }
}

const addFavorite = async () => {
  if (!newGame.value.name || !newGame.value.image) {
    alert('Le nom et l’image du jeu sont obligatoires.')
    return
  }

  try {
    const { data } = await axios.post('http://localhost:8000/api/users/favorites', newGame.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    profile.value.favoris.push(data)
    newGame.value = { name: '', description: '', image: '' }
    showAddGameModal.value = false
  } catch (err) {
    console.error('Erreur ajout favori :', err)
    alert('Impossible d’ajouter ce jeu aux favoris.')
  }
}
</script>
