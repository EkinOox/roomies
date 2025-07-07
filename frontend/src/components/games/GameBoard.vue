<template>
  <div class="game">
    <div class="board">
      <GameCell v-for="(cell, i) in cells" :key="i" :number="cell" />
    </div>

    <div class="buttons">
      <div class="info">
        <h3 class="welcomeText text-neonBlue font-bold mb-2">🎮 Bienvenue dans 2048 !</h3>
        <p class="welcomeText">Votre score : <strong>{{ score }}</strong></p>
        <p class="welcomeText text-green-600">Meilleur score : <strong>{{ bestScore }}</strong></p>
      </div>

      <div class="buttonsUp mt-4 mb-2">
        <button class="btn" @click="move('ArrowUp')" :disabled="isSpectator">
          <img id="ArrowUp" src="/img/up.png" />
        </button>
      </div>

      <div class="flex justify-center gap-2">
        <button class="btn" @click="move('ArrowLeft')" :disabled="isSpectator">
          <img id="ArrowLeft" src="/img/left.png" />
        </button>
        <button class="btn" @click="move('ArrowDown')" :disabled="isSpectator">
          <img id="ArrowDown" src="/img/down.png" />
        </button>
        <button class="btn" @click="move('ArrowRight')" :disabled="isSpectator">
          <img id="ArrowRight" src="/img/right.png" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import GameCell from './GameCell.vue'
import { socket } from '@/plugins/socket'

const props = defineProps({
  roomId: String,
  userId: String,
  isSpectator: Boolean
})

const cells = ref([])
const score = ref(0)
const bestScore = ref(0)

function shuffle(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr
}

function init() {
  cells.value = shuffle([
    2, 2, 0, 0,
    0, 0, 0, 0,
    0, 0, 0, 0,
    0, 0, 0, 0
  ])
  emitState()
}

function checkTurns(i) {
  const v = cells.value[i]
  return v === cells.value[i + 1] || v === cells.value[i - 1] || v === cells.value[i + 4] || v === cells.value[i - 4]
}

function addCells() {
  const empty = cells.value.map((val, i) => val === 0 ? i : null).filter(i => i !== null)
  if (empty.length > 0) {
    const index = shuffle(empty)[0]
    cells.value[index] = 2
  } else {
    const movesAvailable = cells.value.some((_, i) => checkTurns(i))
    if (!movesAvailable) {
      alert('Game Over!')
    }
  }
}

function move(dir) {
  if (props.isSpectator) return

  const arr = []
  for (let k = 1; k <= 4; k++) {
    moveInDirection(dir, k, arr)
  }
  addBtnClass(dir)
  addCells()
  updateBestScore()
  emitState()
}

function updateBestScore() {
  if (score.value > bestScore.value) {
    bestScore.value = score.value
    localStorage.setItem('room-best-score', bestScore.value)
  }
}

function moveInDirection(direction, k, arr) {
  const grid = cells.value

  const moveCell = (from, to) => {
    if (grid[to] === 0 || (k === 3 && grid[to] === grid[from] && !arr.includes(from))) {
      if (k === 3 && grid[to] === grid[from]) {
        grid[to] += grid[from]
        score.value += grid[to]
        grid[from] = 0
        arr.push(to)
      } else if (k !== 3) {
        grid[to] = grid[from]
        grid[from] = 0
      }
    }
  }

  const apply = {
    ArrowLeft() {
      for (let i = 0; i < 16; i++) {
        const j = i - 1
        if (i % 4 !== 0 && j >= 0) moveCell(i, j)
      }
    },
    ArrowRight() {
      for (let i = 15; i >= 0; i--) {
        const j = i + 1
        if (i % 4 !== 3 && j < 16) moveCell(i, j)
      }
    },
    ArrowUp() {
      for (let i = 0; i < 16; i++) {
        const j = i - 4
        if (j >= 0) moveCell(i, j)
      }
    },
    ArrowDown() {
      for (let i = 15; i >= 0; i--) {
        const j = i + 4
        if (j < 16) moveCell(i, j)
      }
    }
  }

  if (apply[direction]) apply[direction]()
}

function emitState() {
  socket.emit('game:update', {
    roomId: props.roomId,
    state: {
      cells: cells.value,
      score: score.value,
      bestScore: bestScore.value
    }
  })
}

function addBtnClass(direction) {
  const btn = document.getElementById(direction)
  if (btn) {
    btn.className += ' effect effect-before'
    setTimeout(() => (btn.className = ''), 200)
  }
}

onMounted(() => {
  const stored = localStorage.getItem('room-best-score')
  bestScore.value = stored ? parseInt(stored) : 0

  socket.emit('room:join', {
    roomId: props.roomId,
    userId: props.userId
  })

  socket.on('game:state', (state) => {
    if (state?.cells) cells.value = [...state.cells]
    if (typeof state.score === 'number') score.value = state.score
    if (typeof state.bestScore === 'number') {
      bestScore.value = state.bestScore
      localStorage.setItem('room-best-score', bestScore.value)
    }
  })

  socket.on('connect', () => {
    socket.emit('room:join', {
      roomId: props.roomId,
      userId: props.userId
    })
  })

  window.addEventListener('keydown', (e) => {
    if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.code)) {
      move(e.code)
    }
  })

  // Initialise si personne dans la room
  init()
})
</script>


<style scoped>
.game {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  flex-direction: row;
  gap: 2rem;
  flex-wrap: wrap;
}

.board {
  display: flex;
  flex-wrap: wrap;
  width: 600px;
  height: 600px;
  background-color: #d0d6da;
  padding: 6px;
  border-radius: 8px;
}

.buttons {
  min-width: 250px;
  padding: 10px;
  background-color: #d0d6da;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.buttonsUp {
  display: flex;
  justify-content: center;
}

.info {
  text-align: center;
}

.welcomeText {
  color: #555;
}

.btn {
  width: 100px;
  height: 100px;
  background: inherit;
  border: none;
  outline: none;
}

.btn img {
  width: 100%;
  height: 100%;
  margin: 5px;
  position: relative;
}

.effect {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  transform: scale(0.001, 0.001);
  overflow: hidden;
}

.effect-before {
  animation: effect_dylan 0.8s ease-out;
}

@keyframes effect_dylan {
  50% {
    transform: scale(1.5, 1.5);
    opacity: 0;
  }
  99% {
    transform: scale(0.001, 0.001);
    opacity: 0;
  }
  100% {
    transform: scale(0.001, 0.001);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .game {
    flex-direction: column;
    align-items: center;
  }

  .buttons {
    margin-top: 1rem;
  }
}
</style>
