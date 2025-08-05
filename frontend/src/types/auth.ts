export interface User {
  id: number
  name: string
  email: string
  role: string
  role_label: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  success: boolean
  message: string
  data: {
    user: User
    token: string
    token_type: string
    expires_in: number
  }
}

export interface ApiErrorResponse {
  success: boolean
  message: string
  errors?: Record<string, string[]>
  error_code?: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface LoginData {
  email: string
  password: string
}