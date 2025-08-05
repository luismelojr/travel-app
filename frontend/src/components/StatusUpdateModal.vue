<template>
  <Dialog 
    v-model:visible="isVisible" 
    :style="{ width: '450px' }" 
    header="Atualizar Status da Solicitação" 
    modal
    @hide="handleClose"
  >
    <div class="status-dialog-content">
      <div class="status-message">
        <h4>Selecione o novo status para esta solicitação:</h4>
        <div v-if="travelRequest" class="request-summary">
          <strong>Solicitação:</strong> #{{ travelRequest.id }}<br>
          <strong>Solicitante:</strong> {{ travelRequest.requester_name }}<br>
          <strong>Destino:</strong> {{ travelRequest.destination }}<br>
          <strong>Status atual:</strong> 
          <Tag
            :value="travelRequest.status.label"
            :severity="getStatusSeverity(travelRequest.status.value)"
            :icon="getStatusIcon(travelRequest.status.value)"
            size="small"
          />
        </div>
        
        <div class="status-options">
          <label class="status-option">
            <input 
              type="radio" 
              v-model="selectedStatus" 
              value="requested"
              :disabled="travelRequest?.status.value === 'requested'"
            />
            <div class="option-content">
              <Tag value="Solicitado" severity="warning" icon="pi pi-clock" size="small" />
              <span class="option-description">Marcar como solicitado/pendente</span>
            </div>
          </label>
          
          <label class="status-option">
            <input 
              type="radio" 
              v-model="selectedStatus" 
              value="approved"
              :disabled="travelRequest?.status.value === 'approved'"
            />
            <div class="option-content">
              <Tag value="Aprovado" severity="success" icon="pi pi-check" size="small" />
              <span class="option-description">Aprovar a solicitação de viagem</span>
            </div>
          </label>
          
          <label class="status-option">
            <input 
              type="radio" 
              v-model="selectedStatus" 
              value="cancelled"
              :disabled="travelRequest?.status.value === 'cancelled'"
            />
            <div class="option-content">
              <Tag value="Cancelado" severity="danger" icon="pi pi-times" size="small" />
              <span class="option-description">Cancelar/rejeitar a solicitação</span>
            </div>
          </label>
        </div>
      </div>
    </div>
    <template #footer>
      <Button 
        label="Cancelar" 
        outlined 
        @click="handleClose" 
      />
      <Button 
        label="Atualizar Status" 
        :loading="loading"
        :disabled="!selectedStatus || selectedStatus === travelRequest?.status.value"
        @click="handleUpdateStatus" 
      />
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import type { TravelRequest } from '@/types/travelRequest'

interface Props {
  visible: boolean
  travelRequest: TravelRequest | null
  loading?: boolean
}

interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'update-status', status: 'requested' | 'approved' | 'cancelled'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
})

const emit = defineEmits<Emits>()

const selectedStatus = ref<'requested' | 'approved' | 'cancelled' | null>(null)

const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const handleClose = () => {
  selectedStatus.value = null
  emit('update:visible', false)
}

const handleUpdateStatus = () => {
  if (selectedStatus.value) {
    emit('update-status', selectedStatus.value)
  }
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

// Reset selected status when dialog opens
watch(() => props.visible, (newValue) => {
  if (newValue) {
    selectedStatus.value = null
  }
})
</script>

<style scoped>
.status-dialog-content {
  margin: 1rem 0;
}

.status-message h4 {
  margin: 0 0 1rem 0;
  color: #374151;
}

.request-summary {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  line-height: 1.5;
}

.status-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.status-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.status-option:hover {
  border-color: #059669;
  background: #f0fdf4;
}

.status-option input[type="radio"] {
  margin: 0;
}

.status-option input[type="radio"]:disabled {
  cursor: not-allowed;
}

.status-option:has(input[type="radio"]:disabled) {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f9fafb;
}

.status-option:has(input[type="radio"]:disabled):hover {
  border-color: #e5e7eb;
  background: #f9fafb;
}

.option-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.option-description {
  font-size: 0.875rem;
  color: #6b7280;
}
</style>