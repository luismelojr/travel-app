import api from './api'
import type { User, AuthResponse, RegisterData, LoginData } from '@/types/auth'

class AuthService {
  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/register', data)
    
    if (response.data.success) {
      this.setAuthData(response.data.data)
    }
    
    return response.data
  }

  async login(data: LoginData): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', data)
    
    if (response.data.success) {
      this.setAuthData(response.data.data)
    }
    
    return response.data
  }

  async logout(): Promise<void> {
    try {
      await api.post('/auth/logout')
    } finally {
      this.clearAuthData()
    }
  }

  async getCurrentUser(): Promise<User> {
    const response = await api.get<{ success: boolean; data: User }>('/auth/me')
    return response.data.data
  }

  private setAuthData(authData: AuthResponse['data']): void {
    localStorage.setItem('travel_token', authData.token)
    localStorage.setItem('travel_user', JSON.stringify(authData.user))
  }

  private clearAuthData(): void {
    localStorage.removeItem('travel_token')
    localStorage.removeItem('travel_user')
  }

  getCurrentUserFromStorage(): User | null {
    const userStr = localStorage.getItem('travel_user')
    if (!userStr) return null
    
    try {
      return JSON.parse(userStr)
    } catch {
      return null
    }
  }

  getToken(): string | null {
    return localStorage.getItem('travel_token')
  }

  isAuthenticated(): boolean {
    return !!this.getToken()
  }
}

export const authService = new AuthService()
export default authService