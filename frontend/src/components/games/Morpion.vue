<template>
  <div class="flex flex-col items-center justify-center w-full h-full">
    <!-- Affiche le timer si la partie a commencé -->
    <div v-if="startTime" class="mb-4 text-neonBlue text-xl font-semibold">
      ⏱️ Temps écoulé : {{ timer }}s
    </div>

    <!-- Affiche le symbole du joueur connecté -->
    <div v-if="mySymbol" class="mb-2 text-white">
      Vous êtes le joueur :
      <span :class="mySymbol === 'X' ? 'text-neonPink' : 'text-neonBlue'">
        {{ mySymbol }}
      </span>
    </div>

    <!-- Indique à qui c'est le tour tant qu'il n'y a pas de gagnant -->
    <div v-if="!winner" class="mb-4 text-sm text-gray-400">
      C’est au tour de :
      <span :class="currentPlayer === 'X' ? 'text-neonPink' : 'text-neonBlue'">
        {{ currentPlayer }}
      </span>
    </div>

    <!-- Affiche les joueurs X et O avec mise en forme si c'est l'utilisateur -->
    <p class="text-xs text-gray-400 mt-2">
      Joueur
      X=<span :class="{ 'font-bold text-white': players.X === userId }">{{ playersInfo.X || '—' }}</span> contre joueur
      O=<span :class="{ 'font-bold text-white': players.O === userId }">{{ playersInfo.O || '—' }}</span>
    </p><br />

    <!-- Bouton pour démarrer la partie, visible uniquement si la partie n'a pas commencé, que l'utilisateur n'est pas spectateur et que les joueurs sont prêts -->
    <button v-if="!startTime && !isSpectator && isReadyToStart" @click="startGame"
      class="mb-4 px-4 py-2 rounded-lg bg-neonPink hover:bg-pink-600 transition text-white shadow">
      🚀 Lancer la partie
    </button>

    <!-- Message d'attente si les joueurs ne sont pas prêts -->
    <p v-if="!isReadyToStart" class="text-sm text-yellow-400 mt-2">
      ⏳ En attente d’un autre joueur pour démarrer...
    </p><br />

    <!-- Grille du morpion : 9 cases en 3x3, chaque case est un bouton -->
    <div class="grid grid-cols-3 gap-4">
      <button v-for="(cell, index) in board" :key="index" @click="handleClick(index)" class="w-24 h-24 text-3xl font-bold rounded-xl flex items-center justify-center
               transition-all duration-200 border border-neonBlue bg-[#1e293b]
               shadow-[4px_4px_10px_#000000,-4px_-4px_10px_#ffffff20]
               hover:shadow-[inset_4px_4px_8px_#000000,inset_-4px_-4px_8px_#ffffff10]"
        :disabled="isSpectator || !isMyTurn || cell || winner">
        <!-- Affiche X ou O avec une couleur différente selon le symbole -->
        <span :class="{
          'text-neonPink': cell === 'X',
          'text-neonBlue': cell === 'O'
        }">
          {{ cell }}
        </span>
      </button>
    </div>

    <!-- Affiche le résultat si la partie est terminée -->
    <p v-if="winner" class="mt-6 text-xl font-bold text-green-400">
      {{ winner === 'draw' ? 'Égalité !' : `🏆 Le gagnant est ${winner}` }}
    </p>

    <!-- Bouton pour rejouer, visible uniquement si la partie est finie, que l'utilisateur n'est pas spectateur et que les joueurs sont prêts -->
    <button v-if="winner && !isSpectator && isReadyToStart" @click="startGame"
      class="mt-4 px-4 py-2 rounded-lg bg-neonPink hover:bg-pink-600 transition text-white shadow">
      🔄 Rejouer
    </button>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import { socket } from '@/plugins/socket'

// Props reçus : id de la room, id utilisateur, nom utilisateur
const props = defineProps({
  roomId: String,
  userId: String,
  username: String
})

// États réactifs locaux
const board = ref(Array(9).fill(''))            // Plateau 3x3 vide initialement
const currentPlayer = ref('X')                   // Joueur courant, par défaut 'X'
const players = ref({ X: '', O: '' })            // IDs des joueurs X et O
const playersInfo = ref({ X: '', O: '' })        // Infos supplémentaires joueurs
const winner = ref(null)                          // Gagnant ('X', 'O', 'draw' ou null)
const startTime = ref(null)                       // Timestamp du début de la partie
const timer = ref(0)                              // Timer en secondes
const interval = ref(null)                        // Interval pour mise à jour timer
const mySymbol = ref(null)                        // Symbole du joueur connecté ('X', 'O' ou null)

// Computed properties pratiques
const isSpectator = computed(() => !mySymbol.value)               // Est-ce un spectateur (pas assigné à X ou O)
const isMyTurn = computed(() => mySymbol.value === currentPlayer.value)  // Est-ce à mon tour
const isReadyToStart = computed(() => players.value.X && players.value.O) // Les deux joueurs sont connectés

// Fonction pour démarrer la partie : envoie un event via socket
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

// Fonction déclenchée au clic sur une case du plateau
function handleClick(index) {
  // Ignore si spectateur, pas son tour, case occupée ou partie terminée
  if (isSpectator.value || !isMyTurn.value || board.value[index] || winner.value) return

  // Envoie le coup au serveur via socket
  socket.emit('morpion:move', {
    roomId: props.roomId,
    index,
    userId: props.userId
  })
}

// Réception des mises à jour du serveur pour synchroniser l'état local
socket.on('morpion:state', (state) => {
  board.value = state.board
  currentPlayer.value = state.currentPlayer
  players.value = state.players
  playersInfo.value = state.playersInfo || { X: '', O: '' }
  winner.value = state.winner
  startTime.value = state.startTime

  // Démarre le timer si la partie a commencé et que ce n'est pas déjà fait
  if (startTime.value && !interval.value) {
    interval.value = setInterval(() => {
      timer.value = Math.floor((Date.now() - startTime.value) / 1000)
    }, 1000)
  }

  // Stoppe le timer si la partie est terminée
  if (state.winner && interval.value) {
    clearInterval(interval.value)
    interval.value = null
    timer.value = 0
  }
})

// Lors du montage du composant, rejoins la room et récupère le rôle (X ou O)
onMounted(() => {
  socket.emit('room:join', {
    roomId: props.roomId,
    userId: props.userId,
    username: props.username,
    maxPlayers: 2
  })

  // Récupère le rôle assigné par le serveur
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
