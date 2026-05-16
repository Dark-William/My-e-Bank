import api from '../interceptors/api'
import type { AuthResponse, LoginPayload, RegisterPayload } from '../models'

const TOKEN_KEY = 'auth_token'

export const AuthService = {
  async register(payload: RegisterPayload): Promise<void> {
    await api.post('/register', payload)
  },

  async login(payload: LoginPayload): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/login', payload)
    localStorage.setItem(TOKEN_KEY, data.token)
    return data
  },

  async logout(): Promise<void> {
    await api.get('/logout')
    localStorage.removeItem(TOKEN_KEY)
  },

  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY)
  },

  isAuthenticated(): boolean {
    return !!localStorage.getItem(TOKEN_KEY)
  },
}
