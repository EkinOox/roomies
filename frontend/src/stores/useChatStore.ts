// stores/chat.ts
import { defineStore } from 'pinia'

type ChatMessage = {
  user: string
  avatar: string
  text: string
  time: string
  id: string
}

export const useChatStore = defineStore('chat', {
  state: () => ({
    messages: [] as { user: string; avatar: string; text: string; time: string; id: string }[],
  }),
  actions: {
    addMessage(msg: ChatMessage) {
      this.messages.push(msg)
      if (this.messages.length > 300) {
      this.messages.splice(0, this.messages.length - 150)
      }
      this.saveToLocal()
    },
    saveToLocal() {
      localStorage.setItem('chatMessages', JSON.stringify(this.messages))
    },
    loadFromLocal() {
      const saved = localStorage.getItem('chatMessages')
      if (saved) this.messages = JSON.parse(saved)
    }
  }
})
