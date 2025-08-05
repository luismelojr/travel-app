<template>
  <AppLayout>
    <div class="dashboard-container">
      <div class="welcome-section">
        <h1 class="welcome-title">Bem-vindo, {{ user?.name }}!</h1>
        <p class="welcome-subtitle">Gerencie suas solicitações de viagem de forma simples e eficiente</p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-content" v-if="!loading">
            <div class="stat-icon pending">
              <i class="pi pi-clock"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ stats?.pending || 0 }}</h3>
              <p class="stat-label">Pendentes</p>
            </div>
          </div>
          <div class="stat-skeleton" v-else>
            <Skeleton height="48px" width="48px" borderRadius="10px" class="stat-icon-skeleton" />
            <div class="stat-info-skeleton">
              <Skeleton height="28px" width="40px" borderRadius="4px" class="stat-number-skeleton" />
              <Skeleton height="14px" width="70px" borderRadius="4px" class="stat-label-skeleton" />
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content" v-if="!loading">
            <div class="stat-icon approved">
              <i class="pi pi-check"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ stats?.approved || 0 }}</h3>
              <p class="stat-label">Aprovadas</p>
            </div>
          </div>
          <div class="stat-skeleton" v-else>
            <Skeleton height="48px" width="48px" borderRadius="10px" class="stat-icon-skeleton" />
            <div class="stat-info-skeleton">
              <Skeleton height="28px" width="40px" borderRadius="4px" class="stat-number-skeleton" />
              <Skeleton height="14px" width="80px" borderRadius="4px" class="stat-label-skeleton" />
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content" v-if="!loading">
            <div class="stat-icon rejected">
              <i class="pi pi-times"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ stats?.cancelled || 0 }}</h3>
              <p class="stat-label">Canceladas</p>
            </div>
          </div>
          <div class="stat-skeleton" v-else>
            <Skeleton height="48px" width="48px" borderRadius="10px" class="stat-icon-skeleton" />
            <div class="stat-info-skeleton">
              <Skeleton height="28px" width="40px" borderRadius="4px" class="stat-number-skeleton" />
              <Skeleton height="14px" width="85px" borderRadius="4px" class="stat-label-skeleton" />
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content" v-if="!loading">
            <div class="stat-icon total">
              <i class="pi pi-calendar"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ stats?.total || 0 }}</h3>
              <p class="stat-label">Total</p>
            </div>
          </div>
          <div class="stat-skeleton" v-else>
            <Skeleton height="48px" width="48px" borderRadius="10px" class="stat-icon-skeleton" />
            <div class="stat-info-skeleton">
              <Skeleton height="28px" width="40px" borderRadius="4px" class="stat-number-skeleton" />
              <Skeleton height="14px" width="45px" borderRadius="4px" class="stat-label-skeleton" />
            </div>
          </div>
        </div>
      </div>

      <div class="quick-actions-section">
        <h2 class="section-title">Ações Rápidas</h2>
        <div class="actions-grid">
          <Button 
            icon="pi pi-plus" 
            label="Nova Solicitação" 
            class="action-button primary"
            @click="$router.push('/travel-requests/new')"
          />
          <Button 
            icon="pi pi-list" 
            label="Minhas Solicitações" 
            class="action-button secondary"
            outlined
            @click="$router.push('/travel-requests')"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import AppLayout from '@/layouts/AppLayout.vue'
import { authService } from '@/services/auth'
import { travelRequestService, type TravelRequestStats } from '@/services/travelRequest'
import type { User } from '@/types/auth'

const user = ref<User | null>(null)
const stats = ref<TravelRequestStats | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const loadStats = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Adiciona um delay mínimo para melhor experiência visual do loading
    const [statsResult] = await Promise.all([
      travelRequestService.getStats(),
      new Promise(resolve => setTimeout(resolve, 800)) // Delay mínimo de 800ms
    ])
    
    stats.value = statsResult
  } catch (err: any) {
    error.value = err.toString()
    console.error('Erro ao carregar estatísticas:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    user.value = await authService.getCurrentUser()
  } catch (error) {
    user.value = authService.getCurrentUserFromStorage()
  }
  
  await loadStats()
})
</script>

<style scoped>
.dashboard-container {
  padding: 0;
}

.welcome-section {
  margin-bottom: 2.5rem;
  text-align: center;
}

.welcome-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.75rem;
  letter-spacing: -0.025em;
}

.welcome-subtitle {
  font-size: 1.125rem;
  color: #6b7280;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.1);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-skeleton {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-info-skeleton {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.stat-icon-skeleton {
  flex-shrink: 0;
}

.stat-number-skeleton {
  align-self: flex-start;
}

.stat-label-skeleton {
  align-self: flex-start;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  flex-shrink: 0;
}

.stat-icon.pending {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-icon.approved {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.stat-icon.rejected {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stat-icon.total {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
  line-height: 1;
}

.stat-label {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
  font-weight: 500;
}

.quick-actions-section {
  text-align: center;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.actions-grid {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.action-button {
  min-width: 200px;
  height: 52px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.action-button.primary {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  border: none;
  color: white;
}

.action-button.primary:hover {
  background: linear-gradient(135deg, #047857 0%, #065f46 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px 0 rgba(5, 150, 105, 0.3);
}

.action-button.secondary {
  border: 2px solid #e5e7eb;
  background: white;
  color: #6b7280;
}

.action-button.secondary:hover {
  border-color: #059669;
  color: #059669;
  background: #f0fdf4;
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .welcome-title {
    font-size: 1.875rem;
  }
  
  .welcome-subtitle {
    font-size: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .stat-content {
    gap: 0.75rem;
  }
  
  .stat-skeleton {
    gap: 0.75rem;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .stat-icon-skeleton {
    width: 40px !important;
    height: 40px !important;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  .actions-grid {
    flex-direction: column;
    align-items: center;
  }
  
  .action-button {
    width: 100%;
    max-width: 300px;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>