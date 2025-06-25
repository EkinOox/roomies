<template>
  <div class="w-full h-full bg-[#0f172a]/90 border border-neonBlue/30 rounded-xl shadow-xl flex flex-col overflow-hidden">
    <!-- En-tête -->
    <div class="bg-neonBlue/20 text-neonBlue px-4 py-3 font-bold flex justify-between items-center">
      <span><i class="pi pi-comments"></i> Chat de la partie</span>
    </div>

    <!-- Messages -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
      <div
        v-for="(msg, index) in messages"
        :key="index"
        class="flex gap-2 items-end"
        :class="msg.id === myId ? 'justify-end' : 'justify-start'"
      >
        <img
          v-if="msg.avatar"
          :src="msg.avatar"
          alt="avatar"
          class="w-8 h-8 rounded-full border border-white/20"
          :class="msg.id === myId ? 'order-2 ml-2' : 'order-1 mr-2'"
        />

        <div
          :class="[
            'max-w-[75%] px-4 py-2 rounded-xl text-sm break-words whitespace-pre-wrap',
            msg.id === myId ? 'bg-neonPink text-white shadow-neon' : 'bg-[#1e293b] text-white/90 shadow-md'
          ]"
        >
          <div class="text-xs font-semibold text-neonPurple mb-1">
            {{ msg.id === myId ? auth.username : msg.user }}
          </div>
          <div>{{ msg.text }}</div>
          <div class="text-right text-xs text-white/40 mt-1">{{ msg.time }}</div>
        </div>
      </div>
      <div ref="bottomRef" />
    </div>

    <!-- Saisie -->
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

const props = defineProps<{ roomId: number; canSend: boolean }>()
const auth = useAuthStore()

const newMessage = ref('')
const messages = ref<{ user: string; avatar: string; text: string; time: string; id: string }[]>([])
const bottomRef = ref<HTMLElement | null>(null)
const myId = ref<string | undefined>('')

onMounted(() => {
  if (!socket.connected) {
    socket.connect()
  }

  socket.on('connect', () => {
    myId.value = socket.id
    socket.emit('room:join-chat', { roomId: props.roomId, userId: auth.userId })
  })

  socket.on('game-chat:message', async (msg) => {
    if (msg.roomId === props.roomId) {
      messages.value.push(msg)
      await nextTick()
      bottomRef.value?.scrollIntoView({ behavior: 'smooth' })
    }
  })
})

onUnmounted(() => {
  socket.emit('leave-chat-room', { roomId: props.roomId, userId: auth.userId })
  socket.off('game-chat:message')
  socket.off('connect')
})

function sendMessage() {
  if (!newMessage.value.trim() || !props.canSend) return ("error: Cannot send empty message or not allowed to send")

  const now = new Date()
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  const message = {
    user: auth.username,
    avatar: auth.avatar,
    text: newMessage.value,
    time,
    id: socket.id,
    roomId: props.roomId,
  }

  socket.emit('game-chat:message', message)
  newMessage.value = ''
}
</script>
