<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header avec titre et statistiques -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-4xl font-bold text-white flex items-center gap-3">
            <i class="pi pi-shield text-neonBlue"></i>
            Administration
            <!-- Debug temporaire -->
            <small class="text-sm text-gray-400 ml-4">
              Users: {{ users.length }} | Rooms: {{ rooms.length }} | Games: {{ games.length }}
            </small>
          </h1>
          <div class="flex items-center gap-4">
            <button
              @click="loadData"
              :disabled="loadingUsers || loadingRooms"
              title="Actualiser"
              class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-full disabled:opacity-50 transition-colors duration-200"
            >
              <i class="pi pi-refresh" :class="{ 'animate-spin': loadingUsers || loadingRooms }"></i>
            </button>
          </div>
        </div>

        <!-- Cards de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-neonBlue/20
                      shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm font-medium">Total Utilisateurs</p>
                <p class="text-2xl font-bold text-white">{{ stats.totalUsers }}</p>
                <p class="text-xs text-green-400 mt-1">
                  <i class="pi pi-arrow-up"></i> +{{ stats.newUsersToday }} aujourd'hui
                </p>
              </div>
              <div class="bg-neonBlue/20 p-3 rounded-full">
                <i class="pi pi-users text-neonBlue text-2xl"></i>
              </div>
            </div>
          </div>

          <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-500/20
                      shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm font-medium">Total Rooms</p>
                <p class="text-2xl font-bold text-white">{{ stats.totalRooms }}</p>
                <p class="text-xs text-green-400 mt-1">
                  <i class="pi pi-arrow-up"></i> +{{ stats.newRoomsToday }} aujourd'hui
                </p>
              </div>
              <div class="bg-green-500/20 p-3 rounded-full">
                <i class="pi pi-home text-green-500 text-2xl"></i>
              </div>
            </div>
          </div>

          <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-yellow-500/20
                      shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm font-medium">Utilisateurs Actifs</p>
                <p class="text-2xl font-bold text-white">{{ stats.activeUsers }}</p>
                <p class="text-xs text-yellow-400 mt-1">Dernières 24h</p>
              </div>
              <div class="bg-yellow-500/20 p-3 rounded-full">
                <i class="pi pi-circle text-green-500 text-2xl"></i>
              </div>
            </div>
          </div>

          <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-purple-500/20
                      shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-400 text-sm font-medium">Rooms Actives</p>
                <p class="text-2xl font-bold text-white">{{ stats.activeRooms }}</p>
                <p class="text-xs text-purple-400 mt-1">En cours</p>
              </div>
              <div class="bg-purple-500/20 p-3 rounded-full">
                <i class="pi pi-play text-purple-500 text-2xl"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Onglets de navigation avec design moderne -->
      <div class="bg-slate-800/30 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
        <TabView v-model:activeIndex="activeTab" :value="activeTab">
          <!-- Gestion des Utilisateurs -->
          <TabPanel value="0">
            <template #header>
              <div class="flex items-center gap-2">
                <i class="pi pi-users"></i>
                <span>Utilisateurs</span>
                <Badge :value="filteredUsers.length" severity="info" />
              </div>
            </template>

            <div class="space-y-6 p-6">
              <!-- Barre d'actions moderne -->
              <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4
                          bg-slate-700/50 p-4 rounded-xl border border-white/10">
                <div class="flex gap-3 items-center flex-wrap">
                  <Button
                    label="Nouvel Utilisateur"
                    icon="pi pi-plus"
                    @click="openUserDialog()"
                    class="bg-gradient-to-r from-neonBlue to-purple-600 hover:from-neonBlue/80 hover:to-purple-600/80
                           border-0 shadow-lg transition-all duration-300"
                  />
                  <Button
                    label="Exporter CSV"
                    icon="pi pi-download"
                    severity="secondary"
                    @click="exportUsers"
                    class="shadow-md"
                  />
                  <Button
                    label="Importer"
                    icon="pi pi-upload"
                    severity="help"
                    @click="() => {}"
                    class="shadow-md"
                  />
                </div>

                <div class="flex gap-3 items-center flex-wrap">
                  <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <InputText
                      v-model="userSearch"
                      placeholder="Rechercher un utilisateur..."
                      class="pl-10 w-64 bg-slate-800/50 border-white/20"
                    />
                  </div>
                  <Dropdown
                    v-model="userRoleFilter"
                    :options="roleFilterOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Filtrer par rôle"
                    class="w-48 bg-slate-800/50"
                    showClear
                  />
                </div>
              </div>

              <!-- Table moderne des utilisateurs -->
              <div class="bg-slate-800/30 rounded-xl border border-white/10 overflow-hidden">
                <DataTable
                  :value="filteredUsers"
                  :paginator="true"
                  :rows="10"
                  :loading="loadingUsers"
                  stripedRows
                  responsiveLayout="scroll"
                  paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                  currentPageReportTemplate="{first} à {last} sur {totalRecords} utilisateurs"
                  :rowsPerPageOptions="[5, 10, 25, 50]"
                  class="custom-datatable"
                  sortField="id"
                  :sortOrder="-1"
                >
                  <Column field="id" header="ID" sortable class="w-16">
                    <template #body="slotProps">
                      <Badge :value="slotProps.data.id" severity="info" />
                    </template>
                  </Column>

                  <Column field="username" header="Utilisateur" sortable>
                    <template #body="slotProps">
                      <div class="flex items-center gap-3">
                        <Avatar
                          :image="slotProps.data.avatar"
                          shape="circle"
                          size="normal"
                          class="border-2 border-white/20"
                        />
                        <div>
                          <div class="font-medium text-white">{{ slotProps.data.username }}</div>
                          <div class="text-sm text-gray-400">{{ slotProps.data.email }}</div>
                        </div>
                      </div>
                    </template>
                  </Column>

                  <Column field="roles" header="Rôles" class="w-32">
                    <template #body="slotProps">
                      <div class="flex gap-1 flex-wrap">
                        <Tag
                          v-for="role in slotProps.data.roles"
                          :key="role"
                          :value="role === 'ROLE_ADMIN' ? 'Admin' : 'User'"
                          :severity="role === 'ROLE_ADMIN' ? 'danger' : 'info'"
                          class="text-xs"
                        />
                      </div>
                    </template>
                  </Column>

                  <Column field="createdAt" header="Inscription" sortable class="w-32">
                    <template #body="slotProps">
                      <div class="text-sm">
                        <div class="text-white">{{ formatDate(slotProps.data.createdAt) }}</div>
                        <div class="text-gray-400">{{ formatTime(slotProps.data.createdAt) }}</div>
                      </div>
                    </template>
                  </Column>

                  <Column field="lastActive" header="Dernière activité" sortable class="w-32">
                    <template #body="slotProps">
                      <div class="flex items-center gap-2">
                        <i :class="[
                          'pi',
                          isUserOnline(slotProps.data.lastActive) ? 'pi-circle text-green-500' : 'pi-circle text-gray-500'
                        ]"></i>
                        <span class="text-sm text-gray-400">{{ getLastActiveText(slotProps.data.lastActive) }}</span>
                      </div>
                    </template>
                  </Column>

                  <Column header="Actions" class="w-32">
                    <template #body="slotProps">
                      <div class="flex gap-2">
                        <Button
                          icon="pi pi-eye"
                          size="small"
                          severity="info"
                          @click="viewUser(slotProps.data)"
                          title="Voir le profil"
                          rounded
                        />
                        <Button
                          icon="pi pi-pencil"
                          size="small"
                          severity="warning"
                          @click="openUserDialog(slotProps.data)"
                          title="Modifier"
                          rounded
                        />
                        <Button
                          icon="pi pi-trash"
                          size="small"
                          severity="danger"
                          @click="confirmDeleteUser(slotProps.data)"
                          title="Supprimer"
                          rounded
                        />
                      </div>
                    </template>
                  </Column>
                </DataTable>
              </div>
            </div>
          </TabPanel>

          <!-- Gestion des Rooms -->
          <TabPanel value="1">
            <template #header>
              <div class="flex items-center gap-2">
                <i class="pi pi-home"></i>
                <span>Rooms</span>
                <Badge :value="filteredRooms.length" severity="success" />
              </div>
            </template>

            <div class="space-y-6 p-6">
              <!-- Barre d'actions pour les rooms -->
              <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4
                          bg-slate-700/50 p-4 rounded-xl border border-white/10">
                <div class="flex gap-3 items-center flex-wrap">
                  <Button
                    label="Nouvelle Room"
                    icon="pi pi-plus"
                    @click="openRoomDialog()"
                    class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-500/80 hover:to-emerald-600/80
                           border-0 shadow-lg transition-all duration-300"
                  />
                  <Button
                    label="Exporter CSV"
                    icon="pi pi-download"
                    severity="secondary"
                    @click="exportRooms"
                    class="shadow-md"
                  />
                  <Button
                    label="Statistiques"
                    icon="pi pi-chart-bar"
                    severity="help"
                    @click="() => {}"
                    class="shadow-md"
                  />
                </div>

                <div class="flex gap-3 items-center flex-wrap">
                  <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <InputText
                      v-model="roomSearch"
                      placeholder="Rechercher une room..."
                      class="pl-10 w-64 bg-slate-800/50 border-white/20"
                    />
                  </div>
                  <Dropdown
                    v-model="roomGameFilter"
                    :options="gameFilterOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Filtrer par jeu"
                    class="w-48 bg-slate-800/50"
                    showClear
                  />
                  <Dropdown
                    v-model="roomStatusFilter"
                    :options="statusFilterOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Filtrer par statut"
                    class="w-48 bg-slate-800/50"
                    showClear
                  />
                </div>
              </div>

              <!-- Table moderne des rooms -->
              <div class="bg-slate-800/30 rounded-xl border border-white/10 overflow-hidden">
                <DataTable
                  :value="filteredRooms"
                  :paginator="true"
                  :rows="10"
                  :loading="loadingRooms"
                  stripedRows
                  responsiveLayout="scroll"
                  paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                  currentPageReportTemplate="{first} à {last} sur {totalRecords} rooms"
                  :rowsPerPageOptions="[5, 10, 25, 50]"
                  class="custom-datatable"
                  sortField="createdAt"
                  :sortOrder="-1"
                >
                  <Column field="id" header="ID" sortable class="w-16">
                    <template #body="slotProps">
                      <Badge :value="slotProps.data.id" severity="success" />
                    </template>
                  </Column>

                  <Column field="name" header="Room" sortable>
                    <template #body="slotProps">
                      <div class="flex items-center gap-3">
                        <img
                          :src="slotProps.data.game?.image || '/img/games/default.png'"
                          :alt="slotProps.data.game?.name"
                          class="w-10 h-10 rounded-lg object-cover border-2 border-white/20"
                        />
                        <div>
                          <div class="font-medium text-white">{{ slotProps.data.name }}</div>
                          <div class="text-sm text-gray-400">{{ slotProps.data.game?.name }}</div>
                        </div>
                      </div>
                    </template>
                  </Column>

                  <Column field="owner.username" header="Créateur" sortable class="w-32">
                    <template #body="slotProps">
                      <div class="flex items-center gap-2">
                        <Avatar
                          :image="slotProps.data.owner?.avatar"
                          shape="circle"
                          size="small"
                          class="border border-white/20"
                        />
                        <span class="text-sm text-white">{{ slotProps.data.owner?.username }}</span>
                      </div>
                    </template>
                  </Column>

                  <Column field="players" header="Joueurs" class="w-24">
                    <template #body="slotProps">
                      <div class="text-center">
                        <div class="text-neonBlue font-bold">
                          {{ slotProps.data.currentPlayers || 0 }} / {{ slotProps.data.maxPlayers }}
                        </div>
                        <ProgressBar
                          :value="getPlayerPercentage(slotProps.data)"
                          :showValue="false"
                          class="h-2 w-full mt-1"
                        />
                      </div>
                    </template>
                  </Column>

                  <Column field="status" header="Statut" class="w-24">
                    <template #body="slotProps">
                      <Tag
                        :value="getStatusLabel(slotProps.data.status)"
                        :severity="getStatusSeverity(slotProps.data.status)"
                        :icon="getStatusIcon(slotProps.data.status)"
                      />
                    </template>
                  </Column>

                  <Column field="createdAt" header="Créée le" sortable class="w-32">
                    <template #body="slotProps">
                      <div class="text-sm">
                        <div class="text-white">{{ formatDate(slotProps.data.createdAt) }}</div>
                        <div class="text-gray-400">{{ formatTime(slotProps.data.createdAt) }}</div>
                      </div>
                    </template>
                  </Column>

                  <Column header="Actions" class="w-32">
                    <template #body="slotProps">
                      <div class="flex gap-2">
                        <Button
                          icon="pi pi-eye"
                          size="small"
                          severity="info"
                          @click="viewRoom(slotProps.data)"
                          title="Voir la room"
                          rounded
                        />
                        <Button
                          icon="pi pi-pencil"
                          size="small"
                          severity="warning"
                          @click="openRoomDialog(slotProps.data)"
                          title="Modifier"
                          rounded
                        />
                        <Button
                          icon="pi pi-trash"
                          size="small"
                          severity="danger"
                          @click="confirmDeleteRoom(slotProps.data)"
                          title="Supprimer"
                          rounded
                        />
                      </div>
                    </template>
                  </Column>
                </DataTable>
              </div>
            </div>
          </TabPanel>
        </TabView>
      </div>
    </div>

    <!-- Dialog pour créer/modifier un utilisateur -->
    <Dialog
      v-model:visible="userDialogVisible"
      :header="editingUser ? 'Modifier Utilisateur' : 'Nouvel Utilisateur'"
      modal
      class="w-full max-w-2xl"
      :closable="false"
    >
      <form @submit.prevent="saveUser" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2 text-gray-300">Nom d'utilisateur *</label>
            <InputText
              v-model="userForm.username"
              required
              class="w-full bg-slate-800/50 border-white/20"
              :class="{ 'border-red-500': userFormErrors.username }"
            />
            <small v-if="userFormErrors.username" class="text-red-400">{{ userFormErrors.username }}</small>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-gray-300">Email *</label>
            <InputText
              v-model="userForm.email"
              type="email"
              required
              class="w-full bg-slate-800/50 border-white/20"
              :class="{ 'border-red-500': userFormErrors.email }"
            />
            <small v-if="userFormErrors.email" class="text-red-400">{{ userFormErrors.email }}</small>
          </div>
        </div>

        <div v-if="!editingUser">
          <label class="block text-sm font-medium mb-2 text-gray-300">Mot de passe *</label>
          <Password
            v-model="userForm.password"
            required
            class="w-full"
            :feedback="false"
            toggleMask
            placeholder="Entrez un mot de passe sécurisé"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-gray-300">Rôles *</label>
          <div class="flex gap-4">
            <div class="flex items-center">
              <Checkbox
                v-model="userForm.roles"
                inputId="role-user"
                value="ROLE_USER"
              />
              <label for="role-user" class="ml-2 text-gray-300">Utilisateur</label>
            </div>
            <div class="flex items-center">
              <Checkbox
                v-model="userForm.roles"
                inputId="role-admin"
                value="ROLE_ADMIN"
              />
              <label for="role-admin" class="ml-2 text-gray-300">Administrateur</label>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-gray-300">Avatar</label>
          <div class="flex items-center gap-4">
            <Avatar
              :image="userForm.avatar || '/img/avatar/1.png'"
              shape="circle"
              size="large"
              class="border-2 border-white/20"
            />
            <FileUpload
              mode="basic"
              name="avatar"
              :maxFileSize="1000000"
              accept="image/*"
              @select="onAvatarSelect"
              chooseLabel="Changer l'avatar"
              class="flex-1"
            />
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
          <Button
            label="Annuler"
            severity="secondary"
            @click="userDialogVisible = false"
            class="px-6"
          />
          <Button
            label="Sauvegarder"
            type="submit"
            :loading="savingUser"
            class="px-6 bg-gradient-to-r from-neonBlue to-purple-600 border-0"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog pour créer/modifier une room -->
    <Dialog
      v-model:visible="roomDialogVisible"
      :header="editingRoom ? 'Modifier Room' : 'Nouvelle Room'"
      modal
      class="w-full max-w-2xl"
      :closable="false"
    >
      <form @submit.prevent="saveRoom" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-2 text-gray-300">Nom de la room *</label>
            <InputText
              v-model="roomForm.name"
              required
              class="w-full bg-slate-800/50 border-white/20"
              placeholder="Entrez le nom de la room"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-gray-300">Jeu *</label>
            <Dropdown
              v-model="roomForm.gameId"
              :options="gameOptions"
              optionLabel="name"
              optionValue="id"
              placeholder="Sélectionner un jeu"
              class="w-full bg-slate-800/50"
              required
            >
              <template #option="slotProps">
                <div class="flex items-center gap-3">
                  <img
                    :src="slotProps.option.image"
                    :alt="slotProps.option.name"
                    class="w-8 h-8 rounded object-cover"
                  />
                  <span>{{ slotProps.option.name }}</span>
                </div>
              </template>
            </Dropdown>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-gray-300">Nombre max de joueurs *</label>
            <InputNumber
              v-model="roomForm.maxPlayers"
              :min="2"
              :max="8"
              class="w-full"
              required
              showButtons
            />
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
          <Button
            label="Annuler"
            severity="secondary"
            @click="roomDialogVisible = false"
            class="px-6"
          />
          <Button
            label="Sauvegarder"
            type="submit"
            :loading="savingRoom"
            class="px-6 bg-gradient-to-r from-green-500 to-emerald-600 border-0"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog de confirmation de suppression -->
    <ConfirmDialog>
      <template #message="slotProps">
        <div class="flex items-center gap-3">
          <i class="pi pi-exclamation-triangle text-orange-500 text-2xl"></i>
          <span>{{ slotProps.message }}</span>
        </div>
      </template>
    </ConfirmDialog>

    <!-- Toast pour les notifications -->
    <Toast position="top-right" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import TabPanel from 'primevue/tabpanel';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Badge from 'primevue/badge';
