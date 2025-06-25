<template>
  <div class="grid grid-cols-1 md:grid-cols-4 h-screen text-white gap-4 p-4 bg-[#0f172a]">
    <!-- Partie jeu -->
    <div class="md:col-span-3 bg-[#1e293b] p-4 rounded-xl overflow-auto">
      <h2 class="text-2xl font-bold mb-2">{{ room.name }}</h2>
      <p>Jeu : {{ room.gameType }}</p>
      <p>Capacité: {{ room.maxPlayers }} joueurs</p>
      <p>Participants : {{ room.participants?.length || 0 }}/{{ room.maxPlayers }}</p>
      <div v-if="isSpectator" class="mt-4 text-yellow-400">Spectateur uniquement - room pleine</div>

      <div class="mt-6 bg-gray-800 h-[500px] flex items-center justify-center rounded-lg">
        <p v-if="isSpectator">🔒 Vue spectateur du jeu</p>
        <p v-else>🎮 Jeu interactif ici</p>
      </div>
    </div>

    <!-- Chat -->
    <div class="md:col-span-1">
      <GameChat :roomId="room.id" :canSend="!isSpectator" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import GameChat from '@/components/GameChat.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import { socket } from '@/plugins/socket'

const auth = useAuthStore()
const route = useRoute()

const room = ref({})
const isSpectator = ref(false)

onMounted(async () => {
  try {
    const res = await axios.post(`http://localhost:8000/api/rooms/${route.params.id}/join`, {}, {
      headers: { Authorization: `Bearer ${auth.token}` },
    })

    room.value = res.data.room
    isSpectator.value = res.data.spectator

    console.log('Room data:', room.value);

    socket.emit('room:join', {
      roomId: room.value.id,
      userId: auth.userId,
      maxPlayers: room.value.maxPlayers,
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

onBeforeUnmount(() => {
  socket.emit('leave-room', {
    roomId: room.value.id,
    userId: auth.userId
  })
})
</script>
