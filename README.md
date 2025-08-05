# 🚀 Travel App - Sistema Completo de Gerenciamento de Viagens

Sistema completo de gerenciamento de pedidos de viagem corporativa com **backend Laravel** e **frontend Vue.js**, incluindo painel administrativo, autenticação JWT, notificações por email e interface moderna e responsiva.

## ✨ Features Principais

### 🔐 Autenticação & Autorização

- ✅ **Sistema JWT Completo** - Login/registro seguro com tokens
- ✅ **Roles & Permissions** - Admin e usuários com permissões específicas
- ✅ **Middleware de Segurança** - Proteção de rotas e recursos

### 📱 Frontend Moderno

- ✅ **Interface Vue.js 3** - SPA moderna e responsiva
- ✅ **Design System** - PrimeVue com tema customizado
- ✅ **Painel Admin** - Gerenciamento completo para administradores
- ✅ **Dashboard Interativo** - Estatísticas e visões gerais

### 🎯 Gerenciamento de Viagens

- ✅ **CRUD Completo** - Criar, listar, visualizar e gerenciar pedidos
- ✅ **Sistema de Status** - Solicitado, Aprovado, Cancelado
- ✅ **Filtros Avançados** - Por status, destino, data, solicitante
- ✅ **Aprovação Admin** - Admins podem alterar status das solicitações

### 🔔 Notificações & Comunicação

- ✅ **Emails Automáticos** - Notificação de mudanças via filas
- ✅ **Templates Responsivos** - Emails HTML bem formatados
- ✅ **Processamento Assíncrono** - Jobs em background com Redis

### 🧪 Qualidade & Documentação

- ✅ **Testes 90%+** - Cobertura completa com PHPUnit
- ✅ **Documentação Swagger** - API totalmente documentada
- ✅ **Dados Demo** - Usuários e solicitações para teste
- ✅ **Docker Completo** - Ambiente containerizado

## 🚀 Quick Start

Execute um único comando para configurar todo o projeto:

```bash
# Antes de executar, certifique-se de ter dado permissão de execução ao script
# Para macOS/Linux:
sudo chmod +x setup.sh restart.sh
./setup.sh # Configura o ambiente Docker, instala dependências e cria o banco de dados
./restart.sh # Reinicia o ambiente Docker podendo limpar os volumes e recriar o banco de dados
```

## 📱 URLs Disponíveis

| Serviço      | URL                                     | Descrição                      |
| ------------ | --------------------------------------- | ------------------------------ |
| **Frontend** | http://localhost:3000                   | Interface Vue.js (SPA)         |
| **Backend**  | http://localhost:8000                   | API Laravel                    |
| **Swagger**  | http://localhost:8000/api/documentation | Documentação interativa da API |
| **Mailhog**  | http://localhost:8025                   | Interface para testes de email |

## 🔐 Credenciais de Demonstração

O sistema cria automaticamente usuários para teste:

| Perfil      | Email            | Senha    | Permissões                               |
| ----------- | ---------------- | -------- | ---------------------------------------- |
| **Admin**   | admin@travel.com | admin123 | Gerenciar todas as solicitações e status |
| **Usuário** | user@travel.com  | user123  | Criar e gerenciar próprias solicitações  |
| **Usuário** | joao@travel.com  | password | Criar e gerenciar próprias solicitações  |
| **Usuário** | maria@travel.com | password | Criar e gerenciar próprias solicitações  |

### 🎯 Como Testar as Funcionalidades Admin

1. **Acesse:** http://localhost:3000
2. **Login Admin:** `admin@travel.com` / `admin123`
3. **Funcionalidades disponíveis:**
   - Ver **todas** as solicitações (não apenas próprias)
   - **Atualizar status** das solicitações (aprovar/cancelar)
   - **Filtrar** e **pesquisar** solicitações
   - **Dashboard** com estatísticas gerais

## 🧱 Stack Tecnológica

### Frontend

- **Vue.js 3** - Framework JavaScript reativo
- **TypeScript** - Tipagem estática para JavaScript
- **Vite** - Build tool moderna e rápida
- **PrimeVue** - Biblioteca de componentes UI
- **Vue Router** - Roteamento SPA
- **Axios** - Cliente HTTP para APIs

### Backend

