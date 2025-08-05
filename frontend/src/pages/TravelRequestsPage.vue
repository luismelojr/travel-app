<template>
  <AppLayout>
    <div class="travel-requests-container">
      <div class="page-header">
        <h1 class="page-title">{{ isAdmin ? 'Todas as Solicitações' : 'Minhas Solicitações' }}</h1>
        <Button 
          label="Nova Solicitação" 
          icon="pi pi-plus" 
          @click="createRequest"
        />
      </div>

      <!-- Filtros -->
      <Card class="filters-card">
        <template #content>
          <div class="filters-grid">
            <div class="filter-group">
              <label for="status-filter" class="filter-label">Status</label>
              <Dropdown 
                id="status-filter"
                v-model="filters.status" 
                :options="statusOptions" 
                optionLabel="label" 
                optionValue="value"
                placeholder="Todos os status"
                class="filter-dropdown"
                showClear
                @change="applyFilters"
              />
            </div>
            
            <div class="filter-group">
              <label for="destination-filter" class="filter-label">Destino</label>
              <InputText 
                id="destination-filter"
                v-model="filters.destination" 
                placeholder="Filtrar por destino"
                class="filter-input"
                @input="debouncedApplyFilters"
              />
            </div>
            
            <div class="filter-group">
              <label for="date-from-filter" class="filter-label">Data de partida (de)</label>
              <Calendar 
                id="date-from-filter"
                v-model="filters.date_from as Date" 
                dateFormat="dd/mm/yy"
                placeholder="dd/mm/aaaa"
                class="filter-calendar"
                showIcon
                @date-select="applyFilters"
                @clear="applyFilters"
              />
            </div>
            
            <div class="filter-group">
              <label for="date-to-filter" class="filter-label">Data de partida (até)</label>
              <Calendar 
                id="date-to-filter"
                v-model="filters.date_to as Date" 
                dateFormat="dd/mm/yy"
                placeholder="dd/mm/aaaa"
                class="filter-calendar"
                showIcon
                @date-select="applyFilters"
                @clear="applyFilters"
              />
            </div>
          </div>
          
          <div class="filters-actions">
            <Button 
              label="Limpar Filtros" 
              icon="pi pi-times" 
              outlined
              @click="clearFilters"
            />
          </div>
        </template>
      </Card>

      <!-- Tabela de Solicitações -->
      <Card class="requests-card">
        <template #content>
          <div v-if="loading" class="table-skeleton">
            <div class="skeleton-header">
              <Skeleton height="40px" borderRadius="6px" />
            </div>
            <div class="skeleton-rows">
              <div v-for="i in 5" :key="i" class="skeleton-row">
                <Skeleton height="60px" borderRadius="6px" />
              </div>
            </div>
          </div>

          <div v-else-if="travelRequests.length === 0" class="empty-state">
            <i class="pi pi-calendar empty-icon"></i>
            <h3 class="empty-title">
              {{ hasActiveFilters ? 'Nenhuma solicitação encontrada' : 'Nenhuma solicitação encontrada' }}
            </h3>
            <p class="empty-text">
              {{ hasActiveFilters 
                ? 'Tente ajustar os filtros para encontrar suas solicitações.' 
                : 'Você ainda não fez nenhuma solicitação de viagem.' 
              }}
            </p>
            <Button 
              v-if="!hasActiveFilters"
              label="Criar Primeira Solicitação" 
              icon="pi pi-plus"
              @click="createRequest"
            />
            <Button 
              v-else
              label="Limpar Filtros" 
              icon="pi pi-times"
              outlined
              @click="clearFilters"
            />
          </div>

          <div v-else class="requests-table-container">
            <DataTable 
              :value="travelRequests" 
              :paginator="pagination.total > pagination.per_page"
              :rows="pagination.per_page"
              :totalRecords="pagination.total"
              :lazy="pagination.total > pagination.per_page"
              @page="onPageChange"
              paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
              :rowsPerPageOptions="[10, 20, 50]"
              currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} registros"
              responsiveLayout="scroll"
              class="requests-table"
              :rowClass="getRowClass"
            >
              <Column field="id" header="ID" :sortable="false" class="id-column">
                <template #body="{ data }">
                  <span class="request-id">#{{ data.id }}</span>
                </template>
              </Column>

              <Column field="destination" header="Destino" :sortable="false">
                <template #body="{ data }">
                  <div class="destination-cell">
                    <i class="pi pi-map-marker destination-icon"></i>
                    <span class="destination-text">{{ data.destination }}</span>
                  </div>
                </template>
              </Column>

              <Column field="departure_date" header="Período" :sortable="false">
                <template #body="{ data }">
                  <div class="dates-cell">
                    <div class="date-range">
                      <span class="date-from">{{ formatDate(data.departure_date) }}</span>
                      <i class="pi pi-arrow-right date-separator"></i>
                      <span class="date-to">{{ formatDate(data.return_date) }}</span>
                    </div>
                    <div class="duration">{{ data.duration_days }} dias</div>
                  </div>
                </template>
              </Column>

              <Column field="status" header="Status" :sortable="false">
                <template #body="{ data }">
                  <Tag 
                    :value="data.status.label" 
                    :severity="getStatusSeverity(data.status.value)"
                    :icon="getStatusIcon(data.status.value)"
                  />
                </template>
              </Column>

              <Column field="created_at" header="Solicitado em" :sortable="false">
                <template #body="{ data }">
                  <span class="created-date">{{ formatDateTime(data.created_at) }}</span>
                </template>
              </Column>

              <Column header="Ações" :exportable="false" class="actions-column">
                <template #body="{ data }">
                  <div class="actions-cell">
                    <Button 
                      icon="pi pi-eye" 
                      rounded 
                      outlined 
                      size="small"
                      v-tooltip.top="'Visualizar'"
                      @click="viewRequest(data)"
                    />
                    <Button 
                      v-if="isAdmin && canUpdateStatus(data)"
                      icon="pi pi-pencil" 
                      rounded 
                      outlined 
                      severity="secondary"
                      size="small"
                      v-tooltip.top="'Atualizar Status'"
                      @click="confirmUpdateStatus(data)"
                    />
                    <Button 
                      v-if="canCancelRequest(data)"
                      icon="pi pi-times" 
                      rounded 
                      outlined 
                      severity="danger"
                      size="small"
                      v-tooltip.top="'Cancelar'"
                      @click="confirmCancelRequest(data)"
                    />
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </template>
      </Card>
    </div>

    <!-- Dialog de Cancelamento -->
    <Dialog 
      v-model:visible="showCancelDialog" 
      :style="{ width: '450px' }" 
      header="Confirmar Cancelamento" 
      modal
    >
      <div class="cancel-dialog-content">
        <i class="pi pi-exclamation-triangle cancel-warning-icon"></i>
        <div class="cancel-message">
          <h4>Tem certeza que deseja cancelar esta solicitação?</h4>
          <p>Esta ação não poderá ser desfeita.</p>
          <div v-if="requestToCancel" class="request-details">
            <strong>Destino:</strong> {{ requestToCancel.destination }}<br>
            <strong>Período:</strong> {{ formatDate(requestToCancel.departure_date) }} - {{ formatDate(requestToCancel.return_date) }}
          </div>
        </div>
      </div>
      <template #footer>
        <Button 
          label="Manter Solicitação" 
          outlined 
          @click="showCancelDialog = false" 
        />
        <Button 
          label="Cancelar Solicitação" 
          severity="danger" 
          :loading="cancelling"
          @click="cancelRequest" 
        />
      </template>
    </Dialog>

    <!-- Status Update Modal -->
    <StatusUpdateModal
      v-model:visible="showStatusDialog"
      :travel-request="requestToUpdate"
      :loading="updatingStatus"
      @update-status="updateRequestStatus"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Calendar from 'primevue/calendar'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Skeleton from 'primevue/skeleton'
