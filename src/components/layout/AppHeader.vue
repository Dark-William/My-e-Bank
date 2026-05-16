<template>
  <header
    class="bg-white/80 dark:bg-slate-950/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-300"
  >
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div
          class="w-8 h-8 bg-linear-to-br from-violet-600 to-cyan-600 rounded-lg flex items-center justify-center shadow-sm"
        >
          <BanknoteIcon class="w-5 h-5 text-white" />
        </div>
        <span
          class="text-lg font-bold text-slate-900 dark:text-white tracking-tight"
          >VaultBank</span
        >
      </div>

      <div class="flex items-center gap-2 sm:gap-4">
        <div
          class="hidden sm:flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
        >
          <UserCircleIcon
            class="w-5 h-5 text-violet-600 dark:text-violet-400"
          />
          <span>{{ authStore.user?.name ?? "Utilisateur" }}</span>
        </div>

        <button
          @click="handleLogout"
          :disabled="authStore.loading"
          class="flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-rose-50 dark:bg-slate-900 dark:hover:bg-rose-500/10 border border-slate-200 dark:border-slate-800 text-slate-700 hover:text-rose-600 dark:text-slate-300 dark:hover:text-rose-400 rounded-xl text-sm font-semibold transition-all duration-200 disabled:opacity-50"
        >
          <LogOutIcon class="w-4 h-4" />
          <span class="hidden sm:block">Déconnexion</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { useAuthStore } from "../../core/stores/auth.store";
import { BanknoteIcon, UserCircleIcon, LogOutIcon } from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();

async function handleLogout() {
  await authStore.logout();
  router.push("/login");
}
</script>