- **Laravel 12** - Framework PHP com PHP 8.2+
- **JWT Auth** - Autenticação via tokens JWT
- **MySQL 8.0** - Banco de dados relacional
- **Redis** - Cache e filas para jobs
- **L5-Swagger** - Documentação automática OpenAPI 3.0
- **PHPUnit** - Framework de testes com 90%+ cobertura
- **Mailhog** - Servidor SMTP para desenvolvimento

### DevOps

- **Docker & Docker Compose** - Ambiente containerizado completo
- **Nginx** - Servidor web para frontend
- **Xdebug** - Debug e cobertura de código

## 🏗️ Arquitetura

O projeto segue padrões de **Clean Code** e **SOLID** com separação clara entre frontend e backend:

### 🎨 Estrutura Frontend

```
frontend/
├── src/
│   ├── components/     # Componentes reutilizáveis
│   ├── composables/    # Lógica reativa compartilhada
│   ├── layouts/        # Templates de layout
│   ├── pages/          # Páginas/Views da aplicação
│   ├── router/         # Configuração de rotas
│   ├── services/       # Serviços de API (HTTP)
│   ├── types/          # Tipos TypeScript
│   └── utils/          # Utilitários e helpers
├── public/             # Assets estáticos
└── dist/               # Build de produção
```

### ⚙️ Estrutura Backend

```
backend/app/
├── Contracts/          # Interfaces dos serviços
├── Enums/             # Enums para status e roles
├── Exceptions/        # Exceções customizadas
├── Helpers/           # Helpers para responses
├── Http/
│   ├── Controllers/   # Controllers da API v1
│   ├── Middleware/    # JWT e outros middlewares
│   ├── Requests/      # Form Requests com validação
│   └── Resources/     # API Resources (transformação)
├── Jobs/              # Jobs assíncronos (emails)
├── Mail/              # Classes de email
├── Models/            # Eloquent Models
├── Policies/          # Authorization policies
├── Rules/             # Custom validation rules
└── Services/          # Lógica de negócio
```

## 📝 Endpoints da API

### Autenticação (`/api/v1/auth`)

| Método | Endpoint    | Descrição               | Auth |
| ------ | ----------- | ----------------------- | ---- |
| `POST` | `/register` | Registrar novo usuário  | ❌   |
| `POST` | `/login`    | Fazer login             | ❌   |
| `POST` | `/refresh`  | Renovar token           | ✅   |
| `POST` | `/logout`   | Fazer logout            | ✅   |
| `GET`  | `/me`       | Dados do usuário logado | ✅   |

### Pedidos de Viagem (`/api/v1/travel-requests`)

| Método  | Endpoint       | Descrição                | Auth | Role                          |
| ------- | -------------- | ------------------------ | ---- | ----------------------------- |
| `POST`  | `/`            | Criar pedido             | ✅   | Qualquer                      |
| `GET`   | `/`            | Listar pedidos           | ✅   | User: próprios / Admin: todos |
| `GET`   | `/{id}`        | Buscar pedido específico | ✅   | User: próprios / Admin: todos |
| `GET`   | `/stats`       | Estatísticas de pedidos  | ✅   | Qualquer                      |
| `PATCH` | `/{id}/status` | Alterar status           | ✅   | **Admin apenas**              |
| `PATCH` | `/{id}/cancel` | Cancelar pedido          | ✅   | Owner ou Admin                |

### 🎯 Funcionalidades Admin (Destaque)

O sistema possui funcionalidades específicas para administradores:

- **Visualizar todas as solicitações** (não apenas próprias)
- **Atualizar status** das solicitações (aprovado/cancelado/solicitado)
- **Dashboard administrativo** com visão geral do sistema
- **Gerenciamento completo** de pedidos de outros usuários

### Rate Limiting

- **Auth endpoints**: 5 requests/minuto
- **Demais endpoints**: 60 requests/minuto

## 🧪 Testes

Cobertura atual: **90%+** com PHPUnit

```bash
# Executar todos os testes
docker-compose exec app php artisan test

# Executar com cobertura HTML
docker-compose exec app ./scripts/test-coverage.sh

# Ver relatório de cobertura
open backend/storage/coverage-html/index.html
```

### Categorias de Teste

- **Unit Tests**: Models, Services, Enums, Exceptions, Helpers
- **Feature Tests**: Controllers, Middleware, Requests
- **Integration Tests**: Jobs, Mail, Policies

## 🛠️ Comandos Úteis

### Docker

