<script setup>
import { inject, ref, onMounted } from 'vue';

const socket = inject("socket");
const messages = ref([]);
const input = ref("");

onMounted(() => {
  socket.on("global-message", (msg) => {
    console.log("📥 Message reçu :", msg);
    messages.value.push(msg);
  });
});

const sendMessage = () => {
  if (input.value.trim() === "") return;
  socket.emit("global-message", input.value);
  input.value = "";
};
</script>

<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-2">Chat Global</h2>
    <ul class="mb-2">
      <li v-for="(msg, index) in messages" :key="index">{{ msg }}</li>
    </ul>
    <input v-model="input" class="border p-1" @keyup.enter="sendMessage" placeholder="Tape un message" />
    <button @click="sendMessage" class="bg-blue-500 text-white px-2 py-1 ml-2">Envoyer</button>
  </div>
</template>