import Checkbox from 'primevue/checkbox';
import ProgressBar from 'primevue/progressbar';
import Password from 'primevue/password';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import TabView from 'primevue/tabview';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';



import axios from 'axios'
import type { User, Room, Game, Stats } from '../types/admin'

// Stores et utilitaires
const router = useRouter()
const auth = useAuthStore()
const toast = useToast()
const confirm = useConfirm()

// Vérification des permissions admin
onMounted(async () => {
  console.log('AdminView mounted')
  console.log('Auth state:', {
    isAuthenticated: auth.isAuthenticated,
    isAdmin: auth.isAdmin,
    token: auth.token?.substring(0, 50) + '...'
  })

  // Pour les tests, on charge directement les données
  console.log('Loading data for testing...')
  await loadData()

  await loadData()
})

// Fonction pour vérifier si l'utilisateur est admin (supprimée, utilise auth.isAdmin)

// États réactifs
const loadingUsers = ref(false)
const loadingRooms = ref(false)
const users = ref<User[]>([])
const rooms = ref<Room[]>([])
const games = ref<Game[]>([])
const activeTab = ref(0) // Onglet actif (0 = utilisateurs, 1 = rooms)
const stats = ref<Stats>({
  totalUsers: 0,
  totalRooms: 0,
  activeUsers: 0,
  activeRooms: 0,
  newUsersToday: 0,
  newRoomsToday: 0
})

