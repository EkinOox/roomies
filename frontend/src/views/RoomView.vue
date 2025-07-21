<template>
  <div class="grid grid-cols-1 md:grid-cols-4 h-screen text-white gap-4 p-4 bg-[#0f172a]">
    <!-- Partie jeu -->
    <div class="md:col-span-3 bg-[#1e293b] p-4 rounded-xl overflow-auto">
      <h2 class="text-2xl font-bold mb-2">{{ room.name }}</h2>
      <p>Jeu : {{ room.gameType }}</p>
      <p>Capacité: {{ room.maxPlayers }} joueurs</p>
      <p>Participants : {{ room.participants?.length || 0 }}/{{ room.maxPlayers }}</p>
      <div v-if="isSpectator" class="mt-4 text-yellow-400">Spectateur uniquement - room pleine</div>

      <div class="mt-6 bg-gray-800 min-h-[500px] flex items-center justify-center rounded-lg">
        <component :is="currentGameComponent" :room-id="String(room.id)" :user-id="String(auth.userId)" :username="String(auth.username)" :key="room.id" />
      </div>
    </div>

    <!-- Chat -->
    <div class="md:col-span-1">
      <GameChat :roomId="room.id" :canSend="!isSpectator" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, defineAsyncComponent } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import GameChat from '@/components/GameChat.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import { socket } from '@/plugins/socket'

const auth = useAuthStore()
const route = useRoute()

const room = ref({})
const isSpectator = ref(false)

// Lazy load les composants de jeux
const gameComponents = {
  '2048': defineAsyncComponent(() => import('@/components/games/2048.vue')),
  'morpion': defineAsyncComponent(() => import('@/components/games/Morpion.vue')),
  'echecs': defineAsyncComponent(() => import('@/components/games/Echecs.vue')),
}

const currentGameComponent = computed(() => {
  const key = room.value?.gameType?.toLowerCase()
  return gameComponents[key] || null
})

onMounted(async () => {
  try {
    const res = await axios.post(`http://localhost:8000/api/rooms/${route.params.id}/join`, {}, {
      headers: { Authorization: `Bearer ${auth.token}` },
    })

    room.value = res.data.room
    isSpectator.value = res.data.spectator

    socket.emit('room:join', {
      roomId: room.value.id,
      userId: auth.userId,
      maxPlayers: room.value.maxPlayers,
      username: auth.username
    })
  } catch (err) {
    console.error('Erreur de join :', err)
  }
})

socket.on('room:status', ({ spectator }) => {
  isSpectator.value = spectator
})

socket.on('room:update', ({ roomId, participantsCount }) => {
  if (room.value.id === roomId) {
    room.value.participants = Array(participantsCount).fill({})
    isSpectator.value = participantsCount > room.value.maxPlayers
  }
})

// ?? écouter les changements de statut
socket.on('room:status-changed', ({ roomId, status, message, winner }) => {
  if (room.value.id === roomId) {
    room.value.status = status
    console.log(`?? Statut room ${roomId} changé: ${status}`)

    // Optionnel: afficher un message é l'utilisateur
    if (message) {
      console.log(`?? ${message}`)
      // Ici on pourrait ajouter une notification toast
    }
  }
})

onBeforeUnmount(async () => {
  // Socket : info aux autres clients
  socket.emit('leave-room', {
    roomId: room.value.id,
    userId: auth.userId
  })

  // Requête HTTP : mise à jour de la base
  try {
    await axios.post(`http://localhost:8000/api/rooms/${room.value.id}/leave`, {}, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    console.log('✅ Base de données mise à jour')
  } catch (err) {
    console.error('❌ Erreur API leave:', err)
  }
})
</script>
