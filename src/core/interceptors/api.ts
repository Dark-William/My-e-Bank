import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? "/api",
  timeout: 10_000,
  headers: { "Content-Type": "application/json" },
});
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;

    if (status === 401) {
      localStorage.removeItem("auth_token");
      globalThis.location.href = "/login";
    }
    const message =
      error.response?.data?.message ??
      error.response?.data?.error ??
      error.message ??
      "Une erreur inattendue est survenue.";

    return Promise.reject(new Error(message));
  },
);

export default api;
