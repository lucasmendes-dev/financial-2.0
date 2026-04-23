<p align="center">
  <h1 align="center">💰 Financial 2.0</h1>
  <p align="center">
    A robust RESTful API for personal investment portfolio management, built with <strong>Laravel 13</strong> and <strong>PHP 8.3+</strong>.
    <br />
    Track transactions, monitor positions, and get real-time market data from the Brazilian stock exchange (B3).
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-8892BF?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/Redis-latest-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis">
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Architecture](#-architecture)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [API Reference](#-api-reference)
- [Portfolio Filters](#-portfolio-filters)
- [Market Data Pipeline](#-market-data-pipeline)
- [Testing](#-testing)
- [Environment Variables](#-environment-variables)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🔭 Overview

**Financial 2.0** is a personal finance API designed to manage investment portfolios on the Brazilian stock market. It provides a complete workflow — from registering buy/sell transactions to automatically calculating positions and consolidating a real-time portfolio with profit/loss metrics.

### Key Features

| Feature | Description |
|---|---|
| 🔐 **Authentication** | Token-based auth via Laravel Sanctum (register, login, logout, refresh) |
| 📊 **Transaction Management** | Full CRUD for buy/sell transactions with automatic position recalculation |
| 📈 **Position Tracking** | Auto-managed positions with weighted average price calculation |
| 💹 **Real-Time Market Data** | Scheduled background jobs fetch live quotes from [brAPI](https://brapi.dev) |
| 🧮 **Portfolio Consolidation** | Aggregated view with profit/loss, daily change, and cost basis — all with precise `bcmath` arithmetic |
| 🔍 **Advanced Filtering** | Filter & sort portfolio by ticker, type, value, profit/loss, and more |
| 🛡️ **Authorization Policies** | Resource-level ownership enforcement ensures users only access their own data |
| ⚡ **Caching** | Redis-backed tagged caching for portfolio data |
| 🤖 **CI/CD** | GitHub Actions workflow for automated testing on push/PR |

---

## 🏗 Architecture

The project follows a **Service-Oriented Architecture** with clear separation of concerns:

```
Request → Controller → Service → Model / External Integration
                  ↓
           FormRequest (validation)
           Policy (authorization)
           Resource (response transformation)
```

### Design Patterns

| Pattern | Usage |
|---|---|
| **Service Layer** | Business logic encapsulated in dedicated service classes |
| **DTO (Data Transfer Object)** | Typed, immutable objects for inter-layer data transport |
| **Value Object** | `Money` class using `bcmath` with 6-digit internal precision |
| **Adapter (Strategy)** | `MarketDataAdapterInterface` allows swapping market data providers |
| **Filter** | Collection-based filters for portfolio with dynamic method dispatch |
| **Repository-like** | Services encapsulate persistence logic, keeping controllers thin |
| **Custom Casts** | `MoneyCast` transparently converts between DB decimals and `Money` objects |

### Entity Relationship

```
User
 ├── has many → Transaction
 ├── has many → Position
 │                └── belongs to → Asset
 │                                   └── has one → MarketData
 └── (portfolio = Position + MarketData aggregation)
```

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Runtime** | PHP 8.3+ / PHP-FPM 8.5 |
| **Framework** | Laravel 13 |
| **Auth** | Laravel Sanctum (token-based) |
| **Database** | MySQL 8.0 |
| **Cache / Queue / Session** | Redis |
| **Web Server** | Nginx (Alpine) |
| **Mail** | MailHog (dev) |
| **DB Admin** | phpMyAdmin |
| **CI** | GitHub Actions |
| **Containerization** | Docker + Docker Compose |
| **Market Data** | [brAPI](https://brapi.dev) (Brazilian stock market API) |

---

## 🚀 Getting Started

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)
- A free API token from [brAPI](https://brapi.dev)

### 1. Clone the repository

```bash
git clone https://github.com/lucasmendes-dev/financial-2.0.git
cd financial-2.0
```

### 2. Configure the environment

```bash
cp .env.example .env
```

Open `.env` and set your brAPI credentials:

```dotenv
BRAPI_URL=https://brapi.dev/api/quote/
BRAPI_TOKEN=your_brapi_token_here
```

> **Note:** The default `.env.example` is pre-configured for the Docker network (MySQL host = `db`, Redis host = `redis`, etc.). No changes needed for local Docker setup.

### 3. Start the containers

```bash
docker compose up -d --build
```

### 4. Install dependencies & initialize

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5. Access the services

| Service | URL |
|---|---|
| 🌐 **API** | http://localhost:8888 |
| 📬 **MailHog** | http://localhost:8025 |
| 🗄️ **phpMyAdmin** | http://localhost:8080 |

---

## 📖 API Reference

All endpoints are prefixed with `/api/v1`. Protected routes require a Bearer token via `Authorization` header.

### Authentication

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/auth/register` | Register a new user | ❌ |
| `POST` | `/auth/login` | Login and receive a token | ❌ |
| `POST` | `/auth/logout` | Revoke current token | ✅ |
| `POST` | `/auth/refresh` | Refresh the token | ✅ |
| `GET` | `/auth/me` | Get authenticated user info | ✅ |

### Assets

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/assets` | List all assets | ✅ |
| `GET` | `/assets/{asset}` | Show a specific asset | ✅ |

### Transactions

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/transactions` | List user transactions | ✅ |
| `POST` | `/transactions` | Create a new transaction | ✅ |
| `GET` | `/transactions/{transaction}` | Show transaction details | ✅ |
| `PUT` | `/transactions/{transaction}` | Update a transaction | ✅ |
| `DELETE` | `/transactions/{transaction}` | Delete a transaction | ✅ |

#### Create Transaction — Request Body

```json
{
  "ticker": "PETR4",
  "type": "buy",
  "quantity": 100,
  "price_per_asset": 38.50
}
```

> When a transaction is created, updated, or deleted, the associated **Position** is automatically recalculated (quantity & weighted average price).

### Positions

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/positions` | List user positions | ✅ |
| `GET` | `/positions/{position}` | Show a specific position | ✅ |
| `DELETE` | `/positions/{position}` | Delete a position | ✅ |

### Market Data

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/update-market-data` | Manually trigger market data update | ✅ |

### Portfolio

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/portfolio` | Consolidated portfolio with real-time metrics | ✅ |

#### Portfolio Response Example

```json
{
  "data": [
    {
      "ticker": "PETR4",
      "type": "stock",
      "quantity": 100,
      "avg_price": "38.50",
      "current_price": "42.15",
      "total_cost": "3850.00",
      "total_value": "4215.00",
      "total_profit_loss_percent": "9.48",
      "total_profit_loss_value": "365.00",
      "daily_change_percent": 1.25,
      "daily_change_value": "125.00",
      "logo_url": "https://s3-symbol-logo.tradingview.com/..."
    }
  ]
}
```

---

## 🔍 Portfolio Filters

The portfolio endpoint supports powerful query-string filters:

```
GET /api/v1/portfolio?ticker=PETR4&total_value_gt=1000&order_by=-total_profit_loss_value
```

| Filter | Type | Description |
|---|---|---|
| `ticker` | `string` | Filter by exact ticker symbol |
| `type` | `string` | Filter by asset type (e.g., `stock`, `fii`) |
| `quantity_gt` | `number` | Quantity greater than |
| `quantity_lt` | `number` | Quantity less than |
| `total_cost_gt` | `number` | Total cost greater than |
| `total_cost_lt` | `number` | Total cost less than |
| `total_value_gt` | `number` | Total value greater than |
| `total_value_lt` | `number` | Total value less than |
| `with_profit_value` | `flag` | Only positions with positive P/L value |
| `with_loss_value` | `flag` | Only positions with negative P/L value |
| `with_profit_percent` | `flag` | Only positions with positive P/L percent |
| `with_loss_percent` | `flag` | Only positions with negative P/L percent |
| `with_daily_profit_percent` | `flag` | Only positions with positive daily change |
| `with_daily_loss_percent` | `flag` | Only positions with negative daily change |
| `order_by` | `string` | Sort by any field (prefix with `-` for descending) |
| `order` | `string` | Sort direction: `asc` or `desc` |

---

## 📡 Market Data Pipeline

Market data is fetched automatically via a scheduled job pipeline:

```
Schedule (weekdays, 10:30–17:30 BRT, every 30 min)
  └── FetchMarketDataJob (dispatches one job per asset)
        └── FetchSingleMarketDataJob (per ticker)
              └── MarketDataService
                    └── BrApiFreeAdapter → brAPI HTTP call
```

- **Adapter pattern**: Swap between `BrApiFreeAdapter` and `BrApiPaidAdapter` via config/binding
- **Queue**: Jobs are dispatched to the `market-data` Redis queue with staggered delays (2s between each)
- **Error handling**: Failures are logged to both Laravel logs and the `market_data_logs` table
- **No overlapping**: `withoutOverlapping()` prevents concurrent job runs

### Manual Trigger

```bash
# Via API
curl -X POST http://localhost:8888/api/v1/update-market-data \
  -H "Authorization: Bearer {token}"

# Via Artisan
docker compose exec app php artisan market-data:fetch
```

---

## 🧪 Testing

The project includes both **Unit** and **Feature** tests covering services, filters, controllers, jobs, and more.

```bash
# Run all tests
docker compose exec app php artisan test

# Run with coverage
docker compose exec app php artisan test --coverage

# Run a specific test suite
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature
```

### CI Pipeline

Tests run automatically on GitHub Actions for every push to `main`/`develop` and on pull requests to `main`. The CI uses SQLite in-memory for fast execution.

---

## ⚙️ Environment Variables

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `Financial` |
| `APP_URL` | Application URL | `http://localhost:8888` |
| `DB_HOST` | Database host | `db` |
| `DB_DATABASE` | Database name | `financial` |
| `DB_USERNAME` | Database username | `root` |
| `DB_PASSWORD` | Database password | `root` |
| `CACHE_DRIVER` | Cache driver | `redis` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |
| `REDIS_HOST` | Redis host | `redis` |
| `BRAPI_URL` | brAPI base URL | `https://brapi.dev/api/quote/` |
| `BRAPI_TOKEN` | brAPI authentication token | — |
| `BRAPI_PROVIDER` | Market data provider (`free` / `paid`) | `free` |

---

## 📜 License

This project is open-source and available under the [MIT License](LICENSE).

---

