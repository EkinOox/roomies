<template>
  <div class="p-8 text-white">
    <h2 class="text-3xl font-bold mb-6">
      🎮 Rooms {{ route.params.slug ? `du jeu ${route.params.slug}` : 'disponibles' }}
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="room in rooms" :key="room.id"
        class="bg-gradient-to-br from-[#1f1f2e] to-[#1a1a2e] p-5 rounded-2xl shadow-[0_0_15px_rgba(0,255,255,0.1)] border border-neonBlue/20 hover:shadow-[0_0_30px_rgba(168,85,247,0.3)] transition-all duration-300 relative">
        <img :src="room.game?.image" :alt="room.game?.name"
          class="w-full h-32 object-cover rounded-lg border border-neonBlue/20 mb-4" />
        <h3 class="text-xl font-bold text-neonPurple mb-1">{{ room.name }}</h3>
        <p class="text-sm text-gray-400 mb-1"><i class="pi pi-user mr-1"></i> Créateur : {{ room.owner?.username }}</p>
        <p class="text-sm text-gray-400 mb-1">
          <i class="pi pi-users mr-1"></i> {{ room.participants?.length || 0 }}/{{ room.maxPlayers }} joueurs
        </p>
        <p class="text-sm text-gray-500 italic mb-2">
          <i class="pi pi-clock mr-1"></i> {{ new Date(room.createdAt).toLocaleDateString('fr-FR') }}
        </p>

        <div class="flex justify-between items-center mt-4">
          <router-link :to="`/rooms/${room.id}`" class="text-neonBlue hover:text-neonPink transition text-sm">
            ➔ Rejoindre la room
          </router-link>

          <button v-if="canDeleteRoom(room)" @click="openDeleteModal(room)"
            class="text-sm text-red-500 hover:text-red-700 transition" title="Supprimer la room">
            <i class="pi pi-trash"></i>
          </button>

        </div>
      </div>

      <!-- Bouton de création -->
      <button @click="modalOpen = true"
        class="flex flex-col justify-center items-center border-2 border-dashed border-neonBlue hover:bg-[#1e293b] transition rounded-2xl p-6 text-neonBlue">
        <i class="pi pi-plus-circle text-3xl mb-2"></i>
        <span>Créer une Room</span>
      </button>

      <div v-if="rooms.length === 0" class="mt-10 text-center text-gray-400 col-span-full">
        😕 Aucune room disponible pour ce jeu.
      </div>
    </div>

    <!-- Modal de création -->
    <div v-if="modalOpen" class="fixed inset-0 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="bg-[#0f172a] p-6 rounded-xl w-full max-w-md space-y-4 border border-neonBlue/30 shadow-lg">
        <h3 class="text-xl font-bold mb-2"><i class="pi pi-plus-circle"></i> Nouvelle Room</h3>

        <input v-model="newRoom.name" placeholder="Nom de la room"
          class="w-full p-2 rounded bg-[#1e293b] text-white outline-none focus:ring-2 focus:ring-neonBlue" />

        <div class="grid grid-cols-2 gap-4">
          <div v-for="game in games" :key="game.id" @click="selectGame(game.name)" :class="[
            'relative cursor-pointer border p-3 rounded-xl transition group',
            selectedGameSlug === game.name
              ? 'border-neonBlue bg-[#1e3a5f] shadow-[0_0_12px_rgba(0,255,255,0.6)]'
              : 'border-gray-600 hover:border-neonBlue'
          ]">
            <div v-if="selectedGameSlug === game.slug" class="absolute top-2 right-2 text-neonBlue text-xl">
              ✅
            </div>
            <img :src="game.image" :alt="game.name" class="w-full h-24 object-cover rounded mb-2" />
            <p class="text-white text-center font-semibold">{{ game.name }}</p>
          </div>
        </div>

        <input v-model.number="newRoom.maxPlayers" type="number" min="1" max="10" placeholder="Nombre max de joueurs"
          class="w-full p-2 rounded bg-[#1e293b] text-white outline-none focus:ring-2 focus:ring-neonBlue" />

        <div class="flex justify-end gap-2">
          <button @click="modalOpen = false" class="px-4 py-2 text-sm bg-gray-600 rounded hover:bg-gray-700 transition">
            Annuler
          </button>
          <button @click="createRoom" :disabled="!newRoom.name || !newRoom.game || !newRoom.maxPlayers"
            class="px-4 py-2 text-sm text-white rounded transition" :class="[
              (!newRoom.name || !newRoom.game || !newRoom.maxPlayers)
                ? 'bg-gray-500 cursor-not-allowed'
                : 'bg-neonPink hover:bg-pink-600'
            ]">
            Créer
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de suppression -->
    <div v-if="showDeleteModal" class="fixed inset-0 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="bg-[#1e293b] p-6 rounded-xl border border-red-500 shadow-lg w-full max-w-md">
        <h3 class="text-xl font-bold text-red-400 mb-4">
          <i class="pi pi-exclamation-circle"></i> Supprimer cette room ?
        </h3>
        <p class="text-gray-300 mb-6">
          Es-tu sûr de vouloir supprimer la room <strong class="text-red-300">"{{ roomToDelete?.name }}"</strong> ?
          Cette action est irréversible.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false"
            class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-700 text-white transition">
            Annuler
          </button>
          <button @click="confirmDeleteRoom"
            class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white transition shadow-[0_0_10px_rgba(255,0,0,0.4)]">
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>

  <Toast position="top-right" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { io } from 'socket.io-client'
import { useAuthStore } from '@/stores/useAuthStore'
import { useRoute } from 'vue-router'
import { watch } from 'vue'
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast'

const auth = useAuthStore()
const socket = io('http://localhost:3000')
const route = useRoute()

const rooms = ref([])
const games = ref([])
const selectedGameSlug = ref('')
const modalOpen = ref(false)
const showDeleteModal = ref(false)
const roomToDelete = ref(null)
const toast = useToast()

const newRoom = ref({
  name: '',
  game: '',
  maxPlayers: 2,
})

function selectGame(name) {
  if (selectedGameSlug.value === name) {
    selectedGameSlug.value = ''
    newRoom.value.game = ''
  } else {
    const selected = games.value.find(g => g.name === name)
    if (selected) {
      selectedGameSlug.value = name
      newRoom.value.game = selected.name
    }
  }
}

const updateRoom = async (roomId) => {
  const res = await axios.get(`http://localhost:8000/api/rooms/${roomId}`, {
    headers: { Authorization: `Bearer ${auth.token}` }
  })
  const index = rooms.value.findIndex(r => r.id === roomId)
  if (index !== -1) {
    rooms.value[index] = res.data
  }
}

socket.on("room:update", ({ roomId, participantsCount }) => {
  const index = rooms.value.findIndex(r => r.id === roomId)
  if (index !== -1) {
    updateRoom(roomId)
  }
})



async function fetchRooms() {
  const slug = route.params.slug

  const res = await axios.get('http://localhost:8000/api/rooms', {
    headers: { Authorization: `Bearer ${auth.token}` },
  })

  const allRooms = res.data
  rooms.value = slug
    ? allRooms.filter(r => r.game?.name?.toLowerCase() === slug.toLowerCase())
    : allRooms
}


async function fetchGames() {
  const res = await axios.get('http://localhost:8000/api/games', {
    headers: { Authorization: `Bearer ${auth.token}` }
  })
  games.value = res.data
}

onMounted(() => {
  fetchRooms()
  fetchGames()
})

async function createRoom() {
  if (!newRoom.value.name || !newRoom.value.game || !newRoom.value.maxPlayers) {
    toast.add({
      severity: 'warn',
      summary: 'Attention',
      detail: 'Merci de remplir tous les champs.'
    })
    return
  }

  try {
    await axios.post('http://localhost:8000/api/rooms', newRoom.value, {
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
    })

    newRoom.value = { name: '', game: '', maxPlayers: 1 }
    selectedGameSlug.value = ''
    modalOpen.value = false
    await fetchRooms()
  } catch (err) {
    console.error('Erreur création :', err.response?.data)
    const msg = err.response?.data?.error
      ?? err.response?.data?.violations?.[0]?.message
      ?? "Ce nom de room est déjà pris"
    toast.add({
      severity: 'info',
      summary: 'Info',
      detail: msg
    })
  }
}

function openDeleteModal(room) {
  roomToDelete.value = room
  showDeleteModal.value = true
}

async function confirmDeleteRoom() {
  if (!roomToDelete.value) return

  try {
    await axios.delete(`http://localhost:8000/api/rooms/${roomToDelete.value.id}`, {
      headers: { Authorization: `Bearer ${auth.token}` },
    })
    await fetchRooms()
    showDeleteModal.value = false
    roomToDelete.value = null
  } catch (err) {
    console.error('Erreur suppression room:', err)
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: "Impossible de supprimer cette room.",
    })
  }
}

watch(() => auth.userId, (newVal) => {
  if (newVal) {
    fetchRooms()
  }
})

watch(() => route.params.slug, () => {
  fetchRooms()
})

function canDeleteRoom(room) {
  return room?.owner?.id === auth.userId
}

</script>

<style scoped>
option[disabled] {
  color: #999;
}

button[title="Supprimer la room"] {
  transition: transform 0.2s ease;
}

button[title="Supprimer la room"]:hover {
  transform: scale(1.2);
}
</style>
