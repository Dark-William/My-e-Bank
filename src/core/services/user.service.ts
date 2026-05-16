import api from '../interceptors/api'
import type { User } from '../models'

export const UserService = {
  async getUser(): Promise<User> {
    const { data } = await api.get<User>('/user')
    return data
  },
}
