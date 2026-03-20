# Financial 2.0
Building...

## 🐳 Executando com Docker

### 1️⃣ Clonar repositório

```bash
git clone https://github.com/lucasmendes-dev/financial-2.0.git
cd financial-2.0
```

### 2️⃣ Copiar arquivo de ambiente

```bash
cp .env.example .env
```

Certifique que o arquivo `.env` tenha pelo menos os seguintes parâmetros:
```dosini

APP_NAME=Financial
APP_URL=http://localhost:8888

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=financial
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="${APP_NAME}"

```

---

### 3️⃣ Subir os containers

```bash
docker compose up -d --build
```

## 4️⃣ Instalar dependências

```bash
docker compose exec app composer install
```

Gere as chaves e rode as migrations (com seeder)

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### Mailhog disponível em:

http://localhost:8025

Acessar o projeto http://localhost:8888
