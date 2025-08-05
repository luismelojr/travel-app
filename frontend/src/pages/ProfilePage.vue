<template>
  <AppLayout>
    <div class="profile-container">
      <div class="page-header">
        <h1 class="page-title">Meu Perfil</h1>
      </div>

      <div class="profile-content">
        <Card class="profile-card">
          <template #title>
            <div class="card-header">
              <div class="user-avatar">
                <i class="pi pi-user"></i>
              </div>
              <div class="user-info">
                <h3 class="user-name">{{ user?.name }}</h3>
                <p class="user-email">{{ user?.email }}</p>
                <span class="user-role">{{ user?.role_label }}</span>
              </div>
            </div>
          </template>
          <template #content>
            <div class="profile-details">
              <div class="detail-row">
                <span class="detail-label">Data de Cadastro:</span>
                <span class="detail-value">{{ formatDate(user?.created_at) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Última Atualização:</span>
                <span class="detail-value">{{ formatDate(user?.updated_at) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email Verificado:</span>
                <span class="detail-value">
                  <Tag 
                    :value="user?.email_verified_at ? 'Verificado' : 'Não Verificado'"
                    :severity="user?.email_verified_at ? 'success' : 'warning'"
                  />
                </span>
              </div>
            </div>
          </template>
        </Card>

        <Card class="actions-card">
          <template #title>
            <h3>Ações</h3>
          </template>
          <template #content>
            <div class="actions-list">
              <Button 
                label="Editar Perfil" 
                icon="pi pi-pencil" 
                outlined 
                class="w-full action-button"
                @click="editProfile"
              />
              <Button 
                label="Alterar Senha" 
                icon="pi pi-lock" 
                outlined 
                severity="secondary"
                class="w-full action-button"
                @click="changePassword"
              />
              <Button 
                label="Configurações" 
                icon="pi pi-cog" 
                outlined 
                severity="secondary"
                class="w-full action-button"
                @click="openSettings"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import AppLayout from '@/layouts/AppLayout.vue'
import { authService } from '@/services/auth'
import { errorHandler } from '@/utils/errorHandler'
import type { User } from '@/types/auth'

const user = ref<User | null>(null)

const formatDate = (dateString: string | undefined) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const editProfile = () => {
  errorHandler.showInfoNotification('Funcionalidade em desenvolvimento')
}

const changePassword = () => {
  errorHandler.showInfoNotification('Funcionalidade em desenvolvimento')
}

const openSettings = () => {
  errorHandler.showInfoNotification('Funcionalidade em desenvolvimento')
}

onMounted(async () => {
  try {
    user.value = await authService.getCurrentUser()
  } catch (error) {
    user.value = authService.getCurrentUserFromStorage()
  }
})
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.profile-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
}

.profile-card,
.actions-card {
  border: none;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.user-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2rem;
}

.user-info {
  flex: 1;
}

.user-name {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.user-email {
  font-size: 1rem;
  color: #64748b;
  margin: 0 0 0.5rem 0;
}

.user-role {
  display: inline-block;
  background: #f1f5f9;
  color: #475569;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 500;
  color: #374151;
}

.detail-value {
  color: #6b7280;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.action-button {
  justify-content: flex-start;
  padding: 0.75rem 1rem;
}

.w-full {
  width: 100%;
}

@media (max-width: 768px) {
  .profile-content {
    grid-template-columns: 1fr;
  }
  
  .card-header {
    flex-direction: column;
    text-align: center;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}
</style>