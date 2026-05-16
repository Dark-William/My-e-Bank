import api from '../interceptors/api'
import type { AccountResponse, OperationPayload } from '../models'

export const AccountService = {
  async credit(payload: OperationPayload): Promise<AccountResponse> {
    const { data } = await api.post<AccountResponse>('/account/credit', payload)
    return data
  },

  async debit(payload: OperationPayload): Promise<AccountResponse> {
    const { data } = await api.post<AccountResponse>('/account/debit', payload)
    return data
  },

  async getAccount(): Promise<AccountResponse> {
    const { data } = await api.get<AccountResponse>('/account')
    return data
  },
}
