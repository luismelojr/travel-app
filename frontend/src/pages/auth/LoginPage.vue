<template>
  <AuthLayout>
    <div class="auth-form">
      <div class="auth-tabs">
        <button type="button" class="tab-button active">Entrar</button>
        <router-link to="/register" class="tab-button">
          Criar conta
        </router-link>
      </div>

      <form @submit="onSubmit" class="form-content">
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
            :feedback="false"
            class="w-full"
          />
          <small v-if="errors.password" class="field-error">{{
            errors.password
          }}</small>
        </div>

        <div class="form-options">
          <label class="checkbox-wrapper">
            <input type="checkbox" class="checkbox-input" />
            <span class="checkbox-label">Lembrar de mim</span>
          </label>
          <button type="button" class="forgot-password">
            Esqueceu a senha?
          </button>
        </div>

        <Button
          type="submit"
          label="Entrar"
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
import { LoginSchema } from "@/schemas/auth";
import { authService } from "@/services/auth";
import { errorHandler } from "@/utils/errorHandler";

const router = useRouter();
const isLoading = ref(false);

// Usando refs simples em vez do useForm quebrado
const email = ref("");
const password = ref("");
const errors = ref<Record<string, string>>({});

// Função de validação manual
const validateForm = () => {
  const data = {
    email: email.value,
    password: password.value,
  };

  try {
    LoginSchema.parse(data);
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
    email: email.value,
    password: password.value,
  };

  isLoading.value = true;

  try {
    const response = await authService.login(values);

    if (response?.success) {
      errorHandler.showSuccessNotification("Login realizado com sucesso!");
      await router.push("/dashboard");
    } else {
      errorHandler.showErrorNotification(
        "Credenciais inválidas. Tente novamente."
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
      if (error.response?.status === 401) {
        errorHandler.showErrorNotification("Email ou senha incorretos.");
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
          "Erro ao fazer login. Tente novamente."
        );
      }
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style src="@/styles/auth.css" scoped></style>