// Filtres et recherche
const userSearch = ref('')
const userRoleFilter = ref<string | null>(null)
const roomSearch = ref('')
const roomGameFilter = ref<number | null>(null)
const roomStatusFilter = ref<string | null>(null)

// Dialogs
const userDialogVisible = ref(false)
const roomDialogVisible = ref(false)
const editingUser = ref<User | null>(null)
const editingRoom = ref<Room | null>(null)
const savingUser = ref(false)
const savingRoom = ref(false)

// Formulaires
const userForm = ref({
  username: '',
  email: '',
  password: '',
  roles: ['ROLE_USER'],
  avatar: '/img/avatar/1.png'
})

const userFormErrors = ref({
  username: '',
  email: ''
})

const roomForm = ref({
  name: '',
  gameId: null as number | null,
  maxPlayers: 4,
})

// Options pour les dropdowns
const roleFilterOptions = ref([
  { label: 'Tous les rôles', value: null },
  { label: 'Utilisateurs', value: 'ROLE_USER' },
  { label: 'Administrateurs', value: 'ROLE_ADMIN' }
])

const statusFilterOptions = ref([
  { label: 'Tous les statuts', value: null },
  { label: 'En attente', value: 'waiting' },
  { label: 'En cours', value: 'active' },
  { label: 'Terminée', value: 'finished' }
])

