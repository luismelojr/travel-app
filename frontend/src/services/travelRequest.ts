import apiService from './api'
import type { 
  TravelRequest, 
  TravelRequestFilters, 
  TravelRequestListResponse,
  CreateTravelRequestData
} from '@/types/travelRequest'

export interface TravelRequestStats {
  total: number
  pending: number
  approved: number
  cancelled: number
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

class TravelRequestService {
  async getStats(): Promise<TravelRequestStats> {
    try {
      const response = await apiService.get<ApiResponse<TravelRequestStats>>('/travel-requests/stats')
      
      if (response.data.success) {
        return response.data.data
      }
      
      throw new Error(response.data.message || 'Erro ao buscar estatísticas')
    } catch (error: any) {
      console.error('Erro ao buscar estatísticas:', error)
      throw error?.response?.data?.message || error?.message || 'Erro ao buscar estatísticas'
    }
  }

  async list(filters: TravelRequestFilters = {}, page: number = 1): Promise<TravelRequestListResponse> {
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        ...Object.fromEntries(
          Object.entries(filters).filter(([_, value]) => value != null && value !== '')
        )
      })

      const response = await apiService.get<TravelRequestListResponse>(
        `/travel-requests?${params.toString()}`
      )
      
      if (response.data.success) {
        return response.data
      }
      
      throw new Error(response.data.message || 'Erro ao buscar solicitações')
    } catch (error: any) {
      console.error('Erro ao buscar solicitações:', error)
      throw error?.response?.data?.message || error?.message || 'Erro ao buscar solicitações'
    }
  }

  async getById(id: number): Promise<TravelRequest> {
    try {
      const response = await apiService.get<ApiResponse<TravelRequest>>(`/travel-requests/${id}`)
      
      if (response.data.success) {
        return response.data.data
      }
      
      throw new Error(response.data.message || 'Erro ao buscar solicitação')
    } catch (error: any) {
      console.error('Erro ao buscar solicitação:', error)
      throw error?.response?.data?.message || error?.message || 'Erro ao buscar solicitação'
    }
  }

  async cancel(id: number): Promise<TravelRequest> {
    try {
      const response = await apiService.patch<ApiResponse<TravelRequest>>(`/travel-requests/${id}/cancel`)
      
      if (response.data.success) {
        return response.data.data
      }
      
      throw new Error(response.data.message || 'Erro ao cancelar solicitação')
    } catch (error: any) {
      console.error('Erro ao cancelar solicitação:', error)
      throw error?.response?.data?.message || error?.message || 'Erro ao cancelar solicitação'
    }
  }

  async create(data: CreateTravelRequestData): Promise<TravelRequest> {
    try {
      const response = await apiService.post<ApiResponse<TravelRequest>>('/travel-requests', data)
      
      if (response.data.success) {
        return response.data.data
      }
      
      throw new Error(response.data.message || 'Erro ao criar solicitação')
    } catch (error: any) {
      console.error('Erro ao criar solicitação:', error)
      throw error?.response?.data?.message || error?.message || 'Erro ao criar solicitação'
    }
  }

  async updateStatus(id: number, status: 'requested' | 'approved' | 'cancelled'): Promise<TravelRequest> {
    const response = await apiService.patch<ApiResponse<TravelRequest>>(`/travel-requests/${id}/status`, { status })
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Erro ao atualizar status')
  }
}

export const travelRequestService = new TravelRequestService()