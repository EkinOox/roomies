<script setup>
import { inject, ref, onMounted } from 'vue';

// On récupère l'instance de socket injectée dans le composant (probablement fournie par un plugin ou un composant parent)
const socket = inject("socket");

// Réactivité : tableau pour stocker les messages reçus
const messages = ref([]);

// Réactivité : variable liée à l'input utilisateur pour taper un message
const input = ref("");

// Au montage du composant, on écoute l'événement "global-message" envoyé par le serveur via socket
onMounted(() => {
  socket.on("global-message", (msg) => {
    console.log("📥 Message reçu :", msg);
    // On ajoute le message reçu dans la liste des messages (mise à jour de l'interface)
    messages.value.push(msg);
  });
});

// Fonction pour envoyer un message au serveur via socket
const sendMessage = () => {
  // On ignore les messages vides ou composés uniquement d'espaces
  if (input.value.trim() === "") return;
  // Envoi du message à tous via l'événement "global-message"
  socket.emit("global-message", input.value);
  // On vide le champ de saisie après envoi
  input.value = "";
};
</script>

<template>
  <div class="p-4">
    <!-- Titre du chat -->
    <h2 class="text-xl font-bold mb-2">Chat Global</h2>

    <!-- Liste des messages reçus -->
    <ul class="mb-2">
      <!-- Affichage de chaque message avec une clé unique -->
      <li v-for="(msg, index) in messages" :key="index">{{ msg }}</li>
    </ul>

    <!-- Champ de saisie du message, lié à la variable `input` -->
    <!-- Envoi du message à la touche Entrée -->
    <input v-model="input" class="border p-1" @keyup.enter="sendMessage" placeholder="Tape un message" />

    <!-- Bouton pour envoyer le message -->
    <button @click="sendMessage" class="bg-blue-500 text-white px-2 py-1 ml-2">Envoyer</button>
  </div>
</template>
