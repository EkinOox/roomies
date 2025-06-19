<template>
  <div class="flex justify-center mt-6 z-30">
    <div class="w-full max-w-7xl p-2 rounded-xl">
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
          <div class="flex items-center gap-6">
            <InputText placeholder="Search" type="text" class="w-32 sm:w-auto
                     rounded-lg
                     shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                     transition-all duration-300
                     focus:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]
                     focus:outline-none
                     px-3 py-2" />

            <div v-if="isAuthenticated">
              <Avatar
                @click="goToProfile"
                :image="userAvatar"
                shape="circle"
                class="cursor-pointer shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80] transition-shadow duration-300"
              />
              <i @click="logout" class="pi pi-sign-out text-xl text-white cursor-pointer"></i>
            </div>

            <div v-else @click="goToAuth" class="cursor-pointer p-2 rounded-full
                     shadow-[2px_2px_4px_#000000,-2px_-2px_4px_#ffffff80]
                     transition-shadow duration-300
                     hover:shadow-[inset_2px_2px_4px_#000000,inset_-2px_-2px_4px_#ffffff80]">
              <i class="pi pi-user text-xl text-white"></i>
            </div>
          </div>
        </template>
      </Menubar>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";

import Menubar from "primevue/menubar";
import InputText from "primevue/inputtext";
import Avatar from "primevue/avatar";
import Badge from "primevue/badge";
import 'primeicons/primeicons.css';

const router = useRouter();
const auth = useAuthStore();

const isAuthenticated = computed(() => auth.isAuthenticated);

const userAvatar = computed(() => auth.avatar ?? "/img/avatar/10.png");

watch(() => auth.avatar, (newVal) => {
  console.log("Avatar mis à jour :", newVal);
});

const items = ref([
  {
    label: "Accueil",
    icon: "pi pi-home",
    command: () => router.push("/")
  },
  {
    label: "Rooms",
    icon: "pi pi-users",
    badge: 2,
    items: [
      { label: "Toutes les rooms", icon: "pi pi-pencil" },
      { separator: true },
      { label: "2048", icon: "pi pi-bolt", command: () => router.push("/jeux") },
      { label: "Morpion", icon: "pi pi-server" },
    ],
  },
  {
    label: "Chat",
    icon: "pi pi-comments",
    command: () => router.push("/")
  },
]);

const goToAuth = () => router.push({ name: "auth" });
const goToProfile = () => router.push({ name: "profile" });
const logout = () => {
  auth.setToken(null);
  router.push({ name: 'auth' });
};
</script>
