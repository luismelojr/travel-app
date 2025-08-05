<template>
  <AuthLayout>
    <div class="auth-form">
      <div class="auth-tabs">
        <router-link to="/login" class="tab-button"> Entrar </router-link>
        <button type="button" class="tab-button active">Criar conta</button>
      </div>

      <form @submit="onSubmit" class="form-content">
        <div class="form-field">
          <label for="name" class="field-label">Nome Completo</label>
          <InputText
            id="name"
            v-model="name"
            placeholder="Digite seu nome completo"
            :class="{ 'p-invalid': errors.name }"
            class="w-full"
          />
          <small v-if="errors.name" class="field-error">{{
            errors.name
          }}</small>
        </div>

        <div class="form-field">
          <label for="email" class="field-label">Email</label>
          <InputText
            id="email"
            v-model="email"
            type="email"
            placeholder="Digite seu email"
            :class="{ 'p-invalid': errors.email }"
            class="w-full"
          />
          <small v-if="errors.email" class="field-error">{{
            errors.email
          }}</small>
        </div>

        <div class="form-field">
          <label for="password" class="field-label">Senha</label>
          <Password
            id="password"
            v-model="password"
            placeholder="Digite sua senha"
            :invalid="!!errors.password"
            toggleMask
            class="w-full"
          />
          <small v-if="errors.password" class="field-error">{{
            errors.password
          }}</small>
          <small class="field-hint">
            Mínimo 8 caracteres com: maiúscula, minúscula, número e símbolo
          </small>
        </div>

        <div class="form-field">
          <label for="password_confirmation" class="field-label"
            >Confirmar Senha</label
          >
          <Password
            id="password_confirmation"
            v-model="password_confirmation"
            placeholder="Confirme sua senha"
            :invalid="!!errors.password_confirmation"
            toggleMask
            class="w-full"
          />
          <small v-if="errors.password_confirmation" class="field-error">
            {{ errors.password_confirmation }}
          </small>
        </div>

        <div class="terms-checkbox">
          <label class="checkbox-wrapper terms">
            <input type="checkbox" class="checkbox-input terms" required />
            <span class="checkbox-label terms">
              Aceito os
              <button type="button" class="terms-link">Termos de Uso</button> e
              <button type="button" class="terms-link">
                Política de Privacidade
              </button>
            </span>
          </label>
        </div>

        <Button
          type="submit"
          label="Criar Conta"
          :loading="isLoading"
          class="w-full submit-button"
          size="large"
        />
      </form>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import Button from "primevue/button";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { RegisterSchema } from "@/schemas/auth";
import { authService } from "@/services/auth";
import { errorHandler } from "@/utils/errorHandler";

const router = useRouter();
const isLoading = ref(false);

// Usando refs simples em vez do useForm quebrado
const name = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const errors = ref<Record<string, string>>({});

// Função de validação manual
const validateForm = () => {
  const data = {
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: password_confirmation.value,
  };

  try {
    RegisterSchema.parse(data);
    errors.value = {};
    return true;
  } catch (error: any) {
    const newErrors: Record<string, string> = {};
    const issues = error.errors || error.issues || [];

    issues.forEach((err: any) => {
      const path = err.path[0] as string;
      if (path) {
        newErrors[path] = err.message;
      }
    });

    errors.value = newErrors;
    return false;
  }
};

const onSubmit = async (event: Event) => {
  event.preventDefault();

  // Validar primeiro
  if (!validateForm()) {
    return;
  }

  const values = {
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: password_confirmation.value,
  };
  isLoading.value = true;

  try {
    const response = await authService.register(values);

    if (response?.success) {
      errorHandler.showSuccessNotification("Conta criada com sucesso!");
      await router.push("/dashboard");
    } else {
      errorHandler.showErrorNotification(
        "Falha ao criar conta. Tente novamente."
      );
    }
  } catch (error: any) {
    const validationErrors = errorHandler.getValidationErrors(error);

    if (validationErrors) {
      // Mapear erros de validação para os campos
      Object.keys(validationErrors).forEach((field) => {
        if (validationErrors[field] && validationErrors[field].length > 0) {
          errors.value[field] = validationErrors[field][0];
        }
      });
    } else {
      // Para outros tipos de erro, mostrar notificação específica
      if (error.response?.status === 409) {
        errorHandler.showErrorNotification(
          "Email já está em uso. Tente outro email."
        );
      } else if (error.response?.status === 422) {
        errorHandler.showErrorNotification(
          "Dados inválidos. Verifique os campos."
        );
      } else if (error.code === "NETWORK_ERROR" || !error.response) {
        errorHandler.showErrorNotification(
          "Erro de conexão. Verifique sua internet."
        );
      } else {
        errorHandler.showErrorNotification(
          "Erro ao criar conta. Tente novamente."
        );
      }
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style src="@/styles/auth.css" scoped>
.terms-checkbox {
  margin: 0.5rem 0;
}
</style>
