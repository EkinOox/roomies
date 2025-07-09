<template>
  <!-- Conteneur principal centré avec marge haute -->
  <div class="flex justify-center mt-6 z-30 relative">
    <div class="w-full max-w-7xl p-2 rounded-xl relative">
      <!-- Barre de menu PrimeVue avec ombre néon légère -->
      <Menubar :model="items" class="rounded-xl shadow-[4px_4px_10px_#000000,-4px_-4px_10px_#ffffff80]">
        <!-- Template personnalisé pour chaque item du menu -->
        <template #item="{ item, hasSubmenu, root }">
          <a v-ripple class="flex items-center px-3 py-2 rounded-lg mx-2 my-2
                   shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                   transition-all duration-300
                   hover:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
                   hover:cursor-pointer
                   select-none" @click="item.command">
            <!-- Icône si elle existe, marges dynamiques selon racine -->
            <i v-if="item.icon" :class="[item.icon, { 'mx-2': root, 'mx-3': !root }]"></i>
            <!-- Label du menu -->
            <span>{{ item.label }}</span>
            <!-- Badge affiché à droite si présent -->
            <Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge" />
            <!-- Flèche indiquant un sous-menu -->
            <i v-if="hasSubmenu" :class="[
              'pi pi-angle-down ml-auto',
              { 'pi-angle-down': root, 'pi-angle-right': !root }
            ]"></i>
          </a>
        </template>

        <!-- Partie de droite de la barre (recherche, avatar, logout) -->
        <template #end>
          <div class="flex items-center gap-6 relative">
            <!-- Zone de recherche -->
            <div class="relative">
              <!-- Champ de recherche visible uniquement si connecté -->
              <InputText v-if="isAuthenticated" v-model="searchQuery" placeholder="Recherche" type="text" class="w-32 sm:w-auto rounded-full
                       shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                       transition-all duration-300
                       focus:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
                       focus:outline-none px-3 py-3" />

              <!-- Résultats de recherche sous le champ -->
              <ul v-if="searchQuery && searchQuery.length > 1"
                class="absolute w-64 bg-[#1e293b] mt-2 rounded-lg z-50 shadow-xl overflow-hidden">
                <template v-if="searchResults.length > 0">
                  <!-- Section Jeux -->
                  <li v-if="gamesResults.length"
                    class="text-sm px-4 py-2 text-gray-400 bg-[#0f172a] font-semibold select-none">
                    Jeux
                  </li>
                  <li v-for="item in gamesResults" :key="'game-' + item.id" @click="handleResultClick(item)"
                    class="px-4 py-2 hover:bg-[#334155] cursor-pointer transition flex justify-between">
                    <span>{{ item.name }}</span>
                    <span class="text-xs italic text-gray-400">jeu</span>
                  </li>

                  <!-- Section Rooms -->
                  <li v-if="roomsResults.length"
                    class="text-sm px-4 py-2 text-gray-400 bg-[#0f172a] font-semibold select-none">
                    Rooms
                  </li>
                  <li v-for="item in roomsResults" :key="'room-' + item.id" @click="handleResultClick(item)"
                    class="px-4 py-2 hover:bg-[#334155] cursor-pointer transition flex justify-between">
                    <span>{{ item.name }}</span>
                    <span class="text-xs italic text-gray-400">room</span>
                  </li>
                </template>

                <!-- Message quand aucun résultat -->
                <li class="px-4 py-3 text-gray-400 italic text-center select-none">
                  Aucun résultat trouvé
                </li>
              </ul>
            </div>

            <!-- Avatar ou icône utilisateur, clic gère la navigation -->
            <div @click="handleUserClick" class="cursor-pointer rounded-full
         shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
         transition-shadow transform duration-300
         hover:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
         hover:scale-110
         h-auto overflow-hidden">
              <Avatar v-if="isAuthenticated" :image="userAvatar" shape="circle"
                class="cursor-pointer block align-top leading-none" />

              <i v-else class="pi pi-user text-xl text-white p-3"></i>
            </div>


            <!-- Bouton déconnexion si connecté -->
            <i v-if="isAuthenticated" @click="logout"
              class="pi pi-sign-out text-xl text-white cursor-pointer hover:text-[#FF4FCB] transition-colors duration-300"
              title="Se déconnecter"></i>
          </div>
        </template>
      </Menubar>
    </div>
  </div>
