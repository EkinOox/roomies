
<template>
  <div class="flex flex-col items-center justify-center w-full h-full">
    <h3 class="text-2xl font-bold mb-6 text-neonBlue">Jeu d'échecs</h3>
    <div class="bg-[#1e293b] p-4 rounded-xl border border-neonBlue shadow-[4px_4px_10px_#000000,-4px_-4px_10px_#ffffff20]">
      <TheChessboard :position="fen" @drop="onDrop" board-style="width: 100%; max-width: 400px;" />
    </div>
    <p v-if="gameOver" class="mt-4 text-lg text-red-400 font-bold">Partie terminée</p>
    <button
      v-if="gameOver"
      @click="resetGame"
      class="mt-4 px-4 py-2 rounded-lg bg-neonPink hover:bg-pink-600 transition text-white shadow"
    >
      Rejouer
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Chess } from 'chess.js'
import { TheChessboard } from 'vue3-chessboard'
import 'vue3-chessboard/style.css'

const game = new Chess()
const fen = ref(game.fen())
const gameOver = ref(false)

function onDrop({ sourceSquare, targetSquare }) {
  const move = game.move({ from: sourceSquare, to: targetSquare, promotion: 'q' })
  if (move) {
    fen.value = game.fen()
    if (game.isGameOver()) {
      gameOver.value = true
    }
  }
}

function resetGame() {
  game.reset()
  fen.value = game.fen()
  gameOver.value = false
}
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
