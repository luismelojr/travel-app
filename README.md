# Travel App - Sistema de Gerenciamento de Pedidos de Viagem Corporativa

Sistema completo para gerenciar pedidos de viagem corporativa desenvolvido com Laravel 12, Docker e as melhores práticas de desenvolvimento.

## 🚀 Quick Start

Execute um único comando para configurar todo o projeto:

```bash
./setup.sh
```

O script irá:
- ✅ Verificar dependências do sistema (Docker, Docker Compose)
- ✅ Construir e subir todos os containers
- ✅ Instalar Laravel 12 com `laravel new`
- ✅ Configurar banco de dados MySQL
- ✅ Instalar e configurar Redis para filas
- ✅ Configurar Mailhog para testes de email
- ✅ Instalar Swagger, Pest, Horizon
- ✅ Executar migrations
- ✅ Verificar se tudo está funcionando

## 📱 URLs Disponíveis

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **API** | http://localhost:8000 | API Laravel principal |
| **Swagger** | http://localhost:8000/api/documentation | Documentação da API |
| **Horizon** | http://localhost:8000/horizon | Dashboard de filas |
| **Mailhog** | http://localhost:8025 | Interface de email para testes |
| **Frontend** | http://localhost:3000 | Interface do usuário (placeholder) |

## 🧱 Stack Tecnológica

### Backend
- **Laravel 12** - Framework PHP moderno
- **MySQL 8.0** - Banco de dados relacional
- **Redis** - Cache e gerenciamento de filas
- **Swagger (L5-Swagger)** - Documentação automática da API
- **Pest** - Framework de testes moderno
- **Laravel Horizon** - Dashboard para filas Redis

### DevOps & Ferramentas
- **Docker & Docker Compose** - Containerização
- **Mailhog** - Servidor de email para desenvolvimento
- **Supervisor** - Gerenciamento de processos

## 🏗️ Arquitetura

O projeto segue os princípios de **Arquitetura Limpa**:

```
app/
├── Http/
│   ├── Controllers/     # Controladores da API
│   ├── Requests/        # Validação de requests
│   └── Resources/       # Transformação de dados (HATEOAS)
├── Services/            # Regras de negócio
├── Repositories/        # Acesso aos dados
├── DTOs/               # Data Transfer Objects
├── Exceptions/         # Exceções customizadas
└── Models/             # Eloquent Models
```

## 🛠️ Comandos Úteis

### Docker
```bash
# Subir todos os containers
docker-compose up -d

# Parar containers
docker-compose down

# Acessar container da aplicação
docker-compose exec app bash

# Ver logs da aplicação
docker-compose logs app -f
```

### Laravel
```bash
# Executar migrations
docker-compose exec app php artisan migrate

# Executar testes com Pest
docker-compose exec app ./vendor/bin/pest

# Gerar documentação Swagger
docker-compose exec app php artisan l5-swagger:generate

# Monitorar filas com Horizon
docker-compose exec app php artisan horizon
```

## 🧪 Testes

O projeto usa **Pest** como framework de testes:

```bash
# Executar todos os testes
docker-compose exec app ./vendor/bin/pest

# Executar testes com cobertura
docker-compose exec app ./vendor/bin/pest --coverage

# Executar testes específicos
docker-compose exec app ./vendor/bin/pest tests/Feature/TravelRequestTest.php
```

## 📊 Monitoramento

### Laravel Horizon
- Dashboard: http://localhost:8000/horizon
- Monitora filas Redis em tempo real
- Estatísticas de processamento
- Controle de workers

### Logs
```bash
# Ver logs da aplicação
docker-compose logs app

# Ver logs do MySQL
docker-compose logs mysql

# Ver logs do Redis
docker-compose logs redis
```

## 🔧 Configuração Avançada

### Banco de Dados
- **Host**: mysql (dentro do Docker)
- **Porta**: 3306
- **Database**: travel_db
- **Usuário**: travel_user
- **Senha**: travel_password

### Redis
- **Host**: redis (dentro do Docker)
- **Porta**: 6379

### Email (Mailhog)
- **SMTP Host**: mailhog
- **SMTP Port**: 1025
- **Web Interface**: http://localhost:8025

## 📝 Desenvolvimento

### Adicionando Nova Feature
1. Criar migration: `php artisan make:migration create_table_name`
2. Criar model: `php artisan make:model ModelName`
3. Criar controller: `php artisan make:controller ModelController`
4. Criar testes: `./vendor/bin/pest --init` (já configurado)
5. Documentar na API com Swagger

### Padrões de Commit
Seguimos **Conventional Commits**:
- `feat:` nova funcionalidade
- `fix:` correção de bug
- `docs:` documentação
- `test:` testes
- `refactor:` refatoração

## 🚨 Troubleshooting

### Porta em uso
Se a porta 8000 já estiver em uso:
```bash
# Verificar processos na porta
lsof -i :8000

# Parar containers e tentar novamente
docker-compose down
./setup.sh
```

### Problemas de permissão
```bash
# Corrigir permissões (Linux/WSL)
sudo chown -R $USER:$USER .
```

### Reset completo
```bash
# Remover tudo e começar do zero
docker-compose down -v
docker system prune -f
./setup.sh
```

## 📋 Requisitos do Sistema

- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Git**
- **Composer** (opcional, usado pelo setup.sh se disponível)

## 🎯 Próximos Passos

1. ✅ Setup inicial completo
2. 🔄 Implementar autenticação (JWT/Sanctum)
3. 🔄 Criar CRUDs para pedidos de viagem
4. 🔄 Sistema de aprovação workflow
5. 🔄 Notificações via email/fila
6. 🔄 Relatórios e dashboards
7. 🔄 Testes de integração completos

---

**Desenvolvido com ❤️ usando Laravel 12 e as melhores práticas de desenvolvimento**