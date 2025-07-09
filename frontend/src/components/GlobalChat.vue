<template>
  <div class="fixed bottom-6 right-6 z-50">
    <!-- Fenêtre de chat visible uniquement si `open` est vrai -->
    <div
      v-if="open"
      class="w-80 h-[28rem] bg-gradient-to-br from-[#0f172a]/90 to-[#1e293b]/90 backdrop-blur-xl
             border border-neonBlue/30 rounded-xl shadow-[0_0_25px_rgba(0,255,255,0.2)]
             flex flex-col overflow-hidden"
    >
      <!-- En-tête du chat -->
      <div class="flex justify-between items-center bg-neonBlue/20 text-neonBlue px-4 py-3 font-bold shadow-inner">
        <span><i class="pi pi-comments"></i> Chat Global</span>
        <button
          @click="open = false"
          class="text-neonBlue hover:text-white transition text-lg"
        >
          &times;
        </button>
      </div>

      <!-- Liste des messages -->
      <div class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
        <div
          v-for="(msg, index) in chatStore.messages"
          :key="index"
          class="flex gap-2 items-end"
          :class="msg.id === myId ? 'justify-end' : 'justify-start'"
        >
          <!-- Avatar utilisateur -->
          <img
            v-if="msg.avatar"
            :src="msg.avatar"
            alt="avatar"
            class="w-8 h-8 rounded-full border border-white/20"
            :class="msg.id === myId ? 'order-2 ml-2' : 'order-1 mr-2'"
          />

          <!-- Bulle du message -->
          <div
            :class="[
              'max-w-[75%] px-4 py-2 rounded-xl text-sm break-words whitespace-pre-wrap',
              msg.id === myId
                ? 'bg-neonPink text-white shadow-neon'
                : 'bg-[#1e293b] text-white/90 shadow-md'
            ]"
          >
            <div class="text-xs font-semibold text-neonPurple mb-1">
              {{ msg.id === myId ? auth.username : msg.user }}
            </div>
            <div>{{ msg.text }}</div>
            <div class="text-right text-xs text-white/40 mt-1">{{ msg.time }}</div>
          </div>
        </div>
        <!-- Ancre pour scroll automatique -->
        <div ref="bottomRef" />
      </div>

      <!-- Zone de saisie -->
      <div class="flex border-t border-white/10 p-2 bg-[#0f172a]/80 backdrop-blur-md">
        <input
          v-model="newMessage"
          @keydown.enter="sendMessage"
          class="flex-1 bg-[#1e293b] text-white text-sm px-3 py-2 rounded-l-md outline-none"
          placeholder="Écris ton message..."
        />
        <button
          @click="sendMessage"
          class="bg-neonPink hover:bg-pink-600 transition px-4 py-2 rounded-r-md
                 text-sm text-white font-semibold"
        >
          Envoyer
        </button>
      </div>
    </div>

    <!-- Bouton flottant pour ouvrir le chat -->
    <button
      v-if="!open"
      @click="open = true"
      class="w-14 h-14 flex items-center justify-center rounded-full
             bg-gradient-to-br from-[#FF4FCB] to-[#00F0FF] text-white
             shadow-[0_0_25px_rgba(0,255,255,0.6)]
             hover:scale-105 transition-transform duration-300 animate-float"
    >
      <i class="pi pi-comments"></i>
    </button>
  </div>
</template>

<script setup lang="ts">
/**
 * Imports
 */
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { io } from 'socket.io-client'
import { useAuthStore } from '@/stores/useAuthStore'
import { useChatStore } from '@/stores/useChatStore'
import 'primeicons/primeicons.css'

/**
 * État local
 */
const open = ref(false) // Contrôle de l'ouverture/fermeture du chat
const newMessage = ref('') // Contenu du message à envoyer
const bottomRef = ref<HTMLElement | null>(null) // Référence pour scroll auto
const myId = ref('') // ID socket du client

/**
 * Stores
 */
const auth = useAuthStore()
const chatStore = useChatStore()
chatStore.loadFromLocal()

/**
 * Socket.io configuration
 */
const socket = io('http://localhost:3000') // À adapter selon l’environnement (prod/dev)

/**
 * Événements liés au cycle de vie
 */
onMounted(() => {
  socket.on('connect', () => {
    myId.value = socket.id ?? ''
  })

  socket.on('chat:message', async (data) => {
    chatStore.addMessage(data)
    await nextTick()
    bottomRef.value?.scrollIntoView({ behavior: 'smooth' })
  })
})

onUnmounted(() => {
  socket.disconnect()
})

/**
 * Envoi d’un message via socket
 */
function sendMessage() {
  if (!newMessage.value.trim()) return

  const now = new Date()
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  const message = {
    user: auth.username || 'Moi',
    avatar: auth.avatar || '',
    text: newMessage.value,
    time,
    id: socket.id,
  }

  socket.emit('chat:message', message)
  newMessage.value = ''
}
</script>

<style scoped>
/* Animation de flottement du bouton */
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-6px);
  }
}
.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>
