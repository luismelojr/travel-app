import axios, { type AxiosInstance, type AxiosResponse } from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

class ApiService {
  private api: AxiosInstance

  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    this.setupInterceptors()
  }

  private setupInterceptors(): void {
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('travel_token')
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    this.api.interceptors.response.use(
      (response: AxiosResponse) => response,
      async (error) => {
        const originalRequest = error.config

        // Não tentar refresh para rotas de auth (login, register)
        const isAuthRoute = originalRequest.url?.includes('/auth/login') || 
                           originalRequest.url?.includes('/auth/register')

        if (error.response?.status === 401 && !originalRequest._retry && !isAuthRoute) {
          originalRequest._retry = true

          try {
            await this.refreshToken()
            const token = localStorage.getItem('travel_token')
            if (token) {
              originalRequest.headers.Authorization = `Bearer ${token}`
              return this.api(originalRequest)
            }
          } catch (refreshError) {
            this.clearAuthData()
            return Promise.reject(refreshError)
          }
        }

        return Promise.reject(error)
      }
    )
  }

  private async refreshToken(): Promise<void> {
    const refreshToken = localStorage.getItem('travel_refresh_token')
    if (!refreshToken) {
      throw new Error('No refresh token available')
    }

    const response = await axios.post(`${API_BASE_URL}/auth/refresh`, {}, {
      headers: {
        'Authorization': `Bearer ${refreshToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      const { token } = response.data.data
      localStorage.setItem('travel_token', token)
    }
  }

  private clearAuthData(): void {
    localStorage.removeItem('travel_token')
    localStorage.removeItem('travel_refresh_token')
    localStorage.removeItem('travel_user')
  }

  public get instance(): AxiosInstance {
    return this.api
  }
}

export const apiService = new ApiService()
export default apiService.instance