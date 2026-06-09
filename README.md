# 💰 FinanceTracker

Sistema de gestão financeira **multi-tenant** desenvolvido em Laravel, com isolamento de dados por usuário, controle de despesas, relatórios visuais e ambiente totalmente containerizado com Docker.

![Laravel](https://img.shields.io/badge/Laravel-11-%23FF2D20.svg?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-%23777BB4.svg?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg?style=flat&logo=mysql&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-%2338B2AC.svg?style=flat&logo=tailwind-css&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-%230db7ed.svg?style=flat&logo=docker&logoColor=white)
![Docker Hub](https://img.shields.io/badge/Docker_Hub-adrielferreira%2Ffinance--tracker-%232496ED.svg?style=flat&logo=docker&logoColor=white)

## ✨ Funcionalidades

- Multi-tenant com isolamento de dados por usuário
- Gestão completa de despesas
- Categorias personalizadas
- Métodos de pagamento
- Relatórios com gráficos (Chart.js)
- Painel administrativo

## 🛠️ Tecnologias

- **Back-end:** Laravel 11, PHP 8.2
- **Banco de dados:** MySQL 8.0
- **Front-end:** Tailwind CSS, Alpine.js, Chart.js
- **Build:** Vite
- **Infra:** Docker e Docker Compose
- **Testes e CI:** PHPUnit e GitHub Actions

## 📸 Telas

> Adicione aqui prints das principais telas (dashboard, relatórios, cadastro de despesas).
>
> Exemplo: `![Dashboard](docs/dashboard.png)`

## 🚀 Como rodar o projeto

### Opção 1: com Docker (recomendado)

A imagem já está publicada no Docker Hub:

```bash
docker pull adrielferreira/finance-tracker:latest
```

Para subir o projeto completo com o banco:

```bash
git clone https://github.com/AdrielFerr/finance-tracker.git
cd finance-tracker

cp .env.example .env
docker compose up -d

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

A aplicação ficará disponível em `http://localhost` (ajuste a porta conforme o seu `docker-compose.yml`).

### Opção 2: ambiente local

Pré-requisitos: PHP 8.2, Composer, Node.js e MySQL 8.0.

```bash
git clone https://github.com/AdrielFerr/finance-tracker.git
cd finance-tracker

cp .env.example .env
composer install
npm install

php artisan key:generate
php artisan migrate --seed
npm run dev

php artisan serve
```

Acesse `http://localhost:8000`.

## ⚙️ Variáveis de ambiente

Configure o banco no arquivo `.env` antes de rodar as migrations:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=root
DB_PASSWORD=
```

## 🧪 Testes

```bash
php artisan test
```

## 🔗 Links

- **Docker Hub:** https://hub.docker.com/r/adrielferreira/finance-tracker

## 👨‍💻 Autor

**Adriel Ferreira**

[![LinkedIn](https://img.shields.io/badge/LinkedIn-%230077B5.svg?style=flat&logo=linkedin&logoColor=white)](https://linkedin.com/in/adriel-ferreira-a5286a215)
[![GitHub](https://img.shields.io/badge/GitHub-%23121011.svg?style=flat&logo=github&logoColor=white)](https://github.com/AdrielFerr)
