# Test Exercise

Requirements:
1. pgsql
2. php 8.3

## Installation

### Clone project
```
git clone git@github.com:azakhozhiy/test-accounts-exchange.git
```

### Copy .env.example
```
cat .env.example >> .env
```

### Set your database config to .env
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=
```

### Run migrations

```
php artisan migrate:fresh --seed
```

## API endpoints

### Auth 
- /api/v1/auth/login POST - Authentication
- /api/v1/auth/logout POST - Logout
- /api/v1/auth/refresh POST - Refresh JWT token
- /api/v1/auth/me GET - Get current user info

### Accounts
- /api/v1/accounts GET - Get current user's bank accounts

### Orders
- /api/v1/orders GET - Get available exchange orders
- /api/v1/orders POST - Create new exchange order
- /api/v1/orders/{uuid}/accept POST - Accept exchange order
- /api/v1/orders/{uuid}/cancel POST - Cancel exchange order
