import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { AuthService } from "../services/auth.service";
import { UserService } from "../services/user.service";
import type { User, LoginPayload, RegisterPayload } from "../models";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(AuthService.getToken());
  const loading = ref(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed(() => !!token.value);

  async function register(payload: RegisterPayload): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      await AuthService.register(payload);
    } catch (e: unknown) {
      error.value = (e as Error).message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function login(payload: LoginPayload): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      const response = await AuthService.login(payload);
      token.value = response.token;
      user.value = response.user;
    } catch (e: unknown) {
      error.value = (e as Error).message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function logout(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      await AuthService.logout();
    } catch {
    } finally {
      token.value = null;
      user.value = null;
      loading.value = false;
    }
  }

  async function fetchUser(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
      user.value = await UserService.getUser();
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
    user,
    token,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    logout,
    fetchUser,
    clearError,
  };
});
