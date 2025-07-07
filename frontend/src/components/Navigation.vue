<template>
  <div class="flex justify-center mt-6 z-30 relative">
    <div class="w-full max-w-7xl p-2 rounded-xl relative">
      <Menubar :model="items" class="rounded-xl shadow-[4px_4px_10px_#000000,-4px_-4px_10px_#ffffff80]">
        <template #item="{ item, hasSubmenu, root }">
          <a v-ripple class="flex items-center px-2 py-2 rounded-lg mx-2 my-2
                    shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                    transition-all duration-300
                    hover:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
                    hover:cursor-pointer" @click="item.command">
            <i v-if="item.icon" :class="[item.icon, { 'mx-2': root, 'mx-3': !root }]"></i>
            <span>{{ item.label }}</span>
            <Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge" />
            <i v-if="hasSubmenu" :class="[
              'pi pi-angle-down ml-auto',
              { 'pi-angle-down': root, 'pi-angle-right': !root }
            ]"></i>
          </a>
        </template>

        <template #end>
          <div class="flex items-center gap-6 relative">
            <!-- Recherche -->
            <div class="relative">
              <InputText v-if="isAuthenticated" v-model="searchQuery" placeholder="Search" type="text" class="w-32 sm:w-auto rounded-full
                                shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                                transition-all duration-300
                                focus:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
                                focus:outline-none px-3 py-3" />

              <ul v-if="searchQuery && searchQuery.length > 1"
                class="absolute w-64 bg-[#1e293b] mt-2 rounded-lg z-50 shadow-xl overflow-hidden">
                <template v-if="searchResults.length > 0">
                  <li v-if="gamesResults.length" class="text-sm px-4 py-2 text-gray-400 bg-[#0f172a] font-semibold">
                    Jeux</li>
                  <li v-for="item in gamesResults" :key="'game-' + item.id" @click="handleResultClick(item)"
                    class="px-4 py-2 hover:bg-[#334155] cursor-pointer transition flex justify-between">
                    <span>{{ item.name }}</span>
                    <span class="text-xs italic text-gray-400">jeu</span>
                  </li>

                  <li v-if="roomsResults.length" class="text-sm px-4 py-2 text-gray-400 bg-[#0f172a] font-semibold">
                    Rooms</li>
                  <li v-for="item in roomsResults" :key="'room-' + item.id" @click="handleResultClick(item)"
                    class="px-4 py-2 hover:bg-[#334155] cursor-pointer transition flex justify-between">
                    <span>{{ item.name }}</span>
                    <span class="text-xs italic text-gray-400">room</span>
                  </li>
                </template>

                <li v-else class="px-4 py-3 text-gray-400 italic text-center">Aucun résultat trouvé</li>
              </ul>
            </div>

            <!-- Avatar / Auth -->
            <div @click="handleUserClick" class="cursor-pointer rounded-full
                        shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                        transition-shadow duration-300
                        hover:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80] h-auto">
              <Avatar v-if="isAuthenticated" :image="userAvatar" shape="circle" class="cursor-pointer" />
              <i v-else class="pi pi-user text-xl text-white p-3"></i>
            </div>

            <!-- Logout -->
            <i v-if="isAuthenticated" @click="logout" class="pi pi-sign-out text-xl text-white cursor-pointer"></i>
          </div>
        </template>
      </Menubar>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"
import { useAuthStore } from "@/stores/useAuthStore"

import Menubar from "primevue/menubar"
import InputText from "primevue/inputtext"
import Avatar from "primevue/avatar"
import Badge from "primevue/badge"

const router = useRouter()
const auth = useAuthStore()

const isAuthenticated = computed(() => auth.isAuthenticated)
const userAvatar = computed(() => auth.avatar)

const items = ref([
  {
    label: "Accueil",
    icon: "pi pi-home",
    command: () => router.push("/")
  },
  {
    label: "Rooms",
    icon: "pi pi-users",
    items: [
      { label: "Toutes les rooms", icon: "pi pi-pencil", command: () => router.push("/rooms") },
      { separator: true },
      { label: "2048", icon: "pi pi-bolt", command: () => router.push("/rooms/game/2048") },
      { label: "Morpion", icon: "pi pi-server", command: () => router.push("/rooms/game/morpion") },
      { label: "échec", icon: "pi pi-server", command: () => router.push("/rooms/game/echecs") },
    ],
  },
  {
    label: "Contact",
    icon: "pi pi-envelope",
    command: () => router.push("/contact")
  }

])

// Recherche
const searchQuery = ref('')
const searchResults = ref<{ type: 'game' | 'room'; id: number; name: string }[]>([])

const gamesResults = computed(() => searchResults.value.filter(r => r.type === 'game'))
const roomsResults = computed(() => searchResults.value.filter(r => r.type === 'room'))

watch(searchQuery, async (val) => {
  if (!val || val.length < 2) {
    searchResults.value = []
    return
  }

  try {
    const [gamesRes, roomsRes] = await Promise.all([
      axios.get('http://localhost:8000/api/games', { headers: { Authorization: `Bearer ${auth.token}` } }),
      axios.get('http://localhost:8000/api/rooms', { headers: { Authorization: `Bearer ${auth.token}` } }),
    ])

    const games = gamesRes.data
      .filter((g: any) => g.name.toLowerCase().includes(val.toLowerCase()))
      .map((g: any) => ({ type: 'game', id: g.id, name: g.name }))

    const rooms = roomsRes.data
      .filter((r: any) => r.name.toLowerCase().includes(val.toLowerCase()))
      .map((r: any) => ({ type: 'room', id: r.id, name: r.name }))

    searchResults.value = [...games, ...rooms].slice(0, 5)
  } catch (err) {
    console.error('Erreur de recherche :', err)
  }
})

function handleResultClick(item: { type: string; id: number; name: string }) {
  searchQuery.value = ''
  searchResults.value = []
  if (item.type === 'game') {
    router.push(`/rooms/game/${item.name.toLowerCase()}`)
  } else {
    router.push(`/rooms/${item.id}`)
  }
}

const goToAuth = () => router.push({ name: "auth" })
const goToProfile = () => router.push({ name: "profile" })

function handleUserClick() {
  if (auth.isAuthenticated && !auth.isTokenExpired()) {
    goToProfile()
  } else {
    auth.logout()
    goToAuth()
  }
}

function logout() {
  auth.setToken(null)
  router.push({ name: "auth" })
}
</script>
