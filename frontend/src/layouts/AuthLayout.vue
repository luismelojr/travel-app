<template>
  <BaseLayout>
    <div class="auth-container">
      <div class="auth-content">
        <div class="form-section">
          <div class="form-header">
            <div class="brand-logo">
              <i class="pi pi-map-marker"></i>
            </div>
            <h1 class="brand-title">Travel</h1>
            <p class="brand-subtitle">Sistema de Gerenciamento de Viagens</p>
          </div>
          <slot />
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import BaseLayout from './BaseLayout.vue'
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: #f8fafc;
}

.auth-content {
  max-width: 480px;
  width: 100%;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.form-section {
  padding: 2.5rem;
}

.form-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.brand-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  border-radius: 16px;
  margin-bottom: 1rem;
}

.brand-logo i {
  font-size: 24px;
  color: white;
}

.brand-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
  letter-spacing: -0.025em;
}

.brand-subtitle {
  font-size: 0.95rem;
  color: #6b7280;
  line-height: 1.5;
  font-weight: 400;
}

@media (max-width: 640px) {
  .auth-container {
    padding: 1rem;
  }
  
  .form-section {
    padding: 2rem;
  }
  
  .brand-title {
    font-size: 1.75rem;
  }
  
  .brand-logo {
    width: 56px;
    height: 56px;
  }
  
  .brand-logo i {
    font-size: 20px;
  }
}
</style>