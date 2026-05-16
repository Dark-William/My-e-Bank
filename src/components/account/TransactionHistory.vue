<template>
  <div
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm transition-colors duration-300"
  >
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white">
        Dernières opérations
      </h3>
      <span
        class="text-xs font-semibold text-slate-500 uppercase tracking-wider"
      >
        {{ accountStore.transactions.length }} au total
      </span>
    </div>

    <!-- Empty State -->
    <div
      v-if="!accountStore.loading && accountStore.transactions.length === 0"
      class="py-12 flex flex-col items-center justify-center text-center"
    >
      <div
        class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4"
      >
        <HistoryIcon class="w-8 h-8 text-slate-400" />
      </div>
      <p class="text-slate-500 dark:text-slate-400 font-medium">
        Aucune transaction trouvée
      </p>
      <p class="text-xs text-slate-400 mt-1">
        Vos opérations apparaîtront ici.
      </p>
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div
        v-for="tx in accountStore.transactions"
        :key="tx.id"
        class="group flex items-center justify-between p-3.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/40 dark:hover:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-xl transition-all duration-200"
      >
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-10 h-10 rounded-lg flex items-center justify-center transition-colors',
              tx.type === 'credit'
                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
            ]"
          >
            <ArrowDownLeftIcon v-if="tx.type === 'credit'" class="w-5 h-5" />
            <ArrowUpRightIcon v-else class="w-5 h-5" />
          </div>
          <div>
            <p
              class="font-bold text-slate-900 dark:text-white text-sm capitalize"
            >
              {{ tx.type === "credit" ? "Crédit" : "Débit" }}
            </p>
            <p
              class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5"
            >
              {{ formatDate(tx.date) }}
            </p>
          </div>
        </div>
        <div class="text-right">
          <p
            :class="[
              'font-bold text-sm',
              tx.type === 'credit'
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-slate-900 dark:text-white',
            ]"
          >
            {{ tx.type === "credit" ? "+" : "-" }}{{ tx.amount }} FCFA
          </p>
          <div
            class="flex justify-end opacity-0 group-hover:opacity-100 transition-opacity"
          >
            <span
              class="text-[9px] text-slate-400 uppercase font-bold tracking-tighter"
              >Confirmé</span
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAccountStore } from "../../core/stores/account.store";
import {
  HistoryIcon,
  ArrowDownLeftIcon,
  ArrowUpRightIcon,
} from "lucide-vue-next";

const accountStore = useAccountStore();

function formatDate(dateStr: string) {
  return new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(dateStr));
}
</script>