const gameOptions = computed(() =>
  games.value.map((game: { id: any; name: any; image: any }) => ({
    id: game.id,
    name: game.name,
    image: game.image,
    label: game.name,
    value: game.id
  }))
)

const gameFilterOptions = computed(() => [
  { label: 'Tous les jeux', value: null },
  ...games.value.map((game: { name: any; id: any }) => ({
    label: game.name,
    value: game.id
  }))
])

// Données filtrées
const filteredUsers = computed(() => {
  let filtered = users.value

  if (userSearch.value) {
    filtered = filtered.filter((user: { username: string; email: string }) =>
      user.username.toLowerCase().includes(userSearch.value.toLowerCase()) ||
      user.email.toLowerCase().includes(userSearch.value.toLowerCase())
    )
  }

  if (userRoleFilter.value) {
    filtered = filtered.filter((user: { roles: string | any[] }) =>
      user.roles.includes(userRoleFilter.value!)
    )
  }

  return filtered
})

const filteredRooms = computed(() => {
  let filtered = rooms.value

  if (roomSearch.value) {
    filtered = filtered.filter((room: { name: string }) =>
      room.name.toLowerCase().includes(roomSearch.value.toLowerCase())
    )
  }

  if (roomGameFilter.value) {
    filtered = filtered.filter((room: { game: { id: any } }) =>
      room.game.id === roomGameFilter.value
    )
  }

  if (roomStatusFilter.value) {
    filtered = filtered.filter((room: { status: any }) =>
      room.status === roomStatusFilter.value
    )
  }

  return filtered
})

