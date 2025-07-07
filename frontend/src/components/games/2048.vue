<template>
  <GameBoard :room-id="roomId" :user-id="userId" />
</template>

<script setup>
import GameBoard from './GameBoard.vue'

const props = defineProps({
  roomId: String,
  userId: String
})
</script>
