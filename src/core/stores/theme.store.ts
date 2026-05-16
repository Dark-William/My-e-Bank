import { defineStore } from "pinia";
import { ref, watch } from "vue";

export type Theme = "light" | "dark";

export const useThemeStore = defineStore("theme", () => {
  const theme = ref<Theme>(
    (localStorage.getItem("theme") as Theme) ||
      (globalThis.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light"),
  );

  function toggleTheme() {
    theme.value = theme.value === "light" ? "dark" : "light";
  }

  function setTheme(newTheme: Theme) {
    theme.value = newTheme;
  }

  watch(
    theme,
    (newTheme) => {
      localStorage.setItem("theme", newTheme);
      if (newTheme === "dark") {
        document.documentElement.classList.add("dark");
      } else {
        document.documentElement.classList.remove("dark");
      }
    },
    { immediate: true },
  );

  return {
    theme,
    toggleTheme,
    setTheme,
  };
});