// Fonctions utilitaires
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (date: string) => {
  return new Date(date).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const isUserOnline = (lastActive?: string) => {
  if (!lastActive) return false
  const now = new Date()
  const lastActiveDate = new Date(lastActive)
  const diffMinutes = (now.getTime() - lastActiveDate.getTime()) / (1000 * 60)
  return diffMinutes < 5 // En ligne si actif dans les 5 dernières minutes
}

const getLastActiveText = (lastActive?: string) => {
  if (!lastActive) return 'Jamais'

  const now = new Date()
  const lastActiveDate = new Date(lastActive)
  const diffMinutes = (now.getTime() - lastActiveDate.getTime()) / (1000 * 60)

  if (diffMinutes < 1) return 'À l\'instant'
  if (diffMinutes < 60) return `${Math.floor(diffMinutes)}min`
  if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)}h`
  return `${Math.floor(diffMinutes / 1440)}j`
}

const getPlayerPercentage = (room: Room) => {
  return (room.currentPlayers / room.maxPlayers) * 100
}

const getStatusLabel = (status: string) => {
  const labels: { [key: string]: string } = {
    waiting: 'En attente',
    active: 'En cours',
    finished: 'Terminée'
  }
  return labels[status] || status
}

const getStatusSeverity = (status: string) => {
  const severities: { [key: string]: string } = {
    waiting: 'warning',
    active: 'success',
    finished: 'info'
  }
  return severities[status] || 'secondary'
}

const getStatusIcon = (status: string) => {
  const icons: { [key: string]: string } = {
    waiting: 'pi-clock',
    active: 'pi-play',
    finished: 'pi-check'
  }
  return icons[status] || 'pi-circle'
}

// Chargement des données
const loadData = async () => {
  try {
    console.log('Loading admin data...')
    loadingUsers.value = true
    loadingRooms.value = true

    const headers = { Authorization: `Bearer ${auth.token}` }

    // Chargement des données depuis l'API
    const [usersRes, roomsRes] = await Promise.all([
      axios.get('http://localhost:8000/api/admin/users', { headers }),
      axios.get('http://localhost:8000/api/admin/rooms', { headers })
    ])

    // Chargement des jeux séparément car l'API semble avoir des problèmes
    let gamesRes
    try {
      gamesRes = await axios.get('http://localhost:8000/api/games', { headers })
    } catch (gameError) {
      console.warn('Erreur chargement jeux:', gameError)
      gamesRes = { data: [] } // Fallback vers un tableau vide
    }

    users.value = usersRes.data
    rooms.value = roomsRes.data
    games.value = gamesRes.data || []

    console.log('Loaded users:', users.value)
    console.log('Loaded rooms:', rooms.value)
    console.log('Loaded games:', games.value)

    // Calcul des statistiques
    calculateStats()
    console.log('Data loaded successfully')
  } catch (error: any) {
    console.error('Erreur chargement:', error)

    let errorMessage = 'Impossible de charger les données'
    if (error.response?.status === 401) {
      errorMessage = 'Session expirée. Veuillez vous reconnecter.'
      router.push('/auth')
    } else if (error.response?.status === 403) {
      errorMessage = 'Accès non autorisé'
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: errorMessage
    })
  } finally {
    loadingUsers.value = false
    loadingRooms.value = false
  }
}

const calculateStats = () => {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())

  stats.value = {
    totalUsers: users.value.length,
    totalRooms: rooms.value.length,
    activeUsers: users.value.filter((u) => isUserOnline(u.lastActive)).length,
    activeRooms: rooms.value.filter((r: { status: string }) => r.status === 'active').length,
    newUsersToday: users.value.filter((u: { createdAt: string | number | Date }) => new Date(u.createdAt) >= today).length,
    newRoomsToday: rooms.value.filter((r: { createdAt: string | number | Date }) => new Date(r.createdAt) >= today).length
  }
}

// === GESTION DES UTILISATEURS ===

const openUserDialog = (user: User | null = null) => {
  editingUser.value = user
  userFormErrors.value = { username: '', email: '' }

  if (user) {
    userForm.value = {
      username: user.username,
      email: user.email,
      password: '',
      roles: user.roles,
      avatar: user.avatar || '/img/avatar/1.png'
    }
  } else {
    userForm.value = {
      username: '',
      email: '',
      password: '',
      roles: ['ROLE_USER'],
      avatar: '/img/avatar/1.png'
    }
  }
  userDialogVisible.value = true
}

const validateUserForm = () => {
  userFormErrors.value = { username: '', email: '' }
  let isValid = true

  if (!userForm.value.username.trim()) {
    userFormErrors.value.username = 'Le nom d\'utilisateur est requis'
    isValid = false
  } else if (userForm.value.username.length < 3) {
    userFormErrors.value.username = 'Le nom d\'utilisateur doit faire au moins 3 caractères'
    isValid = false
  }

  if (!userForm.value.email.trim()) {
    userFormErrors.value.email = 'L\'email est requis'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(userForm.value.email)) {
    userFormErrors.value.email = 'L\'email n\'est pas valide'
    isValid = false
  }

  return isValid
}

const saveUser = async () => {
  if (!validateUserForm()) return

  try {
    savingUser.value = true

    const userData: any = {
      username: userForm.value.username.trim(),
      email: userForm.value.email.trim(),
      roles: userForm.value.roles,
      avatar: userForm.value.avatar
    }

    // Ajouter le mot de passe seulement pour la création
    if (!editingUser.value && userForm.value.password) {
      userData.password = userForm.value.password
    }

    const headers = { Authorization: `Bearer ${auth.token}` }

    if (editingUser.value) {
      // Modification d'un utilisateur existant
      const response = await axios.put(
        `http://localhost:8000/api/admin/users/${editingUser.value.id}`,
        userData,
        { headers }
      )

      // Mettre à jour l'utilisateur dans la liste locale
      const userIndex = users.value.findIndex(u => u.id === editingUser.value!.id)
      if (userIndex !== -1) {
        users.value[userIndex] = { ...users.value[userIndex], ...response.data }
      }

      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Utilisateur modifié avec succès'
      })
    } else {
      // Création d'un nouvel utilisateur
      const response = await axios.post(
        'http://localhost:8000/api/admin/users',
        userData,
        { headers }
      )

      // Ajouter le nouvel utilisateur à la liste locale
      users.value.push(response.data)

      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Utilisateur créé avec succès'
      })
    }

    userDialogVisible.value = false
    // Recalculer les statistiques
    calculateStats()
  } catch (error: any) {
    console.error('Erreur sauvegarde utilisateur:', error)

    let errorMessage = 'Erreur lors de la sauvegarde'

    if (error.response?.status === 422 && error.response.data?.violations) {
      // Erreurs de validation Symfony
      const violations = error.response.data.violations
      violations.forEach((violation: any) => {
        if (violation.propertyPath === 'username') {
          userFormErrors.value.username = violation.message
        } else if (violation.propertyPath === 'email') {
          userFormErrors.value.email = violation.message
        }
      })
      return // Ne pas fermer le dialog en cas d'erreur de validation
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 409) {
      errorMessage = 'Un utilisateur avec ce nom ou email existe déjà'
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: errorMessage
    })
  } finally {
    savingUser.value = false
  }
}

