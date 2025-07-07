<template>
  <div class="flex flex-col items-center justify-center w-full h-full">
    <!-- Timer -->
    <div v-if="startTime" class="mb-4 text-neonBlue text-xl font-semibold">
      ⏱️ Temps écoulé : {{ timer }}s
    </div>

    <!-- Infos joueur -->
    <div v-if="mySymbol" class="mb-2 text-white">
      Vous êtes le joueur :
      <span :class="mySymbol === 'X' ? 'text-neonPink' : 'text-neonBlue'">
        {{ mySymbol }}
      </span>
    </div>

    <!-- À qui le tour -->
    <div v-if="!winner" class="mb-4 text-sm text-gray-400">
      C’est au tour de :
      <span :class="currentPlayer === 'X' ? 'text-neonPink' : 'text-neonBlue'">
        {{ currentPlayer }}
      </span>
    </div>

    <p class="text-xs text-gray-400 mt-2">
      Joueur
      X=<span :class="{ 'font-bold text-white': players.X === userId }">{{ playersInfo.X || '—' }}</span> contre joueur
      O=<span :class="{ 'font-bold text-white': players.O === userId }">{{ playersInfo.O || '—' }}</span>
    </p><br />

    <!-- Bouton Start -->
    <button v-if="!startTime && !isSpectator && isReadyToStart" @click="startGame"
      class="mb-4 px-4 py-2 rounded-lg bg-neonPink hover:bg-pink-600 transition text-white shadow">
      🚀 Lancer la partie
    </button>


    <p v-if="!isReadyToStart" class="text-sm text-yellow-400 mt-2">
      ⏳ En attente d’un autre joueur pour démarrer...
    </p><br />

    <!-- Grille du morpion -->
    <div class="grid grid-cols-3 gap-4">
      <button v-for="(cell, index) in board" :key="index" @click="handleClick(index)" class="w-24 h-24 text-3xl font-bold rounded-xl flex items-center justify-center
               transition-all duration-200 border border-neonBlue bg-[#1e293b]
               shadow-[4px_4px_10px_#000000,-4px_-4px_10px_#ffffff20]
               hover:shadow-[inset_4px_4px_8px_#000000,inset_-4px_-4px_8px_#ffffff10]"
        :disabled="isSpectator || !isMyTurn || cell || winner">
        <span :class="{
          'text-neonPink': cell === 'X',
          'text-neonBlue': cell === 'O'
        }">
          {{ cell }}
        </span>
      </button>
    </div>

    <!-- Résultat -->
    <p v-if="winner" class="mt-6 text-xl font-bold text-green-400">
      {{ winner === 'draw' ? 'Égalité !' : `🏆 Le gagnant est ${winner}` }}
    </p>

    <!-- Bouton Rejouer -->
    <button v-if="winner && !isSpectator && isReadyToStart" @click="startGame"
      class="mt-4 px-4 py-2 rounded-lg bg-neonPink hover:bg-pink-600 transition text-white shadow">
      🔄 Rejouer
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { socket } from '@/plugins/socket'

const props = defineProps({
  roomId: String,
  userId: String,
  username: String
})

// État local
const board = ref(Array(9).fill(''))
const currentPlayer = ref('X')
const players = ref({ X: '', O: '' })
const playersInfo = ref({ X: '', O: '' })
const winner = ref(null)
const startTime = ref(null)
const timer = ref(0)
const interval = ref(null)
const mySymbol = ref(null)

const isSpectator = computed(() => !mySymbol.value)
const isMyTurn = computed(() => mySymbol.value === currentPlayer.value)
const isReadyToStart = computed(() =>
  players.value.X && players.value.O
)

function startGame() {
  socket.emit('morpion:start', { roomId: props.roomId }, (response) => {
    if (response?.error) {
      console.warn('Impossible de démarrer :', response.message)
      alert('Erreur : ' + response.message)
    } else {
      console.log('🎮 Partie lancée avec succès !')
    }
  })
}


function handleClick(index) {
  if (isSpectator.value || !isMyTurn.value || board.value[index] || winner.value) return

  socket.emit('morpion:move', {
    roomId: props.roomId,
    index,
    userId: props.userId
  })
}

// Met à jour l'état quand on reçoit une mise à jour du serveur
socket.on('morpion:state', (state) => {
  board.value = state.board
  currentPlayer.value = state.currentPlayer
  players.value = state.players
  playersInfo.value = state.playersInfo || { X: '', O: '' }
  winner.value = state.winner
  startTime.value = state.startTime

  // Timer
  if (startTime.value && !interval.value) {
    interval.value = setInterval(() => {
      timer.value = Math.floor((Date.now() - startTime.value) / 1000)
    }, 1000)
  }

  // Arrêt du timer en cas de fin
  if (state.winner && interval.value) {
    clearInterval(interval.value)
    interval.value = null
    timer.value = 0
  }
})

onMounted(() => {
  socket.emit('room:join', {
    roomId: props.roomId,
    userId: props.userId,
    username: props.username,
    maxPlayers: 2
  })

  socket.on("morpion:role", ({ role }) => {
    console.log("🎭 Reçu rôle depuis serveur :", role)
    mySymbol.value = role
  })

  timer.value = 0
})



</script>


<style scoped>
.text-neonBlue {
  color: #38bdf8;
}

.text-neonPink {
  color: #ec4899;
}

.bg-neonPink {
  background-color: #ec4899;
}

.border-neonBlue {
  border-color: #38bdf8;
}
</style>
