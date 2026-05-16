<template>
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center p-4 transition-colors duration-300"
  >
    <!-- Logo -->
    <div class="flex items-center gap-3 mb-8">
      <div
        class="w-12 h-12 bg-linear-to-br from-violet-600 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-500/20"
      >
        <BanknoteIcon class="w-7 h-7 text-white" />
      </div>
      <h1
        class="text-3xl font-black text-slate-900 dark:text-white tracking-tight"
      >
        VaultBank
      </h1>
    </div>

    <!-- Login Card -->
    <div
      class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-xl relative overflow-hidden transition-colors duration-300"
    >
      <div
        class="absolute top-0 left-0 w-full h-1 bg-linear-to-r from-violet-600 to-cyan-600"
      ></div>

      <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
          Connexion
        </h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
          Accédez à votre espace bancaire sécurisé.
        </p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <!-- Error Alert -->
        <div
          v-if="authStore.error"
          class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl flex items-start gap-2"
        >
          <AlertCircleIcon
            class="w-4 h-4 text-rose-600 dark:text-rose-400 mt-0.5 shrink-0"
          />
          <p class="text-rose-600 dark:text-rose-400 text-sm">
            {{ authStore.error }}
          </p>
        </div>

        <div class="space-y-4">
          <div>
            <label
              class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5 ml-1"
            >
              Email
            </label>
            <div class="relative group">
              <MailIcon
                class="absolute left-4 top-3.5 w-5 h-5 text-slate-400 group-focus-within:text-violet-500 transition-colors"
              />
              <input
                v-model="email"
                type="email"
                placeholder="nom@exemple.com"
                required
                class="pl-12"
              />
            </div>
          </div>

          <div>
            <label
              class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5 ml-1"
            >
              Mot de passe
            </label>
            <div class="relative group">
              <LockIcon
                class="absolute left-4 top-3.5 w-5 h-5 text-slate-400 group-focus-within:text-violet-500 transition-colors"
              />
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                class="pl-12 pr-12"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-3.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
              >
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeOffIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-linear-to-r from-violet-600 to-cyan-600 hover:from-violet-500 hover:to-cyan-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-violet-500/20 transition-all duration-300 flex items-center justify-center gap-2 hover:-translate-y-px active:translate-y-0 disabled:opacity-50"
        >
          <LoaderIcon v-if="authStore.loading" class="w-5 h-5 animate-spin" />
          <span>{{ authStore.loading ? "Connexion..." : "Se connecter" }}</span>
        </button>
      </form>

      <div
        class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-center"
      >
        <p class="text-slate-500 dark:text-slate-400 text-sm">
          Pas encore de compte ?
          <router-link
            to="/register"
            class="text-violet-600 dark:text-violet-400 font-bold hover:underline ml-1"
          >
            Créer un compte
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../core/stores/auth.store";
import {
  MailIcon,
  LockIcon,
  EyeIcon,
  EyeOffIcon,
  BanknoteIcon,
  AlertCircleIcon,
  LoaderIcon,
} from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();

const email = ref("");
const password = ref("");
const showPassword = ref(false);

async function handleLogin() {
  try {
    await authStore.login({
      email: email.value,
      password: password.value,
    });
    router.push("/");
  } catch (e) {
    // Error is handled by authStore.error
  }
}
</script>
```
