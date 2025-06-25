const { Server } = require("socket.io")
const http = require("http")

const server = http.createServer()
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
})

const roomParticipants = new Map()
const userSockets = new Map()

io.on("connection", (socket) => {
  console.log(`🟢 Client connecté : ${socket.id}`)

  // Chat uniquement
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

  // Rejoindre la room
  socket.on("room:join", ({ roomId, userId, maxPlayers }) => {
    if (!roomId || !userId) {
      socket.emit("room:error", { message: "Informations invalides" })
      return
    }

    const current = roomParticipants.get(roomId) || new Set()
    const isSpectator = current.size >= maxPlayers
    const alreadyInside = current.has(userId)

    if (!alreadyInside && isSpectator) {
      socket.join(`chat_${roomId}`)
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

    io.to(`room_${roomId}`).emit("room:update", {
      roomId,
      participantsCount: current.size
    })

    socket.emit("room:status", {
      spectator: false,
      message: "Rejoint avec succès."
    })

    console.log(`👤 ${userId} a rejoint la room ${roomId}`)
  })

  // Quitter la room volontairement
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

  // Chat
  socket.on("game-chat:message", (msg) => {
    if (!msg.roomId) return ("no roomId provided")
    io.to(`chat_${msg.roomId}`).emit("game-chat:message", msg)
    console.log(`💬 [${msg.roomId}] ${msg.user}: ${msg.text}`)
  })

  // Chat global
  socket.on("chat:message", (msg) => {
    if (!msg || !msg.text) return
    io.emit("chat:message", msg)
    console.log(`🌐 [GLOBAL] ${msg.user}: ${msg.text}`)
  })

  // Déconnexion
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
  console.log("🖥️ Socket.IO serveur lancé sur port 3000")
})
