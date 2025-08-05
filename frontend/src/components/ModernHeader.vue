<template>
  <header class="modern-header">
    <!-- Main Header Container -->
    <div class="header-container">
      <!-- Logo Section -->
      <div
        class="logo-section"
        @click="goToHome"
        role="button"
        tabindex="0"
        @keyup.enter="goToHome"
      >
        <div class="logo-wrapper">
          <div class="logo-icon">
            <i class="pi pi-map-marker"></i>
          </div>
          <div class="logo-text">
            <span class="brand-name">Travel</span>
          </div>
        </div>
      </div>

      <!-- Desktop Navigation -->
      <nav class="desktop-navigation" role="navigation">
        <div class="nav-items">
          <router-link
            to="/dashboard"
            class="nav-item"
            :class="{ active: isActiveRoute(['dashboard']) }"
          >
            <i class="pi pi-home nav-icon"></i>
            <span class="nav-label">Dashboard</span>
          </router-link>

          <router-link
            to="/travel-requests"
            class="nav-item"
            :class="{
              active: isActiveRoute([
                'travel-requests',
                'create-travel-request',
                'travel-request-detail',
              ]),
            }"
          >
            <i class="pi pi-calendar nav-icon"></i>
            <span class="nav-label">Solicitações</span>
          </router-link>
        </div>
      </nav>

      <!-- Header Actions -->
      <div class="header-actions">
        <!-- Mobile Menu Toggle -->
        <div class="action-item mobile-only">
          <Button
            :icon="isMobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"
            text
            @click="toggleMobileMenu"
            class="mobile-toggle"
            aria-label="Menu"
          />
        </div>

        <!-- User Profile -->
        <div class="user-profile">
          <Button
            label="Sair"
            icon="pi pi-sign-out"
            text
            @click="logout"
            class="logout-btn"
          />

          <div class="user-avatar">
            <div class="avatar-circle">
              <span class="avatar-initial">{{ getUserInitial() }}</span>
            </div>
            <div class="user-info desktop-only">
              <span class="user-name">{{ user?.name || "Usuário" }}</span>
              <span class="user-role">{{ getUserRole() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <Transition name="mobile-slide">
      <div v-if="isMobileMenuOpen" class="mobile-navigation">
        <div class="mobile-nav-content">
          <div class="mobile-nav-items">
            <router-link
              to="/dashboard"
              class="mobile-nav-item"
              :class="{ active: isActiveRoute(['dashboard']) }"
              @click="closeMobileMenu"
            >
              <div class="mobile-nav-icon">
                <i class="pi pi-home"></i>
              </div>
              <div class="mobile-nav-content-item">
                <span class="mobile-nav-label">Dashboard</span>
                <span class="mobile-nav-description"
                  >Visão geral do sistema</span
                >
              </div>
              <i class="pi pi-chevron-right mobile-nav-arrow"></i>
            </router-link>

            <router-link
              to="/travel-requests"
              class="mobile-nav-item"
              :class="{
                active: isActiveRoute([
                  'travel-requests',
                  'create-travel-request',
                  'travel-request-detail',
                ]),
              }"
              @click="closeMobileMenu"
            >
              <div class="mobile-nav-icon">
                <i class="pi pi-calendar"></i>
              </div>
              <div class="mobile-nav-content-item">
                <span class="mobile-nav-label">Solicitações</span>
                <span class="mobile-nav-description">Gerenciar viagens</span>
              </div>
              <i class="pi pi-chevron-right mobile-nav-arrow"></i>
            </router-link>
          </div>

          <!-- Mobile User Actions -->
          <div class="mobile-user-section">
            <div class="mobile-user-info">
              <div class="mobile-avatar">
                <span class="mobile-avatar-initial">{{
                  getUserInitial()
                }}</span>
              </div>
              <div class="mobile-user-details">
                <span class="mobile-user-name">{{
                  user?.name || "Usuário"
                }}</span>
                <span class="mobile-user-role">{{ getUserRole() }}</span>
              </div>
            </div>

            <div class="mobile-actions">
              <Button
                label="Sair"
                icon="pi pi-sign-out"
                severity="danger"
                text
                @click="logout"
                class="mobile-action-btn"
              />
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Mobile Overlay -->
    <Transition name="overlay">
      <div
        v-if="isMobileMenuOpen"
        class="mobile-overlay"
        @click="closeMobileMenu"
      ></div>
    </Transition>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import Button from "primevue/button";
import { authService } from "@/services/auth";
import { errorHandler } from "@/utils/errorHandler";
import type { User } from "@/types/auth";

const router = useRouter();
const route = useRoute();
const user = ref<User | null>(null);
const isMobileMenuOpen = ref(false);

// Methods
const goToHome = () => {
  router.push("/dashboard");
};

const isActiveRoute = (routeNames: string[]) => {
  return routeNames.includes(route.name as string);
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const getUserInitial = () => {
  return user.value?.name?.charAt(0).toUpperCase() || "U";
};

const getUserRole = () => {
  return user.value?.role === "admin" ? "Administrador" : "Usuário";
};

async function logout() {
  try {
    await authService.logout();
    errorHandler.showSuccessNotification("Logout realizado com sucesso");
    router.push("/login");
  } catch (error) {
    errorHandler.handleError(error);
  }
}

// Handle click outside to close mobile menu
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement;
  if (!target.closest(".modern-header") && isMobileMenuOpen.value) {
    closeMobileMenu();
  }
};

// Handle escape key
const handleEscapeKey = (event: KeyboardEvent) => {
  if (event.key === "Escape" && isMobileMenuOpen.value) {
    closeMobileMenu();
  }
};

// Router guard to close mobile menu on route change
router.afterEach(() => {
  closeMobileMenu();
});

onMounted(async () => {
  try {
    user.value = await authService.getCurrentUser();
  } catch (error) {
    user.value = authService.getCurrentUserFromStorage();
  }

  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscapeKey);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEscapeKey);
});
</script>

