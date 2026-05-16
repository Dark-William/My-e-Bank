<template>
  <div
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm transition-colors duration-300"
  >
    <div class="flex items-center gap-3 mb-6">
      <div
        :class="[
          'w-10 h-10 rounded-xl flex items-center justify-center transition-colors',
          type === 'credit'
            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
            : 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
        ]"
      >
        <PlusCircleIcon v-if="type === 'credit'" class="w-6 h-6" />
        <MinusCircleIcon v-else class="w-6 h-6" />
      </div>
      <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
          {{ type === "credit" ? "Créditer" : "Débiter" }} le compte
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Entrez le montant de l'opération
        </p>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Success/Error Alerts -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="feedback"
          :class="[
            'p-3 rounded-xl border flex items-center gap-2 text-sm',
            feedback.type === 'success'
              ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
              : 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400',
          ]"
        >
          <CheckCircleIcon v-if="feedback.type === 'success'" class="w-4 h-4" />
          <AlertCircleIcon v-else class="w-4 h-4" />
          {{ feedback.message }}
        </div>
      </transition>

      <div>
        <label
          class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5 ml-1"
        >
          Montant (FCFA)
        </label>
        <div class="relative">
          <input
            v-model.number="amount"
            type="number"
            step="0.01"
            min="0.01"
            placeholder="0.00"
            required
            :disabled="accountStore.loading"
          />
        </div>
      </div>

      <button
        type="submit"
        :disabled="accountStore.loading || !amount || amount <= 0"
        :class="[
          'w-full py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 transition-all duration-200',
          type === 'credit'
            ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
            : 'bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-500/20',
          'disabled:opacity-50 disabled:cursor-not-allowed',
        ]"
      >
        <span
          v-if="accountStore.loading"
          class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"
        ></span>
        <template v-else>
          {{ type === "credit" ? "Déposer les fonds" : "Retirer les fonds" }}
        </template>
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useAccountStore } from "../../core/stores/account.store";
import {
  PlusCircleIcon,
  MinusCircleIcon,
  CheckCircleIcon,
  AlertCircleIcon,
} from "lucide-vue-next";

const props = defineProps<{
  type: "credit" | "debit";
}>();

const accountStore = useAccountStore();
const amount = ref<number | null>(null);
const feedback = ref<{ type: "success" | "error"; message: string } | null>(
  null,
);

async function handleSubmit() {
  if (!amount.value || amount.value <= 0) return;

  feedback.value = null;

  try {
    if (props.type === "credit") {
      await accountStore.credit({ amount: amount.value });
    } else {
      await accountStore.debit({ amount: amount.value });
    }

    feedback.value = {
      type: "success",
      message:
        props.type === "credit"
          ? `Virement de ${amount.value} FCFA reçu.`
          : `Retrait de ${amount.value} FCFA effectué.`,
    };
    amount.value = null;

    setTimeout(() => {
      if (feedback.value?.type === "success") {
        feedback.value = null;
      }
    }, 5000);
  } catch (e: any) {
    feedback.value = {
      type: "error",
      message: e.message || "Une erreur est survenue.",
    };
  }
}
</script>
