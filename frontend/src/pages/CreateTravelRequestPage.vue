<template>
  <AppLayout>
    <div class="create-travel-request-container">
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
          <span class="breadcrumb-current">Nova Solicitação</span>
        </div>
        
        <div class="page-title-section">
          <h1 class="page-title">
            <i class="pi pi-plus page-title-icon"></i>
            Nova Solicitação de Viagem
          </h1>
          <p class="page-subtitle">
            Preencha os dados abaixo para criar uma nova solicitação de viagem.
          </p>
        </div>
      </div>

      <!-- Form Card -->
      <Card class="main-card">
        <template #content>
          <form @submit.prevent="submitForm" class="travel-form">
            <!-- Grid Layout -->
            <div class="form-grid-container">
              <!-- Left Column -->
              <div class="form-column">
                <!-- Informações do Solicitante -->
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="pi pi-user section-icon"></i>
                    Informações do Solicitante
                  </h2>

                  <div class="form-group">
                    <label for="requester_name" class="form-label required">
                      Nome do Solicitante
                    </label>
                    <InputText
                      id="requester_name"
                      v-model="form.requester_name"
                      placeholder="Digite o nome completo do solicitante"
                      :class="{ 'p-invalid': errors.requester_name }"
                      class="form-input"
                    />
                    <small v-if="errors.requester_name" class="p-error">
                      {{ errors.requester_name }}
                    </small>
                  </div>
                </div>

                <!-- Destino da Viagem -->
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="pi pi-map-marker section-icon"></i>
                    Destino da Viagem
                  </h2>

                  <div class="form-group">
                    <label for="destination" class="form-label required">
                      Destino
                    </label>
                    <InputText
                      id="destination"
                      v-model="form.destination"
                      placeholder="Digite o destino da viagem"
                      :class="{ 'p-invalid': errors.destination }"
                      class="form-input"
                    />
                    <small v-if="errors.destination" class="p-error">
                      {{ errors.destination }}
                    </small>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="form-column">
                <!-- Período da Viagem -->
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="pi pi-calendar section-icon"></i>
                    Período da Viagem
                  </h2>

                  <div class="date-grid">
                    <div class="form-group">
                      <label for="departure_date" class="form-label required">
                        Data de Partida
                      </label>
                      <Calendar
                        id="departure_date"
                        v-model="form.departure_date"
                        dateFormat="dd/mm/yy"
                        placeholder="Selecione a data de partida"
                        :class="{ 'p-invalid': errors.departure_date }"
                        class="form-input"
                        showIcon
                        :minDate="minDate"
                        :maxDate="form.return_date || undefined"
                      />
                      <small v-if="errors.departure_date" class="p-error">
                        {{ errors.departure_date }}
                      </small>
                    </div>

                    <div class="form-group">
                      <label for="return_date" class="form-label required">
                        Data de Retorno
                      </label>
                      <Calendar
                        id="return_date"
                        v-model="form.return_date"
                        dateFormat="dd/mm/yy"
                        placeholder="Selecione a data de retorno"
                        :class="{ 'p-invalid': errors.return_date }"
                        class="form-input"
                        showIcon
                        :minDate="form.departure_date || minDate"
                      />
                      <small v-if="errors.return_date" class="p-error">
                        {{ errors.return_date }}
                      </small>
                    </div>
                  </div>

                  <!-- Duration Display -->
                  <div v-if="calculatedDuration > 0" class="duration-display">
                    <div class="duration-card">
                      <i class="pi pi-clock duration-icon"></i>
                      <div class="duration-content">
                        <span class="duration-label">Duração da Viagem</span>
                        <span class="duration-value">
                          {{ calculatedDuration }} dia{{ calculatedDuration !== 1 ? "s" : "" }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Observações - Full Width -->
            <div class="form-section full-width">
              <h2 class="section-title">
                <i class="pi pi-file-edit section-icon"></i>
                Observações (Opcional)
              </h2>

              <div class="form-group">
                <label for="notes" class="form-label">
                  Observações Adicionais
                </label>
                <Textarea
                  id="notes"
                  v-model="form.notes"
                  placeholder="Digite observações adicionais sobre a viagem (opcional)"
                  :class="{ 'p-invalid': errors.notes }"
                  class="form-textarea"
                  rows="4"
                  :maxlength="1000"
                />
                <div class="textarea-footer">
                  <small v-if="errors.notes" class="p-error">
                    {{ errors.notes }}
                  </small>
                  <small class="char-counter">
                    {{ form.notes?.length || 0 }}/1000 caracteres
                  </small>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <Button
                label="Cancelar"
                icon="pi pi-times"
                outlined
                @click="goBack"
                :disabled="loading"
                class="cancel-button"
              />
              <Button
                type="submit"
                label="Criar Solicitação"
                icon="pi pi-check"
                :loading="loading"
                loadingIcon="pi pi-spinner pi-spin"
                class="submit-button"
              />
            </div>
          </form>
        </template>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Calendar from "primevue/calendar";
import Textarea from "primevue/textarea";
import AppLayout from "@/layouts/AppLayout.vue";
import { travelRequestService } from "@/services/travelRequest";
import { errorHandler } from "@/utils/errorHandler";
import type { CreateTravelRequestData } from "@/types/travelRequest";

const router = useRouter();

// Estados
const loading = ref(false);
const minDate = ref(new Date());

// Form data
const form = ref<{
  requester_name: string;
  destination: string;
  departure_date: Date | null;
  return_date: Date | null;
  notes: string;
}>({
  requester_name: "",
  destination: "",
  departure_date: null,
  return_date: null,
  notes: "",
});

// Form errors
const errors = ref<Record<string, string>>({});

// Computed
const calculatedDuration = computed(() => {
  if (!form.value.departure_date || !form.value.return_date) return 0;
  
  const departure = new Date(form.value.departure_date);
  const returnDate = new Date(form.value.return_date);
  const diffTime = returnDate.getTime() - departure.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  
  return diffDays > 0 ? diffDays : 0;
});

// Methods
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.value.requester_name.trim()) {
    errors.value.requester_name = "O nome do solicitante é obrigatório.";
  }

  if (!form.value.destination.trim()) {
    errors.value.destination = "O destino é obrigatório.";
  }

  if (!form.value.departure_date) {
    errors.value.departure_date = "A data de partida é obrigatória.";
  } else if (form.value.departure_date < minDate.value) {
    errors.value.departure_date =
      "A data de partida não pode ser anterior a hoje.";
  }

  if (!form.value.return_date) {
    errors.value.return_date = "A data de retorno é obrigatória.";
  } else if (
    form.value.departure_date &&
    form.value.return_date <= form.value.departure_date
  ) {
    errors.value.return_date =
      "A data de retorno deve ser posterior à data de partida.";
  }

  if (form.value.notes && form.value.notes.length > 1000) {
    errors.value.notes = "As observações não podem ter mais de 1000 caracteres.";
  }

  return Object.keys(errors.value).length === 0;
};