const confirmDeleteUser = (user: User) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer l'utilisateur "${user.username}" ? Cette action est irréversible.`,
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: () => deleteUser(user)
  })
}

const deleteUser = async (user: User) => {
  try {
    const headers = { Authorization: `Bearer ${auth.token}` }

    await axios.delete(`http://localhost:8000/api/admin/users/${user.id}`, { headers })

    // Supprimer l'utilisateur de la liste locale
    users.value = users.value.filter(u => u.id !== user.id)

    // Recalculer les statistiques
    calculateStats()

    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Utilisateur supprimé avec succès'
    })
  } catch (error: any) {
    console.error('Erreur suppression utilisateur:', error)

    let errorMessage = 'Erreur lors de la suppression'
    if (error.response?.status === 409) {
      errorMessage = 'Impossible de supprimer cet utilisateur (données liées)'
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: errorMessage
    })
  }
}

const viewUser = (user: User) => {
  router.push(`/admin/users/${user.id}`)
}

// === GESTION DES ROOMS ===

const openRoomDialog = (room: Room | null = null) => {
  editingRoom.value = room
  if (room) {
    roomForm.value = {
      name: room.name,
      gameId: room.game.id,
      maxPlayers: room.maxPlayers,
    }
  } else {
    roomForm.value = {
      name: '',
      gameId: null,
      maxPlayers: 4,
    }
  }
  roomDialogVisible.value = true
}