import StatusUpdateModal from '@/components/StatusUpdateModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { travelRequestService } from '@/services/travelRequest'
import { errorHandler } from '@/utils/errorHandler'
import { authService } from '@/services/auth'
import type { 
  TravelRequest, 
  TravelRequestFilters, 
  PaginationData 
} from '@/types/travelRequest'

const router = useRouter()

// Estados
const loading = ref(true)
const travelRequests = ref<TravelRequest[]>([])
const pagination = ref<PaginationData>({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
})

// Filtros
const filters = ref<TravelRequestFilters>({})
const statusOptions = [
  { label: 'Solicitado', value: 'requested' },
  { label: 'Aprovado', value: 'approved' },
  { label: 'Cancelado', value: 'cancelled' }
]

// Dialog de cancelamento
const showCancelDialog = ref(false)
const requestToCancel = ref<TravelRequest | null>(null)
const cancelling = ref(false)

// Dialog de atualização de status
const showStatusDialog = ref(false)
const requestToUpdate = ref<TravelRequest | null>(null)
const updatingStatus = ref(false)

// Computed
const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(value => value != null && value !== '')
})

const isAdmin = computed(() => {
  const user = authService.getCurrentUserFromStorage()
  return user?.role === 'admin'
})

// Debounce para filtros de texto
let debounceTimeout: number
const debouncedApplyFilters = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

