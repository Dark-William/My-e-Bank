<template>
  <div
    class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300"
  >
    <AppHeader />

    <div class="max-w-6xl mx-auto px-4 py-8">
      <div v-if="initialLoading" class="space-y-6">
        <div
          class="h-36 bg-slate-200 dark:bg-slate-800/50 animate-pulse rounded-2xl"
        ></div>
        <div
          class="h-64 bg-slate-200 dark:bg-slate-800/50 animate-pulse rounded-2xl"
        ></div>
      </div>

      <template v-else>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Welcome Card -->
          <div
            class="md:col-span-2 bg-linear-to-br from-violet-600/10 to-cyan-600/10 dark:from-violet-600/20 dark:to-cyan-600/20 border border-violet-500/20 rounded-2xl p-6"
          >
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-1">
              Bonjour,
            </p>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
              {{ authStore.user?.name ?? "—" }} 👋
            </h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
              {{ authStore.user?.email }}
            </p>
            <div class="mt-4 flex gap-3">
              <span
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-emerald-600 dark:text-emerald-400 text-xs font-medium"
              >
                <span
                  class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"
                ></span>
                Compte actif
              </span>
            </div>
          </div>

          <!-- Balance Card -->
          <BalanceCard />
        </div>

        <!-- Tabs -->
        <div
          class="flex gap-1 p-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl mb-6 w-fit"
        >
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2',
              activeTab === tab.id
                ? 'bg-linear-to-r from-violet-600 to-cyan-600 text-white shadow-lg'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white',
            ]"
          >
            <component :is="tab.icon" :size="18" />
            {{ tab.label }}
          </button>
        </div>

        <Transition name="slide-fade" mode="out-in">
          <div
            v-if="activeTab === 'operations'"
            key="op"
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
          >
            <OperationForm type="credit" />
            <OperationForm type="debit" />
          </div>

          <TransactionHistory v-else-if="activeTab === 'history'" key="hist" />
        </Transition>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useAuthStore } from "../core/stores/auth.store";
import { useAccountStore } from "../core/stores/account.store";
import AppHeader from "../components/layout/AppHeader.vue";
import BalanceCard from "../components/account/BalanceCard.vue";
import OperationForm from "../components/account/OperationForm.vue";
import TransactionHistory from "../components/account/TransactionHistory.vue";
import { CreditCard, History } from "lucide-vue-next";

const authStore = useAuthStore();
const accountStore = useAccountStore();
const initialLoading = ref(true);
const activeTab = ref<"operations" | "history">("operations");

const tabs = [
  { id: "operations" as const, label: "Opérations", icon: CreditCard },
  { id: "history" as const, label: "Historique", icon: History },
];

onMounted(async () => {
  const tasks: Promise<void>[] = [];
  if (!authStore.user) tasks.push(authStore.fetchUser());
  tasks.push(accountStore.fetchAccount());
  await Promise.allSettled(tasks);
  initialLoading.value = false;
});
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