const saveRoom = async () => {
  try {
    savingRoom.value = true

    const roomData = {
      name: roomForm.value.name.trim(),
      gameId: roomForm.value.gameId,
      maxPlayers: roomForm.value.maxPlayers,
    }

    const headers = { Authorization: `Bearer ${auth.token}` }

    if (editingRoom.value) {
      // Modification d'une room existante
      const response = await axios.put(
        `http://localhost:8000/api/admin/rooms/${editingRoom.value.id}`,
        roomData,
        { headers }
      )

      // Mettre à jour la room dans la liste locale
      const roomIndex = rooms.value.findIndex(r => r.id === editingRoom.value!.id)
      if (roomIndex !== -1) {
        rooms.value[roomIndex] = { ...rooms.value[roomIndex], ...response.data }
      }

      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Room modifiée avec succès'
      })
    } else {
      // Création d'une nouvelle room
      const response = await axios.post(
        'http://localhost:8000/api/admin/rooms',
        roomData,
        { headers }
      )

      // Ajouter la nouvelle room à la liste locale
      rooms.value.push(response.data)

      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Room créée avec succès'
      })
    }

    roomDialogVisible.value = false
    // Recalculer les statistiques
    calculateStats()
  } catch (error: any) {
    console.error('Erreur sauvegarde room:', error)

    let errorMessage = 'Erreur lors de la sauvegarde'

    if (error.response?.status === 422 && error.response.data?.violations) {
      // Erreurs de validation Symfony
      const violations = error.response.data.violations
      errorMessage = violations.map((v: any) => v.message).join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 404) {
      errorMessage = 'Jeu non trouvé'
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: errorMessage
    })
  } finally {
    savingRoom.value = false
  }
}

