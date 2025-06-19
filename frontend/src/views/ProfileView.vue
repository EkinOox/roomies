<template>
  <div class="min-h-screen p-6 text-text-light max-w-4xl mx-auto" v-if="user">
    <!-- Bienvenue & avatar -->
    <section class="flex items-center gap-6 mb-8">
      <div class="bg-white rounded-full p-1 shadow-md relative">
        <img :src="previewImage || user.avatar" alt="Avatar" class="w-24 h-24 rounded-full object-cover" />
        <button v-if="isEditing" @click="openAvatarModal"
          class="absolute bottom-0 right-0 bg-neonPink text-white rounded-full p-1" title="Changer d'avatar">
          🎨
        </button>
      </div>

      <div>
        <h1 class="text-4xl font-bold text-neonBlue mb-1">Salut {{ user.username }},</h1>
        <p class="text-white text-lg">Nous sommes heureux de te revoir !</p>
      </div>
    </section>

    <!-- Modal de sélection d’avatar -->
    <div v-if="showAvatarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-20">
      <div class="bg-white p-6 rounded-xl shadow-lg max-w-2xl w-full relative">
        <h2 class="text-xl font-bold mb-4 text-neonPurple">Choisis ton avatar</h2>
        <div class="grid grid-cols-4 gap-4">
          <img v-for="img in avatarList" :key="img" :src="`/img/avatar/${img}`" :alt="img"
            class="w-20 h-20 rounded-full cursor-pointer border-4" :class="{
              'border-neonBlue': selectedAvatar === `/img/avatar/${img}`,
              'border-transparent': selectedAvatar !== `/img/avatar/${img}`,
            }" @click="selectAvatar(`/img/avatar/${img}`)" />
        </div>

        <div class="mt-6 flex justify-end gap-4">
          <button @click="closeAvatarModal" class="px-4 py-2 bg-gray-400 text-white rounded-xl">
            Annuler
          </button>
          <button @click="confirmAvatar" class="px-4 py-2 bg-neonPink text-white rounded-xl">
            Valider
          </button>
        </div>
      </div>
    </div>

    <!-- Infos personnelles -->
    <section class="bg-white neon-box p-8 rounded-3xl mb-8">
      <h2 class="text-2xl font-semibold mb-6 text-neonPurple">Mes Informations</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-color-text text-base">
        <!-- Nom d'utilisateur -->
        <div>
          <label class="block font-semibold mb-2" for="username">Nom d'utilisateur</label>
          <div v-if="!isEditing" class="bg-gray-light p-3 rounded-md select-none">
            {{ user.username }}
          </div>
          <input v-else id="username" v-model="editedUser.username" type="text"
            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-neonPurple" />
        </div>

        <!-- Email -->
        <div>
          <label class="block font-semibold mb-2" for="email">Email</label>
          <div v-if="!isEditing" class="bg-gray-light p-3 rounded-md select-none">
            {{ user.email }}
          </div>
          <input v-else id="email" v-model="editedUser.email" type="email"
            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-neonPurple" />
        </div>

        <!-- Nouveau mot de passe -->
        <div v-if="isEditing">
          <label class="block font-semibold mb-2" for="password">Nouveau mot de passe</label>
          <input id="password" v-model="editedUser.password" type="password"
            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-neonPurple"
            placeholder="Laisse vide si inchangé" />
        </div>

        <!-- Confirmation mot de passe -->
        <div v-if="isEditing">
          <label class="block font-semibold mb-2" for="confirmPassword">Confirmer le mot de passe</label>
          <input id="confirmPassword" v-model="confirmPassword" type="password"
            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-neonPurple" />
        </div>
      </div>

      <!-- Boutons -->
      <div class="mt-8 flex gap-4">
        <button v-if="!isEditing" @click="startEditing"
          class="px-6 py-3 bg-neonPink text-white rounded-xl hover:shadow-pinkGlow transition duration-300">
          Modifier mes informations
        </button>

        <div v-else class="flex gap-4">
          <button @click="saveChanges"
            class="px-6 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition duration-300">
            Enregistrer
          </button>
          <button @click="cancelEditing"
            class="px-6 py-3 bg-gray-400 text-white rounded-xl hover:bg-gray-500 transition duration-300">
            Annuler
          </button>
        </div>
      </div>
    </section>

    <!-- Jeux favoris -->
    <section class="bg-white neon-box p-8 rounded-3xl">
      <h2 class="text-2xl font-semibold mb-6 text-neonPurple">Mes Jeux Favoris</h2>
      <div v-if="user.favoris && user.favoris.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="(jeu, index) in user.favoris" :key="index"
          class="bg-backgroundLight rounded-xl p-5 shadow-md hover:shadow-neon transition">
          <img :src="jeu.image" alt="Jeu" class="w-full h-44 object-cover rounded-md mb-4" />
          <h3 class="text-xl font-semibold text-neonBlue mb-2">{{ jeu.name }}</h3>
          <p class="text-sm text-graySoft">{{ jeu.description }}</p>
        </div>
      </div>
      <p v-else class="text-graySoft text-sm italic">Tu n’as pas encore de jeux favoris.</p>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'

const auth = useAuthStore()
const user = ref(null)
const isEditing = ref(false)
const editedUser = ref({ username: '', email: '', password: '' })
const confirmPassword = ref('')
const avatarFile = ref(null)
const previewImage = ref(null)

// Démarre l'édition du profil
const startEditing = () => {
  editedUser.value = {
    username: user.value.username,
    email: user.value.email,
    password: ''
  }
  confirmPassword.value = ''
  previewImage.value = null
  avatarFile.value = null
  isEditing.value = true
}

// Annule l'édition
const cancelEditing = () => {
  isEditing.value = false
}

// Gère l'upload d'image
const showAvatarModal = ref(false)
const selectedAvatar = ref(null)
const avatarList = [
  '1.png',
  '2.png',
  '3.png',
  '4.png',
  '5.png',
  '6.png',
  '7.png',
  '8.png',
  '9.png'
]

const openAvatarModal = () => {
  selectedAvatar.value = previewImage.value || user.value.avatar
  showAvatarModal.value = true
}

const closeAvatarModal = () => {
  showAvatarModal.value = false
}

const selectAvatar = (avatarPath) => {
  selectedAvatar.value = avatarPath
}

const confirmAvatar = () => {
  previewImage.value = selectedAvatar.value
  avatarFile.value = null // on n'utilise pas de fichier
  showAvatarModal.value = false
}

// Sauvegarde les modifications
const saveChanges = async () => {
  if (editedUser.value.password && editedUser.value.password !== confirmPassword.value) {
    alert("Les mots de passe ne correspondent pas.")
    return
  }

  try {
    const formData = new FormData()
    formData.append('username', editedUser.value.username)
    formData.append('email', editedUser.value.email)
    if (editedUser.value.password) formData.append('password', editedUser.value.password)
    if (avatarFile.value) formData.append('avatar', avatarFile.value)

    const { data } = await axios.post('http://localhost:8000/api/users/update', formData, {
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'multipart/form-data',
      },
    })

    user.value = data
    isEditing.value = false
  } catch (error) {
    console.error('Erreur lors de la mise à jour :', error)
    alert("Échec de la mise à jour du profil.")
  }
}

// Charge l’utilisateur connecté
onMounted(async () => {
  try {
    const { data } = await axios.get('http://localhost:8000/api/users/profile', {
      headers: {
        Authorization: `Bearer ${auth.token}`,
      },
    })
    user.value = data
  } catch (error) {
    console.error("Erreur lors du chargement du profil :", error)
  }
})
</script>
