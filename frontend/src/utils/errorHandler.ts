import { AxiosError } from 'axios'
import type { ApiErrorResponse } from '@/types/auth'

export interface ErrorHandlerOptions {
  showNotification?: boolean
  customMessage?: string
}

// Toast service singleton
let toastService: any = null

export const setToastService = (toast: any) => {
  toastService = toast
}

class ErrorHandler {
  handleError(error: unknown, options: ErrorHandlerOptions = {}): string {
    const {
      showNotification = true,
      customMessage
    } = options

    let message = customMessage || 'Ocorreu um erro inesperado'

    if (error instanceof AxiosError) {
      message = this.handleAxiosError(error)
    } else if (error instanceof Error) {
      message = error.message || message
    }

    if (showNotification) {
      this.showErrorNotification(message)
    }

    return message
  }

  private handleAxiosError(error: AxiosError<ApiErrorResponse>): string {
    const response = error.response

    if (!response) {
      return 'Erro de conexão. Verifique sua internet.'
    }

    switch (response.status) {
      case 400:
        return response.data?.message || this.extractErrorMessage(response.data) || 'Requisição inválida'
      
      case 401:
        return 'Não autorizado. Faça login novamente.'
      
      case 403:
        return 'Acesso negado. Você não tem permissão para esta ação.'
      
      case 404:
        return 'Recurso não encontrado'
      
      case 422:
        return this.handleValidationErrors(response.data)
      
      case 429:
        return 'Muitas tentativas. Tente novamente em alguns minutos.'
      
      case 500:
        return response.data?.message || 'Erro interno do servidor'
      
      default:
        return response.data?.message || `Erro ${response.status}`
    }
  }

  private handleValidationErrors(data: ApiErrorResponse): string {
    if (!data?.errors) {
      return data?.message || 'Dados inválidos'
    }

    // Pega o primeiro erro de validação
    const firstErrorKey = Object.keys(data.errors)[0]
    const firstError = data.errors[firstErrorKey]?.[0]

    return firstError || data.message || 'Dados inválidos'
  }

  private extractErrorMessage(data: ApiErrorResponse | undefined): string | null {
    if (!data) return null
    
    // Prioriza a mensagem da API
    if (data.message) {
      return data.message
    }
    
    return null
  }

  showErrorNotification(message: string): void {
    console.log("showErrorNotification chamado com:", message);
    if (toastService) {
      toastService.add({
        severity: 'error',
        summary: 'Erro',
        detail: message,
        life: 5000
      })
      console.log("toast.add foi chamado");
    } else {
      console.error("Toast service não está disponível");
    }
  }

  showSuccessNotification(message: string): void {
    if (toastService) {
      toastService.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: message,
        life: 3000
      })
    }
  }

  showWarningNotification(message: string): void {
    if (toastService) {
      toastService.add({
        severity: 'warn',
        summary: 'Atenção',
        detail: message,
        life: 4000
      })
    }
  }

  showInfoNotification(message: string): void {
    if (toastService) {
      toastService.add({
        severity: 'info',
        summary: 'Informação',
        detail: message,
        life: 3000
      })
    }
  }

  getValidationErrors(error: unknown): Record<string, string[]> | null {
    if (!(error instanceof AxiosError)) return null
    
    const response = error.response
    if (response?.status !== 422 || !response.data?.errors) return null
    
    return response.data.errors
  }
}

export const errorHandler = new ErrorHandler()
export default errorHandler