</template>

<script setup lang="ts">
// Import des hooks Vue et modules nécessaires
import { ref, computed, watch } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"
import { useAuthStore } from "@/stores/useAuthStore"

// Import des composants PrimeVue
import Menubar from "primevue/menubar"
import InputText from "primevue/inputtext"
import Avatar from "primevue/avatar"
import Badge from "primevue/badge"

// Instance du routeur et du store d'authentification
const router = useRouter()
const auth = useAuthStore()

// Computed pour savoir si utilisateur est connecté et récupérer son avatar
const isAuthenticated = computed(() => auth.isAuthenticated)
const userAvatar = computed(() => auth.avatar)

// Menu principal avec labels, icônes, et commandes de navigation
const items = ref([
  {
    label: "Accueil",
    icon: "pi pi-home",
    command: () => router.push("/")
  },
  {
    label: "Rooms",
    icon: "pi pi-users",
    icone: "pi pi-pencil", badge: '4',
    items: [
      { label: "Toutes les rooms", command: () => router.push("/rooms") },
      { separator: true },
      { label: "2048", icon: "pi pi-bolt", command: () => router.push("/rooms/game/2048") },
      { label: "Morpion", icon: "pi pi-server", command: () => router.push("/rooms/game/morpion") },
      { label: "Échec", icon: "pi pi-server", command: () => router.push("/rooms/game/echecs") },
      { label: "Quizz", icon: "pi pi-question-circle", command: () => router.push("/rooms/game/quizz") },
    ],
  },
  {
    label: "Contact",
    icon: "pi pi-envelope",
    command: () => router.push("/contact")
  }
])

// États réactifs pour la recherche
const searchQuery = ref('')
const searchResults = ref<{ type: 'game' | 'room'; id: number; name: string }[]>([])

// Résultats filtrés par type pour affichage
const gamesResults = computed(() => searchResults.value.filter(r => r.type === 'game'))
const roomsResults = computed(() => searchResults.value.filter(r => r.type === 'room'))

// Surveille la saisie dans la barre de recherche
watch(searchQuery, async (val) => {
  if (!val || val.length < 2) {
    searchResults.value = []
    return
  }

  try {
    // Appel simultané des API jeux et rooms avec token d'authentification
    const [gamesRes, roomsRes] = await Promise.all([
      axios.get('http://localhost:8000/api/games', { headers: { Authorization: `Bearer ${auth.token}` } }),
      axios.get('http://localhost:8000/api/rooms', { headers: { Authorization: `Bearer ${auth.token}` } }),
    ])

    // Filtrage et transformation des résultats selon la saisie
    const games = gamesRes.data
      .filter((g: any) => g.name.toLowerCase().includes(val.toLowerCase()))
      .map((g: any) => ({ type: 'game', id: g.id, name: g.name }))

    const rooms = roomsRes.data
      .filter((r: any) => r.name.toLowerCase().includes(val.toLowerCase()))
      .map((r: any) => ({ type: 'room', id: r.id, name: r.name }))

    // Limite à 5 résultats pour éviter surcharge visuelle
    searchResults.value = [...games, ...rooms].slice(0, 5)
  } catch (err) {
    console.error('Erreur de recherche :', err)
  }
})

// Gestion du clic sur un résultat de recherche
function handleResultClick(item: { type: string; id: number; name: string }) {
  // Réinitialisation du champ et des résultats
  searchQuery.value = ''
  searchResults.value = []

  // Navigation différente selon le type (jeu ou room)
  if (item.type === 'game') {
    router.push(`/rooms/game/${item.name.toLowerCase()}`)
  } else {
    router.push(`/rooms/${item.id}`)
  }
}

// Navigation vers la page d'authentification
const goToAuth = () => router.push({ name: "auth" })
// Navigation vers le profil utilisateur
const goToProfile = () => router.push({ name: "profile" })

// Gestion du clic sur l'avatar/utilisateur
function handleUserClick() {
  // Si connecté et token valide => profil, sinon redirection vers auth + déconnexion
  if (auth.isAuthenticated && !auth.isTokenExpired()) {
    goToProfile()
  } else {
    auth.logout()
    goToAuth()
  }
}

// Fonction de déconnexion
function logout() {
  auth.setToken(null)
  router.push({ name: "auth" })
}
</script>
