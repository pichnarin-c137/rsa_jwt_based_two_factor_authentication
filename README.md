# RSA JWT Authentication System

A Laravel 12 application implementing secure RSA-based JWT authentication with role-based access control (RBAC) and two-factor authentication (2FA).

## Features

- ✅ **RSA-based JWT Authentication** - RS256 algorithm with 4096-bit RSA keys
- ✅ **Two-Factor Authentication (2FA)** - Email-based OTP with random 5-10 minute expiry
- ✅ **Role-Based Access Control (RBAC)** - Admin and user roles with middleware protection
- ✅ **Token Management** - Access tokens (1 day) and refresh tokens (30 days)
- ✅ **PostgreSQL with UUIDs** - Enhanced security with UUID primary keys
- ✅ **Separated Credential Storage** - User data separate from authentication credentials
- ✅ **Docker Support** - Complete Docker Compose setup with PostgreSQL
- ✅ **Postman Collection** - Ready-to-use API collection for team collaboration

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Authentication:** firebase/php-jwt (RS256)
- **Database:** PostgreSQL 16 (Docker), SQLite (local alternative)
- **Frontend:** Vite + TailwindCSS 4.0
- **Testing:** PHPUnit 11

## Quick Start

### Option 1: Docker (Recommended)

```bash
# Start containers
docker-compose up -d

# Backend available at http://localhost:1134
# PostgreSQL on localhost:1143
```

### Option 2: Local Development

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:generate-keys

# Setup database (SQLite)
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
composer dev
```

## Default Admin Account

- **Username:** `admin`
- **Password:** `Admin@123456`
- **Email:** `admin@rsajwt.local`

## API Testing

### Using Postman

1. **Import Collection:**
   - Open Postman
   - Import `RSA_JWT_Auth.postman_collection.json`
   - Import `RSA_JWT_Auth.postman_environment.json`

2. **Select Environment:**
   - Choose "RSA JWT Auth - Local"

3. **Test Flow:**
   - Login → Get OTP from logs → Verify OTP → Access protected endpoints

**See [POSTMAN_GUIDE.md](postman/v1/POSTMAN_GUIDE.md) for detailed instructions**

### Using cURL

```bash
# Login (Step 1)
curl -X POST http://localhost:1134/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin","password":"Admin@123456"}'

# Check logs for OTP
docker-compose logs backend | grep "OTP"

# Verify OTP (Step 2)
curl -X POST http://localhost:1134/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin","otp":"1234"}'

# Get Profile
curl -X GET http://localhost:1134/api/get-profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## API Endpoints

### Public Endpoints
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login (sends OTP)
- `POST /api/auth/verify-otp` - Verify OTP and get tokens
- `POST /api/auth/refresh-token` - Refresh access token

### Protected Endpoints
- `POST /api/auth/logout` - Logout and revoke token
- `GET /api/get-profile` - Get user profile

### Admin-Only Endpoints
- `POST /api/create-user` - Admin creates new user

**See [API_DOCUMENTATION.md](postman/v1/API_DOCUMENTATION.md) for complete API reference**

## Development Commands

```bash
# Start all services (server, queue, logs, vite)
composer dev

# Run tests
composer test

# Format code
vendor/bin/pint

# Generate RSA keys
php artisan jwt:generate-keys

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f backend
docker-compose logs -f postgres

# Access backend shell
docker-compose exec backend bash

# Access PostgreSQL
docker-compose exec postgres psql -U rsa_user -d rsa_jwt_auth
```

## Project Structure

```
app/
├── Console/Commands/     # Artisan commands (GenerateJwtKeys)
├── Http/
│   ├── Controllers/      # API controllers (AuthController, UserController)
│   └── Middleware/       # Custom middleware (JwtAuthenticate, AdminOnly)
├── Models/               # Eloquent models (User, Credential, RefreshToken, Role)
└── Services/             # Service classes (JwtService, OtpService)

database/
├── migrations/           # Database migrations
└── seeders/              # Database seeders

storage/
└── keys/                 # RSA keys (jwt_private.pem, jwt_public.pem)

resources/
├── css/                  # Stylesheets (TailwindCSS)
├── js/                   # JavaScript/frontend code
└── views/                # Blade templates

tests/
├── Feature/              # Feature tests
└── Unit/                 # Unit tests
```

## Security Features

- **Password Hashing:** bcrypt with 12 rounds
- **Token Security:** RS256 asymmetric encryption with 4096-bit RSA keys
- **Token Expiry:** Access tokens (1 day), Refresh tokens (30 days)
- **OTP Security:** 4-digit OTP with 5-10 minute random expiry
- **Account Suspension:** Admin capability to suspend users
- **Data Protection:** No password/OTP exposure in API responses
- **Password Requirements:** Minimum 8 chars with uppercase, lowercase, number, and special character

## Documentation

- **[CLAUDE.md](CLAUDE.md)** - Complete development guide and project overview
- **[API_DOCUMENTATION.md](postman/v1/API_DOCUMENTATION.md)** - API reference with examples
- **[POSTMAN_GUIDE.md](postman/v1/POSTMAN_GUIDE.md)** - Postman setup and team sharing guide

## Database Architecture

- **roles** - Role definitions (admin, user)
- **users** - User profile information
- **credentials** - Authentication data (username, email, password, OTP)
- **refresh_tokens** - JWT refresh token storage
- **sessions** - Session tracking
- **cache** - Cache storage
- **jobs** - Queue jobs

All tables use UUID primary keys for enhanced security.

## Environment Configuration

### Docker Mode (.env)
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=rsa_jwt_auth
DB_USERNAME=rsa_user
DB_PASSWORD=rsa_secure_pass_2026
```

### Local Mode (.env)
```env
DB_CONNECTION=sqlite
# Or use PostgreSQL with custom credentials
```

## Testing

```bash
# Run all tests
composer test

# Run specific test
php artisan test --filter=ExampleTest

# Run with coverage
php artisan test --coverage
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions:
1. Check documentation in `CLAUDE.md`, `API_DOCUMENTATION.md`, or `POSTMAN_GUIDE.md`
2. Review Docker logs: `docker-compose logs -f backend`
3. Check Laravel logs: `storage/logs/laravel.log`

---

Built with Laravel 12 and ❤️
