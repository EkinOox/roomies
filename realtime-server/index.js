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

  socket.on("global-message", (msg) => {
    console.log(`📨 Message reçu : ${msg}`);

    io.emit("global-message", msg);
  });

  socket.on("disconnect", () => {
    console.log(`🔌 Client déconnecté : ${socket.id}`);
  });
});

server.listen(3000, () => {
  console.log("🖥️ Socket.IO serveur lancé sur port 3000");
});
