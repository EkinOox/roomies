const { Server } = require("socket.io");
const http = require("http");

const server = http.createServer();
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

io.on("connection", (socket) => {
  console.log(`🟢 Client connecté : ${socket.id}`);

  // Quand un message est reçu
  socket.on("chat:message", (msg) => {
    console.log(`📨 Message reçu de ${msg.user}: ${msg.text}`);

    // Réémission à tout le monde (y compris l'émetteur)
    io.emit("chat:message", msg);
  });

  socket.on("disconnect", () => {
    console.log(`🔌 Client déconnecté : ${socket.id}`);
  });
});

server.listen(3000, () => {
  console.log("🖥️ Socket.IO serveur lancé sur port 3000");
});
