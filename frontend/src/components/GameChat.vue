<template>
  <!-- Conteneur principal du chat -->
  <div class="w-full h-full bg-[#0f172a]/90 border border-neonBlue/30 rounded-xl shadow-xl flex flex-col overflow-hidden">

    <!-- En-tête du chat -->
    <div class="bg-neonBlue/20 text-neonBlue px-4 py-3 font-bold flex justify-between items-center">
      <span><i class="pi pi-comments"></i> Chat de la partie</span>
    </div>

    <!-- Zone d'affichage des messages -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
      <div
        v-for="(msg, index) in messages"
        :key="index"
        class="flex gap-2 items-end"
        :class="msg.id === myId ? 'justify-end' : 'justify-start'"
      >
        <!-- Avatar de l'utilisateur -->
        <img
          v-if="msg.avatar"
          :src="msg.avatar"
          alt="avatar"
          class="w-8 h-8 rounded-full border border-white/20"
          :class="msg.id === myId ? 'order-2 ml-2' : 'order-1 mr-2'"
        />

        <!-- Contenu du message -->
        <div
          :class="[
            'max-w-[75%] px-4 py-2 rounded-xl text-sm break-words whitespace-pre-wrap',
            msg.id === myId
              ? 'bg-neonPink text-white shadow-neon'
              : 'bg-[#1e293b] text-white/90 shadow-md'
          ]"
        >
          <!-- Pseudo de l'auteur -->
          <div class="text-xs font-semibold text-neonPurple mb-1">
            {{ msg.id === myId ? auth.username : msg.user }}
          </div>
          <!-- Texte du message -->
          <div>{{ msg.text }}</div>
          <!-- Heure du message -->
          <div class="text-right text-xs text-white/40 mt-1">{{ msg.time }}</div>
        </div>
      </div>

      <!-- Ancre pour scroll automatique -->
      <div ref="bottomRef" />
    </div>

    <!-- Zone de saisie du message -->
    <div class="flex border-t border-white/10 p-2 bg-[#0f172a]/80 backdrop-blur-md">
      <input
        v-model="newMessage"
        @keydown.enter="sendMessage"
        :disabled="!canSend"
        class="flex-1 bg-[#1e293b] text-white text-sm px-3 py-2 rounded-l-md outline-none disabled:opacity-50"
        placeholder="Écris ton message..."
      />
      <button
        @click="sendMessage"
        :disabled="!canSend"
        class="bg-neonPink hover:bg-pink-600 transition px-4 py-2 rounded-r-md text-sm text-white font-semibold disabled:opacity-50"
      >
        Envoyer
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/useAuthStore'
import { socket } from '@/plugins/socket'

/**
 * Props :
 * - roomId : identifiant de la room à rejoindre
 * - canSend : booléen qui autorise ou non l'envoi de messages
 */
const props = defineProps<{
  roomId: number
  canSend: boolean
}>()

// Auth store : contient username, avatar, userId
const auth = useAuthStore()

// Message en cours d'écriture
const newMessage = ref('')

// Liste des messages reçus
const messages = ref<Array<{
  user: string
  avatar: string
  text: string
  time: string
  id: string
}>>([])

// Référence à l'élément pour scroll auto vers le bas
const bottomRef = ref<HTMLElement | null>(null)

// ID du client socket (utilisé pour savoir si un message vient de soi)
const myId = ref<string | undefined>('')

/**
 * Lifecycle : au montage du composant
 */
onMounted(() => {
  // Connexion au socket si pas déjà connecté
  if (!socket.connected) {
    socket.connect()
  }

  // Lors de la connexion socket
  socket.on('connect', () => {
    myId.value = socket.id

    // Rejoindre la room du chat avec l'identité utilisateur
    socket.emit('room:join-chat', {
      roomId: props.roomId,
      userId: auth.userId
    })
  })

  // Réception d'un message
  socket.on('game-chat:message', async (msg) => {
    if (msg.roomId === props.roomId) {
      messages.value.push(msg)
      await nextTick()
      bottomRef.value?.scrollIntoView({ behavior: 'smooth' })
    }
  })
})

/**
 * Lifecycle : au démontage du composant
 */
onUnmounted(() => {
  socket.emit('leave-chat-room', {
    roomId: props.roomId,
    userId: auth.userId
  })

  socket.off('game-chat:message')
  socket.off('connect')
})

/**
 * Fonction pour envoyer un message
 */
function sendMessage() {
  // Vérification de la validité du message
  if (!newMessage.value.trim() || !props.canSend) {
    console.warn('Message vide ou envoi non autorisé.')
    return
  }

  const now = new Date()
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  const message = {
    user: auth.username,
    avatar: auth.avatar,
    text: newMessage.value.trim(),
    time,
    id: socket.id,
    roomId: props.roomId,
  }

  // Émission du message vers le serveur
  socket.emit('game-chat:message', message)

  // Réinitialisation du champ
  newMessage.value = ''
}
</script>
