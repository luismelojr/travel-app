<template>
  <AppLayout>
    <div class="travel-request-detail-container">
      <!-- Header com breadcrumb -->
      <div class="page-header">
        <div class="breadcrumb">
          <Button
            icon="pi pi-arrow-left"
            text
            @click="goBack"
            class="back-button"
          />
          <span class="breadcrumb-separator">/</span>
          <router-link to="/travel-requests" class="breadcrumb-link">
            Solicitações
          </router-link>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">
            {{ loading ? "Carregando..." : `#${travelRequest?.id}` }}
          </span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="detail-skeleton">
        <Card class="main-card">
          <template #content>
            <div class="skeleton-header">
              <Skeleton height="32px" width="200px" borderRadius="6px" />
              <Skeleton height="24px" width="100px" borderRadius="12px" />
            </div>
            <div class="skeleton-content">
              <div class="skeleton-section">
                <Skeleton height="20px" width="120px" borderRadius="4px" />
                <Skeleton height="16px" width="180px" borderRadius="4px" />
              </div>
              <div class="skeleton-section">
                <Skeleton height="20px" width="100px" borderRadius="4px" />
                <Skeleton height="16px" width="160px" borderRadius="4px" />
              </div>
              <div class="skeleton-section">
                <Skeleton height="20px" width="80px" borderRadius="4px" />
                <Skeleton height="40px" width="100%" borderRadius="6px" />
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <Card class="error-card">
          <template #content>
            <div class="error-content">
              <i class="pi pi-exclamation-triangle error-icon"></i>
              <h3 class="error-title">Erro ao carregar solicitação</h3>
              <p class="error-message">{{ error }}</p>
              <div class="error-actions">
                <Button
                  label="Tentar Novamente"
                  icon="pi pi-refresh"
                  @click="loadTravelRequest"
                />
                <Button
                  label="Voltar"
                  icon="pi pi-arrow-left"
                  outlined
                  @click="goBack"
                />
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Content -->
      <div v-else-if="travelRequest" class="detail-content">
        <!-- Main Card -->
        <Card class="main-card">
          <template #content>
            <div class="request-header">
              <div class="header-info">
                <h1 class="request-title">
                  Solicitação #{{ travelRequest.id }}
                </h1>
                <Tag
                  :value="travelRequest.status.label"
                  :severity="getStatusSeverity(travelRequest.status.value)"
                  :icon="getStatusIcon(travelRequest.status.value)"
                  class="status-tag"
                />
              </div>
              <div class="header-actions">
                <Button
                  v-if="isAdmin && canUpdateStatus(travelRequest)"
                  label="Atualizar Status"
                  icon="pi pi-pencil"
                  outlined
                  @click="showStatusDialog = true"
                />
                <Button
                  v-if="canCancelRequest(travelRequest)"
                  label="Cancelar Solicitação"
                  icon="pi pi-times"
                  severity="danger"
                  outlined
                  @click="confirmCancelRequest"
                />
              </div>
            </div>

            <Divider />

            <!-- Request Details Grid -->
            <div class="details-grid">
              <!-- Basic Information -->
              <div class="detail-section">
                <h2 class="section-title">
                  <i class="pi pi-info-circle section-icon"></i>
                  Informações Básicas
                </h2>
                <div class="detail-rows">
                  <div class="detail-row">
                    <span class="detail-label">Solicitante:</span>
                    <span class="detail-value">{{
                      travelRequest.requester_name
                    }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Destino:</span>
                    <div class="destination-value">
                      <i class="pi pi-map-marker destination-icon"></i>
                      <span>{{ travelRequest.destination }}</span>
                    </div>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <Tag
                      :value="travelRequest.status.label"
                      :severity="getStatusSeverity(travelRequest.status.value)"
                      :icon="getStatusIcon(travelRequest.status.value)"
                      size="small"
                    />
                  </div>
                </div>
              </div>

              <!-- Travel Dates -->
              <div class="detail-section">
                <h2 class="section-title">
                  <i class="pi pi-calendar section-icon"></i>
                  Período da Viagem
                </h2>
                <div class="detail-rows">
                  <div class="detail-row">
                    <span class="detail-label">Data de Partida:</span>
                    <div class="date-value">
                      <i class="pi pi-calendar-plus date-icon departure"></i>
                      <span>{{
                        formatDate(travelRequest.departure_date)
                      }}</span>
                    </div>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Data de Retorno:</span>
                    <div class="date-value">
                      <i class="pi pi-calendar-minus date-icon return"></i>
                      <span>{{ formatDate(travelRequest.return_date) }}</span>
                    </div>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Duração:</span>
                    <div class="duration-value">
                      <i class="pi pi-clock duration-icon"></i>
                      <span>{{ travelRequest.duration_days }} dias</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Information (if available) -->
              <div v-if="travelRequest.user" class="detail-section">
                <h2 class="section-title">
                  <i class="pi pi-user section-icon"></i>
                  Informações do Usuário
                </h2>
                <div class="detail-rows">
                  <div class="detail-row">
                    <span class="detail-label">Nome:</span>
                    <span class="detail-value">{{
                      travelRequest.user.name
                    }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">E-mail:</span>
                    <span class="detail-value">{{
                      travelRequest.user.email
                    }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Perfil:</span>
                    <span class="detail-value">{{
                      travelRequest.user.role_label
                    }}</span>
                  </div>
                </div>
              </div>

              <!-- Timeline -->
              <div class="detail-section full-width">
                <h2 class="section-title">
                  <i class="pi pi-history section-icon"></i>
                  Histórico
                </h2>
                <div class="timeline">
                  <div class="timeline-item">
                    <div class="timeline-marker created"></div>
                    <div class="timeline-content">
                      <h4 class="timeline-title">Solicitação Criada</h4>
                      <p class="timeline-time">
                        {{ formatDateTime(travelRequest.created_at) }}
                      </p>
                    </div>
                  </div>
                  <div
                    v-if="travelRequest.updated_at !== travelRequest.created_at"
                    class="timeline-item"
                  >
                    <div class="timeline-marker updated"></div>
                    <div class="timeline-content">
                      <h4 class="timeline-title">Última Atualização</h4>
                      <p class="timeline-time">
                        {{ formatDateTime(travelRequest.updated_at) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes Section (if available) -->
            <div v-if="travelRequest.notes" class="notes-section">
              <Divider />
              <h2 class="section-title">
                <i class="pi pi-file-edit section-icon"></i>
                Observações
              </h2>
              <div class="notes-content">
                <p>{{ travelRequest.notes }}</p>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Cancel Confirmation Dialog -->
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
          <div v-if="travelRequest" class="request-summary">
            <strong>Destino:</strong> {{ travelRequest.destination }}<br />
            <strong>Período:</strong>
            {{ formatDate(travelRequest.departure_date) }} -
            {{ formatDate(travelRequest.return_date) }}
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
      :travel-request="travelRequest"
      :loading="updatingStatus"
      @update-status="updateRequestStatus"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import StatusUpdateModal from "@/components/StatusUpdateModal.vue";
import Divider from "primevue/divider";
import Skeleton from "primevue/skeleton";
import AppLayout from "@/layouts/AppLayout.vue";
import { travelRequestService } from "@/services/travelRequest";
import { errorHandler } from "@/utils/errorHandler";
import { authService } from "@/services/auth";
import type { TravelRequest } from "@/types/travelRequest";

const route = useRoute();
const router = useRouter();

// Estados
const loading = ref(true);
const error = ref<string | null>(null);
const travelRequest = ref<TravelRequest | null>(null);
const showCancelDialog = ref(false);
const cancelling = ref(false);
const showStatusDialog = ref(false);
const updatingStatus = ref(false);

// Computed
const isAdmin = computed(() => {
  const user = authService.getCurrentUserFromStorage();
  return user?.role === "admin";
});

// Métodos
const loadTravelRequest = async () => {
  try {
    loading.value = true;
    error.value = null;

    const requestId = parseInt(route.params.id as string);
    if (isNaN(requestId)) {
      throw new Error("ID da solicitação inválido");
    }

    travelRequest.value = await travelRequestService.getById(requestId);
  } catch (err: any) {
    error.value = err.toString();
    console.error("Erro ao carregar solicitação:", err);
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.back();
};

const canCancelRequest = (request: TravelRequest): boolean => {
  return request.status.value === "requested";
};

const canUpdateStatus = (request: TravelRequest): boolean => {
  // Admins podem atualizar status apenas se não estiver aprovado
  return request.status.value !== 'approved';
};

const confirmCancelRequest = () => {
  showCancelDialog.value = true;
};

const cancelRequest = async () => {
  if (!travelRequest.value) return;

  try {
    cancelling.value = true;
    const updatedRequest = await travelRequestService.cancel(
      travelRequest.value.id
    );

    // Atualizar o objeto local com os dados atualizados
    travelRequest.value = updatedRequest;

    errorHandler.showSuccessNotification("Solicitação cancelada com sucesso");
    showCancelDialog.value = false;
  } catch (error: any) {
    errorHandler.handleError(error, {
      customMessage: "Erro ao cancelar solicitação",
    });
  } finally {
    cancelling.value = false;
  }
};

const updateRequestStatus = async (newStatus: "requested" | "approved" | "cancelled") => {
  if (!travelRequest.value) return;

  try {
    updatingStatus.value = true;
    const updatedRequest = await travelRequestService.updateStatus(
      travelRequest.value.id,
      newStatus
    );

    travelRequest.value = updatedRequest;

    errorHandler.showSuccessNotification("Status atualizado com sucesso");
    showStatusDialog.value = false;
  } catch (error: any) {
    console.log("ERROR", error);
    errorHandler.handleError(error);
  } finally {
    updatingStatus.value = false;
  }
};

// Utilitários
const formatDate = (dateString: string): string => {
  // Adicionar 'T00:00:00' para tratar como data local e evitar problemas de timezone
  const date = new Date(dateString + "T00:00:00");
  return date.toLocaleDateString("pt-BR", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

const formatDateTime = (dateString: string): string => {
  return new Date(dateString).toLocaleString("pt-BR", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getStatusSeverity = (status: string): string => {
  switch (status) {
    case "requested":
      return "warning";
    case "approved":
      return "success";
    case "cancelled":
      return "danger";
    default:
      return "info";
  }
};

const getStatusIcon = (status: string): string => {
  switch (status) {
    case "requested":
      return "pi pi-clock";
    case "approved":
      return "pi pi-check";
    case "cancelled":
      return "pi pi-times";
    default:
      return "pi pi-info-circle";
  }
};

// Lifecycle
onMounted(() => {
  loadTravelRequest();
});
</script>

<style scoped>
.travel-request-detail-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.back-button {
  padding: 0.5rem;
  border-radius: 6px;
}

.breadcrumb-separator {
  color: #9ca3af;
}

.breadcrumb-link {
  color: #059669;
  text-decoration: none;
  font-weight: 500;
}

.breadcrumb-link:hover {
  text-decoration: underline;
}

.breadcrumb-current {
  color: #374151;
  font-weight: 600;
}

.detail-skeleton {
  animation: pulse 2s infinite;
}

.skeleton-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.skeleton-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.skeleton-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.error-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.error-card {
  max-width: 500px;
  width: 100%;
}

.error-content {
  text-align: center;
  padding: 2rem;
}

.error-icon {
  font-size: 3rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.error-message {
  color: #6b7280;
  margin-bottom: 2rem;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.main-card {
  border: none;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.request-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.status-tag {
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.detail-section {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1.5rem;
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.section-icon {
  color: #059669;
}

.detail-rows {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: #374151;
  min-width: 120px;
}

.detail-value {
  color: #6b7280;
  font-weight: 500;
}

.destination-value,
.date-value,
.duration-value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  font-weight: 500;
}

.destination-icon {
  color: #ef4444;
}

.date-icon.departure {
  color: #059669;
}

.date-icon.return {
  color: #dc2626;
}

.duration-icon {
  color: #3b82f6;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: "";
  position: absolute;
  left: 0.75rem;
  top: 1rem;
  bottom: 1rem;
  width: 2px;
  background: #e5e7eb;
}

.timeline-item {
  position: relative;
  padding-bottom: 2rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -1.75rem;
  top: 0.25rem;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline-marker.created {
  background: #059669;
}

.timeline-marker.updated {
  background: #3b82f6;
}

.timeline-title {
  font-weight: 600;
  color: #374151;
  margin: 0 0 0.25rem 0;
}

.timeline-time {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.notes-section {
  margin-top: 2rem;
}

.notes-content {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1.5rem;
  border-left: 4px solid #059669;
}

.notes-content p {
  color: #374151;
  line-height: 1.6;
  margin: 0;
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

.request-summary {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  line-height: 1.5;
}


@media (max-width: 768px) {
  .request-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .header-actions {
    justify-content: stretch;
  }

  .details-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .detail-label {
    min-width: auto;
  }

  .timeline {
    padding-left: 1.5rem;
  }

  .timeline-marker {
    left: -1.25rem;
  }
}
</style>
