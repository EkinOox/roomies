import socket from "../services/socket";

export default {
  install: (app) => {
    socket.connect();

    // Optionnel : gestion des �v�nements globaux
    socket.on("connect", () => {
      console.log("Socket connecté :", socket.id);
    });

    socket.on("disconnect", () => {
      console.log("Socket déconnecté");
    });

    app.config.globalProperties.$socket = socket;
    app.provide("socket", socket);
  },
};
