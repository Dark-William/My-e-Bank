import { defineStore } from "pinia";
import { ref } from "vue";
import { AccountService } from "../services/account.service";
import type { Account, Transaction, OperationPayload } from "../models";

export const useAccountStore = defineStore("account", () => {
  const account = ref<Account | null>(null);
  const transactions = ref<Transaction[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchAccount(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      const data = await AccountService.getAccount();
      account.value = data.account;
      transactions.value = data.transactions ?? [];
    } catch (e: unknown) {
      error.value = (e as Error).message;
    } finally {
      loading.value = false;
    }
  }

  async function credit(payload: OperationPayload): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      const data = await AccountService.credit(payload);
      account.value = data.account;
      if (data.transactions) transactions.value = data.transactions;
    } catch (e: unknown) {
      error.value = (e as Error).message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function debit(payload: OperationPayload): Promise<void> {
    if (account.value && payload.amount > account.value.balance) {
      error.value = "Solde insuffisant pour effectuer ce débit.";
      throw new Error(error.value);
    }
    loading.value = true;
    error.value = null;
    try {
      const data = await AccountService.debit(payload);
      account.value = data.account;
      if (data.transactions) transactions.value = data.transactions;
    } catch (e: unknown) {
      error.value = (e as Error).message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  function clearError(): void {
    error.value = null;
  }

  return {
    account,
    transactions,
    loading,
    error,
    fetchAccount,
    credit,
    debit,
    clearError,
  };
});
