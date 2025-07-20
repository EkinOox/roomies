const { Server } = require("socket.io")
const http = require("http")

const server = http.createServer()
const io = new Server(server, {
  cors: {
    origin: "http://localhost:5173",
    methods: ["GET", "POST"]
  }
})

// === UTILITAIRE : Communication avec le backend ===
const updateRoomStatusInDB = async (roomId, status) => {
  try {
    // Note: En production, il faudrait un token de service pour l'authentification
    console.log(`?? Mise � jour statut room ${roomId} ? ${status}`)
    // Ici on pourrait faire un appel HTTP vers le backend si n�cessaire
    // Pour l'instant, on se contente de logger
  } catch (error) {
    console.error(`? Erreur mise � jour statut room ${roomId}:`, error)
  }
}

// Stores
const roomParticipants = new Map()
const userSockets = new Map()
const gameState = new Map()
const morpionStates = new Map()
const morpionPlayersMap = new Map()
const userIdToName = new Map()

const getUsername = (id) => userIdToName.get(id) || `Joueur ${id}`

io.on("connection", (socket) => {
  console.log(`🟢 Client connecté : ${socket.id}`)

  // === CHAT uniquement ===
  socket.on('room:join-chat', ({ roomId, userId }) => {
    if (!roomId || !userId) return
    socket.join(`chat_${roomId}`)
    userSockets.set(socket.id, { userId, roomId })
    console.log(`💬 [CHAT] ${userId} a rejoint chat_${roomId}`)
  })

  socket.on('leave-chat-room', ({ roomId }) => {
    if (roomId) {
      socket.leave(`chat_${roomId}`)
      console.log(`💬 Socket ${socket.id} a quitté chat_${roomId}`)
    }
  })

  // === Room principale (joueur ou spectateur) ===
  socket.on("room:join", ({ roomId, userId, maxPlayers, username }) => {
    if (!roomId || !userId) {
      socket.emit("room:error", { message: "Informations invalides" })
      return
    }

    userId = String(userId)
    if (username) userIdToName.set(userId, username)

    const current = roomParticipants.get(roomId) || new Set()
    const isSpectator = maxPlayers && current.size >= maxPlayers
    const alreadyInside = current.has(userId)

    if (!alreadyInside && isSpectator) {
      socket.join(`chat_${roomId}`)
      socket.join(`room_${roomId}`)
      socket.emit("room:status", {
        spectator: true,
        message: "La room est pleine, vous êtes en mode spectateur."
      })
      userSockets.set(socket.id, { userId, roomId })
      return
    }

    if (!alreadyInside) {
      current.add(userId)
      roomParticipants.set(roomId, current)
    }

    socket.join(`room_${roomId}`)
    socket.join(`chat_${roomId}`)
    userSockets.set(socket.id, { userId, roomId })

    // GESTION JOUEURS MORPION
    if (!morpionPlayersMap.has(roomId)) {
      morpionPlayersMap.set(roomId, { X: null, O: null })
    }

    const players = morpionPlayersMap.get(roomId)
    const currentUsers = roomParticipants.get(roomId) || new Set()

    if (!currentUsers.has(players.X)) players.X = null
    if (!currentUsers.has(players.O)) players.O = null

    if (!players.X) players.X = userId
    else if (!players.O && players.X !== userId) players.O = userId

    const role = players.X === userId ? 'X' : players.O === userId ? 'O' : null
    console.log(`🎭 Attribution du rôle pour ${userId} dans room ${roomId} :`, players)
    socket.emit("morpion:role", { role })

    // Init ou maj état morpion
    if (!morpionStates.has(roomId)) {
      morpionStates.set(roomId, {
        board: Array(9).fill(''),
        currentPlayer: 'X',
        players: { X: players.X, O: players.O },
        playersInfo: {
          X: getUsername(players.X),
          O: getUsername(players.O)
        },
        winner: null,
        startTime: null
      })
    } else {
      const state = morpionStates.get(roomId)
      state.players = { X: players.X, O: players.O }
      state.playersInfo = {
        X: getUsername(players.X),
        O: getUsername(players.O)
      }
    }

    if (players.X && players.O) {
      io.to(`room_${roomId}`).emit("morpion:state", morpionStates.get(roomId))
    }

    io.to(`room_${roomId}`).emit("room:update", {
      roomId,
      participantsCount: current.size
    })

    socket.emit("room:status", {
      spectator: false,
      message: "Rejoint avec succès."
    })

    const state = gameState.get(roomId)
    if (state) socket.emit("game:state", state)
  })

  // === Lancer le morpion ===
  socket.on('morpion:start', ({ roomId }, callback) => {
    if (!roomId) return callback?.({ error: true, message: "Room ID manquant" })

    const state = morpionStates.get(roomId)
    if (!state || !state.players.X || !state.players.O) {
      return callback?.({ error: true, message: "La room n'est pas prête (2 joueurs requis)" })
    }

    state.board = Array(9).fill('')
    state.currentPlayer = 'X'
    state.winner = null
    state.startTime = Date.now()

    // ?? Notifier que la partie commence (room ? active dans le backend)
    io.to(`room_${roomId}`).emit('room:status-changed', { 
      roomId, 
      status: 'active',
      message: 'La partie a commenc� !' 
    })

    io.to(`room_${roomId}`).emit('morpion:state', state)
    callback?.({ success: true })
  })

  // === Déplacement morpion
  socket.on('morpion:move', ({ roomId, index, userId }) => {
    const state = morpionStates.get(roomId)
    if (!state || state.winner || index < 0 || index > 8) return

    const playerSymbol = Object.keys(state.players).find(key => state.players[key] === userId)
    if (!playerSymbol || state.currentPlayer !== playerSymbol) return
    if (state.board[index]) return

    state.board[index] = playerSymbol

    const winPatterns = [
      [0, 1, 2], [3, 4, 5], [6, 7, 8],
      [0, 3, 6], [1, 4, 7], [2, 5, 8],
      [0, 4, 8], [2, 4, 6]
    ]

    const hasWon = winPatterns.some(([a, b, c]) =>
      state.board[a] === playerSymbol &&
      state.board[b] === playerSymbol &&
      state.board[c] === playerSymbol
    )

    if (hasWon) {
      state.winner = playerSymbol
      // ?? Partie termin�e avec un gagnant
      io.to(`room_${roomId}`).emit('room:status-changed', { 
        roomId, 
        status: 'finished',
        message: `?? ${userIdToName.get(userId) || userId} a gagn� !`,
        winner: playerSymbol
      })
    } else if (state.board.every(cell => cell)) {
      state.winner = 'draw'
      // ?? Partie termin�e en �galit�  
      io.to(`room_${roomId}`).emit('room:status-changed', { 
        roomId, 
        status: 'finished',
        message: '?? Match nul !',
        winner: null
      })
    } else {
      state.currentPlayer = state.currentPlayer === 'X' ? 'O' : 'X'
    }

    state.playersInfo = {
      X: getUsername(state.players.X),
      O: getUsername(state.players.O)
    }

    io.to(`room_${roomId}`).emit('morpion:state', state)
  })

  // === Chat
  socket.on("game-chat:message", (msg) => {
    if (!msg.roomId) return
    io.to(`chat_${msg.roomId}`).emit("game-chat:message", msg)
    console.log(`💬 [${msg.roomId}] ${msg.user}: ${msg.text}`)
  })

  socket.on("chat:message", (msg) => {
    if (!msg || !msg.text) return
    io.emit("chat:message", msg)
    console.log(`🌝 [GLOBAL] ${msg.user}: ${msg.text}`)
  })

  // === Quitter la room
  socket.on("leave-room", ({ roomId, userId }) => {
    if (!roomId || !userId) return

    const current = roomParticipants.get(roomId)
    if (current) {
      current.delete(userId)
      roomParticipants.set(roomId, current)

      io.to(`room_${roomId}`).emit("room:update", {
        roomId,
        participantsCount: current.size
      })

      console.log(`🚪 ${userId} a quitté la room ${roomId} volontairement`)
    }

    socket.leave(`room_${roomId}`)
    socket.leave(`chat_${roomId}`)
    userSockets.delete(socket.id)
  })

  // === Déconnexion
  socket.on("disconnect", () => {
    const info = userSockets.get(socket.id)
    if (!info) {
      console.log(`🔌 Déconnexion orpheline : ${socket.id}`)
      return
    }

    const { userId, roomId } = info
    const current = roomParticipants.get(roomId)

    if (current) {
      current.delete(userId)
      roomParticipants.set(roomId, current)

      io.to(`room_${roomId}`).emit("room:update", {
        roomId,
        participantsCount: current.size
      })

      console.log(`❌ ${userId} a quitté la room ${roomId}`)
    }

    userSockets.delete(socket.id)
    console.log(`🔌 Client déconnecté : ${socket.id}`)
  })
})

server.listen(3000, () => {
  console.log("🖥︝ Socket.IO serveur lancé sur port 3000")
})