const confirmDeleteRoom = (room: Room) => {
  confirm.require({
    message: `Êtes-vous sûr de vouloir supprimer la room "${room.name}" ? Cette action est irréversible.`,
    header: 'Confirmation de suppression',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: () => deleteRoom(room)
  })
}

const deleteRoom = async (room: Room) => {
  try {
    const headers = { Authorization: `Bearer ${auth.token}` }

    await axios.delete(`http://localhost:8000/api/admin/rooms/${room.id}`, { headers })

    // Supprimer la room de la liste locale
    rooms.value = rooms.value.filter(r => r.id !== room.id)

    // Recalculer les statistiques
    calculateStats()

    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Room supprimée avec succès'
    })
  } catch (error: any) {
    console.error('Erreur suppression room:', error)

    let errorMessage = 'Erreur lors de la suppression'
    if (error.response?.status === 409) {
      errorMessage = 'Impossible de supprimer cette room (partie en cours)'
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: errorMessage
    })
  }
}

const viewRoom = (room: Room) => {
  router.push(`/rooms/${room.id}`)
}

// === FONCTIONS D'EXPORT ===

const exportUsers = () => {
  const csvContent = "data:text/csv;charset=utf-8,"
    + "ID,Nom d'utilisateur,Email,Rôles,Date d'inscription,Dernière activité\n"
    + users.value.map(user =>
        `${user.id},"${user.username}","${user.email}","${user.roles.join(', ')}","${formatDate(user.createdAt)}","${getLastActiveText(user.lastActive)}"`
      ).join("\n")

  const encodedUri = encodeURI(csvContent)
  const link = document.createElement("a")
  link.setAttribute("href", encodedUri)
  link.setAttribute("download", `users_${new Date().toISOString().split('T')[0]}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)

  toast.add({
    severity: 'success',
    summary: 'Export réussi',
    detail: 'Les utilisateurs ont été exportés en CSV'
  })
}

const exportRooms = () => {
  const csvContent = "data:text/csv;charset=utf-8,"
    + "ID,Nom,Jeu,Créateur,Joueurs,Statut,Date de création\n"
    + rooms.value.map((room: { id: any; name: any; game: { name: any }; owner: { username: any }; currentPlayers: any; maxPlayers: any; status: string; createdAt: string }) =>
        `${room.id},"${room.name}","${room.game.name}","${room.owner.username}","${room.currentPlayers}/${room.maxPlayers}","${getStatusLabel(room.status)}","${formatDate(room.createdAt)}"`
      ).join("\n")

  const encodedUri = encodeURI(csvContent)
  const link = document.createElement("a")
  link.setAttribute("href", encodedUri)
  link.setAttribute("download", `rooms_${new Date().toISOString().split('T')[0]}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)

  toast.add({
    severity: 'success',
    summary: 'Export réussi',
    detail: 'Les rooms ont été exportées en CSV'
  })
}

// Gestion upload avatar
const onAvatarSelect = (event: any) => {
  const file = event.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      userForm.value.avatar = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}
</script>

<style scoped>
.custom-datatable ::v-deep(.p-datatable-tbody > tr) {
  background: rgba(51, 65, 85, 0.3);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.custom-datatable ::v-deep(.p-datatable-tbody > tr:nth-child(even)) {
  background: rgba(71, 85, 105, 0.3);
}

.custom-datatable ::v-deep(.p-datatable-tbody > tr:hover) {
  background: rgba(59, 130, 246, 0.2);
}

.custom-datatable ::v-deep(.p-datatable-header) {
  background: rgba(30, 41, 59, 0.8);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.custom-datatable ::v-deep(.p-column-header-content) {
  color: white;
  font-weight: 600;
}

.custom-datatable ::v-deep(.p-paginator) {
  background: rgba(30, 41, 59, 0.5);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