// Métodos
const loadRequests = async (page: number = 1) => {
  try {
    loading.value = true
    
    // Converter datas para formato ISO
    const filtersToSend = { ...filters.value }
    if (filtersToSend.date_from) {
      filtersToSend.date_from = formatDateForAPI(filtersToSend.date_from)
    }
    if (filtersToSend.date_to) {
      filtersToSend.date_to = formatDateForAPI(filtersToSend.date_to)
    }
    
    const response = await travelRequestService.list(filtersToSend, page)
    
    // A resposta tem a estrutura: { data: [...], meta: { current_page, per_page, total, etc. }, success, message }
    travelRequests.value = response.data || []
    pagination.value = {
      current_page: response.meta?.current_page || page,
      per_page: response.meta?.per_page || 15,
      total: response.meta?.total || 0,
      last_page: response.meta?.last_page || 1,
      from: response.meta?.from || 0,
      to: response.meta?.to || 0
    }
  } catch (error: any) {
    errorHandler.handleError(error, { customMessage: 'Erro ao carregar solicitações' })
    travelRequests.value = []
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.value.current_page = 1
  loadRequests(1)
}

const clearFilters = () => {
  filters.value = {}
  applyFilters()
}

const onPageChange = (event: any) => {
  pagination.value.current_page = event.page + 1
  pagination.value.per_page = event.rows
  loadRequests(pagination.value.current_page)
}

const createRequest = () => {
  router.push({ name: 'create-travel-request' })
}

const viewRequest = (request: TravelRequest) => {
  router.push({ name: 'travel-request-detail', params: { id: request.id.toString() } })
}

const canCancelRequest = (request: TravelRequest): boolean => {
  return request.status.value === 'requested'
}

const canUpdateStatus = (request: TravelRequest): boolean => {
  // Admins podem atualizar status apenas se não estiver aprovado
  return request.status.value !== 'approved'
}

const confirmCancelRequest = (request: TravelRequest) => {
  requestToCancel.value = request
  showCancelDialog.value = true
}

const confirmUpdateStatus = (request: TravelRequest) => {
  requestToUpdate.value = request
  showStatusDialog.value = true
}

const cancelRequest = async () => {
  if (!requestToCancel.value) return
  
  try {
    cancelling.value = true
    await travelRequestService.cancel(requestToCancel.value.id)
    
    errorHandler.showSuccessNotification('Solicitação cancelada com sucesso')
    showCancelDialog.value = false
    requestToCancel.value = null
    
    // Recarregar a lista
    await loadRequests(pagination.value.current_page)
  } catch (error: any) {
    errorHandler.handleError(error, { customMessage: 'Erro ao cancelar solicitação' })
  } finally {
    cancelling.value = false
  }
}

const updateRequestStatus = async (newStatus: 'requested' | 'approved' | 'cancelled') => {
  if (!requestToUpdate.value) return
  
  try {
    updatingStatus.value = true
    await travelRequestService.updateStatus(requestToUpdate.value.id, newStatus)
    
    errorHandler.showSuccessNotification('Status atualizado com sucesso')
    showStatusDialog.value = false
    requestToUpdate.value = null
    
    // Recarregar a lista
    await loadRequests(pagination.value.current_page)
  } catch (error: any) {
    errorHandler.handleError(error)
  } finally {
    updatingStatus.value = false
  }
}

// Utilitários
const formatDate = (dateString: string): string => {
  // Adicionar 'T00:00:00' para tratar como data local e evitar problemas de timezone
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}

const formatDateTime = (dateString: string): string => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const formatDateForAPI = (date: Date | string): string => {
  if (typeof date === 'string') return date
  // Usar getFullYear, getMonth, getDate para evitar problemas de timezone
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getStatusSeverity = (status: string): string => {
  switch (status) {
    case 'requested': return 'warning'
    case 'approved': return 'success'
    case 'cancelled': return 'danger'
    default: return 'info'
  }
}

const getStatusIcon = (status: string): string => {
  switch (status) {
    case 'requested': return 'pi pi-clock'
    case 'approved': return 'pi pi-check'
    case 'cancelled': return 'pi pi-times'
    default: return 'pi pi-info-circle'
  }
}

const getRowClass = (data: TravelRequest): string => {
  return `status-${data.status.value}`
}

// Lifecycle
onMounted(() => {
  loadRequests()
})
</script>

<style scoped>
.travel-requests-container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.filters-card {
  margin-bottom: 2rem;
  border: none;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.filter-dropdown,
.filter-input,
.filter-calendar {
  width: 100%;
}

.filters-actions {
  display: flex;
  justify-content: flex-end;
}

.requests-card {
  border: none;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.table-skeleton {
  padding: 1rem;
}

.skeleton-header {
  margin-bottom: 1rem;
}

.skeleton-rows {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 1.5rem;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-text {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.requests-table-container {
  overflow-x: auto;
}

.requests-table :deep(.p-datatable-header) {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.requests-table :deep(.p-datatable-tbody > tr) {
  border-bottom: 1px solid #f1f5f9;
}

.requests-table :deep(.p-datatable-tbody > tr:hover) {
  background: #f8fafc;
}

.requests-table :deep(.status-cancelled) {
  opacity: 0.7;
}

.id-column {
  width: 80px;
}

.actions-column {
  width: 160px;
}

.request-id {
  font-family: 'Monaco', 'Menlo', monospace;
  font-weight: 600;
  color: #6b7280;
}

.destination-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.destination-icon {
  color: #6b7280;
  font-size: 0.875rem;
}

.destination-text {
  font-weight: 500;
}

.dates-cell {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.date-separator {
  font-size: 0.75rem;
  color: #9ca3af;
}

.duration {
  font-size: 0.75rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  align-self: flex-start;
}

.created-date {
  font-size: 0.875rem;
  color: #6b7280;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.cancel-dialog-content {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.cancel-warning-icon {
  font-size: 2rem;
  color: #f59e0b;
  flex-shrink: 0;
}

.cancel-message h4 {
  margin: 0 0 0.5rem 0;
  color: #374151;
}

.cancel-message p {
  margin: 0 0 1rem 0;
  color: #6b7280;
}

.request-details {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  line-height: 1.5;
}


@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .empty-state {
    padding: 3rem 1rem;
  }
  
  .requests-table {
    font-size: 0.875rem;
  }
  
  .dates-cell {
    min-width: 120px;
  }
}
</style>