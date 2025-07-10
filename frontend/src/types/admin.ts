// Types pour l'interface d'administration

export interface User {
  id: number
  username: string
  email: string
  roles: string[]
  avatar?: string
  createdAt: string
  lastActive?: string
}

export interface Room {
  id: number
  name: string
  description?: string
  game: {
    id: number
    name: string
    image: string
  }
  owner: {
    username: string
    avatar?: string
  }
  currentPlayers: number
  maxPlayers: number
  status: string
  createdAt: string
}

export interface Game {
  id: number
  name: string
  image: string
  description?: string
}

export interface Stats {
  totalUsers: number
  totalRooms: number
  activeUsers: number
  activeRooms: number
  newUsersToday: number
  newRoomsToday: number
}
