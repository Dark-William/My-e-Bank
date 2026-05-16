<template>
  <div
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 flex flex-col justify-between shadow-sm"
  >
    <div class="flex items-center justify-between mb-4">
      <span class="text-slate-500 dark:text-slate-400 text-sm font-medium"
        >Solde disponible</span
      >
      <WalletIcon class="w-5 h-5 text-violet-500 dark:text-violet-400" />
    </div>

    <div
      v-if="accountStore.loading && !accountStore.account"
      class="h-10 bg-slate-100 dark:bg-slate-800 animate-pulse rounded-lg"
    ></div>
    <div v-else>
      <div
        class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight"
      >
        {{ formattedBalance }} FCFA
      </div>
      <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
        Compte #{{ accountStore.account?.id ?? "—" }}
      </p>
    </div>

    <div class="mt-4 flex items-center gap-1.5 text-emerald-400 text-sm">
      <TrendingUpIcon class="w-4 h-4" />
      <span>Compte en bonne santé</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useAccountStore } from "../../core/stores/account.store";
import { WalletIcon, TrendingUpIcon } from "lucide-vue-next";

const accountStore = useAccountStore();

const formattedBalance = computed(() => {
  const balance = accountStore.account?.balance ?? 0;
  return new Intl.NumberFormat("fr-FR", { minimumFractionDigits: 0 }).format(
    balance,
  );
});
</script>
