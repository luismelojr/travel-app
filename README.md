# Travel API - Sistema de Gerenciamento de Pedidos de Viagem

API REST completa para gerenciamento de pedidos de viagem corporativa desenvolvida com Laravel 12, autenticação JWT, documentação Swagger e cobertura de testes de 90%+.

## 🚀 Features Principais

- ✅ **Autenticação JWT** - Sistema completo de login/registro com tokens seguros
- ✅ **CRUD Pedidos de Viagem** - Criar, listar, visualizar e gerenciar pedidos
- ✅ **Sistema de Autorização** - Roles (admin/user) com permissões específicas
- ✅ **Notificações por Email** - Notificação automática de status via filas
- ✅ **Documentação Swagger** - API totalmente documentada
- ✅ **Testes Completos** - 90%+ de cobertura com PHPUnit
- ✅ **Rate Limiting** - Proteção contra spam e ataques
- ✅ **Docker Ready** - Ambiente completo containerizado

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

| Serviço     | URL                                     | Descrição                      |
| ----------- | --------------------------------------- | ------------------------------ |
| **API**     | http://localhost:8000                   | API Laravel principal          |
| **Swagger** | http://localhost:8000/api/documentation | Documentação interativa da API |
| **Mailhog** | http://localhost:8025                   | Interface para testes de email |

## 🧱 Stack Tecnológica

### Backend

- **Laravel 12** - Framework PHP com PHP 8.2+
- **JWT Auth** - Autenticação via tokens JWT
- **MySQL 8.0** - Banco de dados relacional
- **Redis** - Cache e filas para jobs
- **L5-Swagger** - Documentação automática OpenAPI 3.0
- **PHPUnit** - Framework de testes com 90%+ cobertura
- **Mailhog** - Servidor SMTP para desenvolvimento

### DevOps

- **Docker & Docker Compose** - Ambiente containerizado
- **Xdebug** - Debug e cobertura de código
- **Supervisor** - Gerenciamento de workers de fila

## 🏗️ Arquitetura

O projeto segue padrões de **Clean Code** e **SOLID**:

```
app/
├── Contracts/           # Interfaces dos serviços
├── Enums/              # Enums para status e roles
├── Exceptions/         # Exceções customizadas
├── Helpers/            # Helpers para responses
├── Http/
│   ├── Controllers/    # Controllers da API v1
│   ├── Middleware/     # JWT e outros middlewares
│   ├── Requests/       # Form Requests com validação
│   └── Resources/      # API Resources (transformação)
├── Jobs/               # Jobs assíncronos (emails)
├── Mail/               # Classes de email
├── Models/             # Eloquent Models
├── Policies/           # Authorization policies
├── Rules/              # Custom validation rules
└── Services/           # Lógica de negócio
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
| `PATCH` | `/{id}/status` | Alterar status           | ✅   | Admin apenas                  |
| `PATCH` | `/{id}/cancel` | Cancelar pedido          | ✅   | Owner ou Admin                |

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

# Gerar documentação Swagger
docker-compose exec app php artisan l5-swagger:generate

# Executar filas (desenvolvimento)
docker-compose exec app php artisan queue:work

# Limpar caches
docker-compose exec app php artisan optimize:clear
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

### Sistema de Autenticação

- Registro de usuários com validação robusta
- Login com credenciais email/senha
- Tokens JWT com expiração configurável
- Refresh de tokens
- Middleware de autenticação customizado

### Gerenciamento de Pedidos

- Criação de pedidos com validação de datas
- Listagem com filtros (status, destino, datas)
- Visualização de pedidos individuais
- Sistema de autorização baseado em roles
- Cancelamento de pedidos com regras de negócio

### Sistema de Notificações

- Emails automáticos via filas Redis
- Notificação de mudanças de status
- Templates HTML responsivos
- Processamento assíncrono via Jobs

### Documentação e Testes

- Swagger/OpenAPI 3.0 completo
- Testes unitários e de integração
- Cobertura de código com relatórios HTML
- Factories e Seeders para desenvolvimento

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
- **PHP 8.2+** (se executar fora do Docker)

---

**API desenvolvida seguindo as melhores práticas de segurança, teste e documentação** 🚀
**Desenvolvido por [Luis Henrique](https://www.linkedin.com/in/luis-henrique-da-silva-melo-junior-416579155/)** 🛠️
