<template>
  <button
    @click="themeStore.toggleTheme"
    class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-all duration-200
           dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-400 dark:hover:text-slate-200
           flex items-center justify-center border border-slate-200 dark:border-slate-700"
    :title="themeStore.theme === 'light' ? 'Passer au mode sombre' : 'Passer au mode clair'"
  >
    <SunIcon v-if="themeStore.theme === 'dark'" class="w-5 h-5" />
    <MoonIcon v-else class="w-5 h-5" />
  </button>
</template>

<script setup lang="ts">
import { useThemeStore } from "../../core/stores/theme.store";
import { SunIcon, MoonIcon } from "lucide-vue-next";

const themeStore = useThemeStore();
</script>
