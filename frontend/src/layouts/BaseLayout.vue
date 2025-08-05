<template>
  <div class="min-h-screen bg-gray-50">
    <slot />
  </div>
</template>

<script setup lang="ts">
// Layout base limpo sem header/footer
</script>

<style scoped>
.min-h-screen {
  min-height: 100vh;
}

.bg-gray-50 {
  background-color: #f9fafb;
}
</style>