```bash
# Subir ambiente
docker-compose up -d

# Parar ambiente
docker-compose down

# Acessar container
docker-compose exec app bash

# Ver logs
docker-compose logs app -f
```

### Laravel

```bash
# Migrations
docker-compose exec app php artisan migrate

# Executar seeders (criar dados demo)
docker-compose exec app php artisan db:seed

# Gerar documentação Swagger
docker-compose exec app php artisan l5-swagger:generate

# Executar filas (desenvolvimento)
docker-compose exec app php artisan queue:work

# Limpar caches
docker-compose exec app php artisan optimize:clear
```

### Frontend

```bash
# Instalar dependências
cd frontend && npm install

# Desenvolvimento
cd frontend && npm run dev

# Build para produção
cd frontend && npm run build

# Preview da build
cd frontend && npm run preview
```

## 📊 Configuração do Ambiente

### Banco de Dados

- **Host**: mysql (Docker) / localhost (local)
- **Porta**: 3306
- **Database**: travel_db
- **Root Password**: root_password

### Redis

- **Host**: redis (Docker) / localhost (local)
- **Porta**: 6380 (externa) / 6379 (interna)

### Email (Mailhog)

- **SMTP Host**: mailhog
- **SMTP Port**: 1025
- **Web Interface**: http://localhost:8025

### Variáveis de Ambiente

```env
# JWT
JWT_SECRET=your-secret-key
JWT_TTL=60

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=travel_db

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Mail
MAIL_HOST=mailhog
MAIL_PORT=1025
```

## 🔧 Funcionalidades Implementadas

### 🎨 Frontend (Vue.js)

- **Interface Responsiva** - Design adaptativo para desktop e mobile
- **Autenticação JWT** - Login/logout com persistência de sessão
- **Dashboard Interativo** - Estatísticas e gráficos em tempo real
- **CRUD de Viagens** - Criar, editar, visualizar e gerenciar pedidos
- **Painel Admin** - Interface específica para administradores
- **Filtros Avançados** - Busca por status, destino, datas
- **Notificações Toast** - Feedback visual para ações do usuário
- **Estados de Loading** - Skeletons e spinners para melhor UX

### ⚙️ Backend (Laravel)

- **API RESTful** - Endpoints bem estruturados e documentados
- **Autenticação JWT** - Sistema seguro de tokens
- **Autorização RBAC** - Roles e permissões (admin/user)
- **Validação Robusta** - Form Requests com regras customizadas
- **Sistema de Notificações** - Emails automáticos via filas
- **Tratamento de Erros** - Exceções customizadas e responses padronizados
- **Rate Limiting** - Proteção contra spam e ataques
- **Cache Redis** - Otimização de performance

### 🔔 Sistema de Notificações

- **Emails Automáticos** - Notificação de mudanças de status
- **Templates Responsivos** - HTML bem formatados para todos os dispositivos
- **Processamento Assíncrono** - Jobs em background com Redis
- **Mailhog Integration** - Testes de email em desenvolvimento

### 📊 Dados e Relatórios

- **Seeders Inteligentes** - Dados realistas para demonstração
- **Estatísticas** - Dashboard com métricas de pedidos
- **Filtros Complexos** - Busca por múltiplos critérios
- **Paginação** - Listagens otimizadas para grandes volumes

### 🧪 Qualidade e Testes

- **Testes Automatizados** - 90%+ de cobertura com PHPUnit
- **Documentação Swagger** - API totalmente documentada
- **Docker Completo** - Ambiente isolado e reproduzível
- **CI/CD Ready** - Estrutura preparada para integração contínua

## 🚨 Troubleshooting

### Portas em Uso

```bash
# Verificar portas ocupadas
lsof -i :8000
lsof -i :3306

# Parar e reiniciar
docker-compose down && docker-compose up -d
```

### Problemas de Permissão

```bash
# Linux/WSL
sudo chown -R $USER:$USER backend/storage backend/bootstrap/cache

# Recriar containers
docker-compose down -v
docker-compose up -d --build
```

### Reset Completo

```bash
# Limpar tudo
docker-compose down -v
docker system prune -f
./setup.sh
```

## 📋 Requisitos

- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Git**
- **Node.js 18+** (se executar frontend fora do Docker)
- **PHP 8.2+** (se executar backend fora do Docker)

## 🤝 Contribuições

**Desenvolvido por [Luis Henrique](https://www.linkedin.com/in/luis-henrique-da-silva-melo-junior-416579155/)** 👨‍💻