const submitForm = async () => {
  if (!validateForm()) {
    errorHandler.showErrorNotification("Por favor, corrija os erros no formulário");
    return;
  }

  try {
    loading.value = true;

    const requestData: CreateTravelRequestData = {
      requester_name: form.value.requester_name.trim(),
      destination: form.value.destination.trim(),
      departure_date: formatDateForAPI(form.value.departure_date!),
      return_date: formatDateForAPI(form.value.return_date!),
      notes: form.value.notes.trim() || undefined,
    };

    const createdRequest = await travelRequestService.create(requestData);

    errorHandler.showSuccessNotification("Solicitação criada com sucesso!");

    // Redirecionar para a página de detalhes da solicitação criada
    router.push({
      name: "travel-request-detail",
      params: { id: createdRequest.id.toString() },
    });
  } catch (error: any) {
    errorHandler.handleError(error, {
      customMessage: "Erro ao criar solicitação",
    });
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.back();
};

// Utilities
const formatDateForAPI = (date: Date): string => {
  // Usar getFullYear, getMonth, getDate para evitar problemas de timezone
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Lifecycle
onMounted(() => {
  // Set minimum date to today at 00:00:00
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  minDate.value = today;
});
</script>

<style scoped>
.create-travel-request-container {
  max-width: 1400px;
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
  margin-bottom: 2rem;
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

.page-title-section {
  text-align: left;
}

.page-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-title-icon {
  color: #059669;
}

.page-subtitle {
  color: #6b7280;
  font-size: 1rem;
  margin: 0;
  line-height: 1.5;
}

.main-card {
  border: none;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.travel-form {
  padding: 0;
}

.form-grid-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.form-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1.5rem;
  border-left: 4px solid #059669;
}

.form-section.full-width {
  grid-column: 1 / -1;
  margin-top: 2rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 1.5rem 0;
}

.section-icon {
  color: #059669;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-label.required::after {
  content: " *";
  color: #ef4444;
}

.form-input,
.form-textarea {
  width: 100%;
}

.date-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.duration-display {
  margin-top: 1.5rem;
}

.duration-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.duration-icon {
  color: #059669;
  font-size: 1.25rem;
  background: #dcfce7;
  padding: 0.75rem;
  border-radius: 50%;
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.duration-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.duration-label {
  font-size: 0.875rem;
  color: #065f46;
  font-weight: 500;
}

.duration-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #047857;
}

.textarea-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.5rem;
}

.char-counter {
  color: #6b7280;
  font-size: 0.75rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e7eb;
}

.cancel-button {
  min-width: 120px;
}

.submit-button {
  min-width: 180px;
}

@media (max-width: 1024px) {
  .form-grid-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .date-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

@media (max-width: 768px) {
  .create-travel-request-container {
    max-width: none;
  }

  .page-title {
    font-size: 1.5rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .form-section {
    padding: 1rem;
  }

  .duration-card {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }

  .duration-icon {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 1rem;
  }

  .form-actions {
    flex-direction: column-reverse;
    gap: 0.75rem;
  }

  .cancel-button,
  .submit-button {
    width: 100%;
    min-width: auto;
  }
}
</style>