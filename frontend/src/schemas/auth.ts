import { z } from 'zod'

export const RegisterSchema = z.object({
  name: z.string().min(2, 'Nome deve ter pelo menos 2 caracteres'),
  email: z.string().email('Email inválido'),
  password: z.string()
    .min(8, 'Senha deve ter pelo menos 8 caracteres')
    .regex(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/, 
           'Senha deve conter ao menos: 1 maiúscula, 1 minúscula, 1 número e 1 caractere especial'),
  password_confirmation: z.string()
}).refine((data) => data.password === data.password_confirmation, {
  message: 'Senhas não coincidem',
  path: ['password_confirmation']
})

export const LoginSchema = z.object({
  email: z.string().email('Email inválido'),
  password: z.string().min(1, 'Senha é obrigatória')
})