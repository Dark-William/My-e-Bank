export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Account {
  id: number;
  user_id: number;
  balance: number;
}

export interface Transaction {
  id: number;
  account_id: number;
  type: "credit" | "debit";
  amount: number;
  date: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface OperationPayload {
  amount: number;
}

export interface AuthResponse {
  token: string;
  user: User;
}

export interface AccountResponse {
  account: Account;
  transactions: Transaction[];
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