<style scoped>
/* Modern Header Styles */
.modern-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.5rem;
  max-width: 1450px;
  margin: 0 auto;
  min-height: 70px;
}

/* Logo Section */
.logo-section {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.logo-section:hover {
  transform: translateY(-1px);
}

.logo-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-icon {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

/* Desktop Navigation */
.desktop-navigation {
  flex: 1;
  display: flex;
  justify-content: center;
  max-width: 400px;
  margin: 0 2rem;
}

.nav-items {
  display: flex;
  gap: 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  color: #6b7280;
  font-weight: 500;
  transition: all 0.2s ease;
}

.nav-item:hover {
  color: #059669;
  background: #f9fafb;
}

.nav-item.active {
  color: #059669;
  background: #ecfdf5;
}

.nav-icon {
  font-size: 1rem;
}

/* Header Actions */
.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.action-item {
  position: relative;
}

.logout-btn {
  color: #6b7280;
}

.logout-btn:hover {
  color: #dc2626;
}

/* User Profile */
.user-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar-circle {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-initial {
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1.2;
}

/* Mobile Navigation */
.mobile-navigation {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  z-index: 999;
}

.mobile-nav-content {
  padding: 1rem;
  max-width: 1400px;
  margin: 0 auto;
}

.mobile-nav-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.mobile-nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  text-decoration: none;
  color: #374151;
  transition: all 0.3s ease;
}

.mobile-nav-item:hover {
  background: rgba(243, 244, 246, 0.8);
}

.mobile-nav-item.active {
  background: linear-gradient(
    135deg,
    rgba(16, 185, 129, 0.1),
    rgba(5, 150, 105, 0.1)
  );
  border-left: 3px solid #10b981;
  color: #059669;
}

.mobile-nav-icon {
  width: 44px;
  height: 44px;
  background: rgba(107, 114, 128, 0.1);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
}

.mobile-nav-item.active .mobile-nav-icon {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.mobile-nav-content-item {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.mobile-nav-label {
  font-weight: 600;
  font-size: 1rem;
  line-height: 1.3;
}

.mobile-nav-description {
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.3;
}

.mobile-nav-arrow {
  color: #9ca3af;
  font-size: 0.875rem;
}

/* Mobile User Section */
.mobile-user-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 1.5rem;
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.mobile-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-avatar-initial {
  color: white;
  font-weight: 600;
  font-size: 1.125rem;
}

.mobile-user-details {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.mobile-user-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 1rem;
}

.mobile-user-role {
  font-size: 0.875rem;
  color: #6b7280;
}

.mobile-actions {
  display: flex;
  gap: 0.5rem;
}

.mobile-action-btn {
  flex: 1;
  justify-content: center;
}

/* Mobile Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
}

/* Utility Classes */
.mobile-only {
  display: none;
}

.desktop-only {
  display: flex;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .header-container {
    padding: 0.75rem 1rem;
  }

  .desktop-navigation {
    margin: 0 1rem;
  }
}

@media (max-width: 768px) {
  .desktop-navigation {
    display: none;
  }

  .mobile-only {
    display: flex;
  }

  .desktop-only {
    display: none;
  }

  .brand-subtitle {
    display: none;
  }

  .header-container {
    min-height: 60px;
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 0.5rem 1rem;
  }

  .logo-text {
    display: none;
  }

  .logo-icon {
    width: 36px;
    height: 36px;
    font-size: 16px;
  }
}

/* Transitions */
.mobile-slide-enter-active,
.mobile-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-slide-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.mobile-slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

/* Custom Menu Styles */
:deep(.user-menu .p-menu) {
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  border: 1px solid #e5e7eb;
  padding: 0.5rem;
}

:deep(.user-menu .p-menuitem-link) {
  border-radius: 8px;
  margin: 0.125rem 0;
}

.menu-item-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
}

.menu-item-icon {
  width: 16px;
  text-align: center;
}

.menu-item-label {
  flex: 1;
}

.menu-item-badge {
  margin-left: auto;
}

/* Focus Styles */
.logo-section:focus-visible,
.nav-item:focus-visible,
.mobile-nav-item:focus-visible {
  outline: 2px solid #10b981;
  outline-offset: 2px;
}
</style>
