<template>
  <div class="layout-wrapper">
    <ModernHeader />

    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import ModernHeader from "@/components/ModernHeader.vue";
</script>

<style scoped>
.layout-wrapper {
  min-height: 100vh;
  background-color: #f8fafc;
}

.main-content {
  padding: 2rem;
  max-width: 1450px;
  margin: 0 auto;
  width: 100%;
}

@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }
}
</style>
