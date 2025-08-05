export interface TravelRequestStatus {
  value: 'requested' | 'approved' | 'cancelled'
  label: string
}

export interface TravelRequestUser {
  id: number
  name: string
  email: string
  role: string
  role_label: string
}

export interface TravelRequest {
  id: number
  requester_name: string
  destination: string
  departure_date: string
  return_date: string
  status: TravelRequestStatus
  notes?: string | null
  duration_days: number
  user?: TravelRequestUser
  created_at: string
  updated_at: string
}

export interface TravelRequestFilters {
  status?: string
  destination?: string
  date_from?: Date | string
  date_to?: Date | string
  request_date_from?: Date | string
  request_date_to?: Date | string
}

export interface PaginationData {
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number
  to: number
}

export interface CreateTravelRequestData {
  requester_name: string
  destination: string
  departure_date: string
  return_date: string
  notes?: string
}

export interface TravelRequestListResponse {
  data: TravelRequest[]
  meta: {
    current_page: number
    per_page: number
    total: number
    last_page: number
    from: number
    to: number
    path: string
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
  success: boolean
  message: string
}