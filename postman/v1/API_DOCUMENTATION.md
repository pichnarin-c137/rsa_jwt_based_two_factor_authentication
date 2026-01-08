# RSA JWT Authentication API Documentation

## Base URL
```
http://localhost:1134/api
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <access_token>
```

## Endpoints

### 1. Register User
**POST** `/auth/register`

Create a new user account (admin creates, OTP sent via email).

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "dob": "1995-05-15",
  "address": "123 Main Street",
  "gender": "male",
  "nationality": "USA",
  "email": "john@example.com",
  "username": "johndoe",
  "phone_number": "+19876543210",
  "password": "John@123456",
  "password_confirmation": "John@123456",
  "role": "user"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully. OTP sent to email.",
  "data": {
    "user_id": "uuid",
    "email": "john@example.com"
  }
}
```

### 2. Login - Step 1
**POST** `/auth/login`

Authenticate with username/email and password, triggers OTP.

**Request Body:**
```json
{
  "identifier": "johndoe",
  "password": "John@123456"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OTP sent to your email",
  "data": {
    "email": "john@example.com",
    "next_step": "verify_otp"
  }
}
```

### 3. Verify OTP - Step 2
**POST** `/auth/verify-otp`

Verify OTP and receive JWT tokens.

**Request Body:**
```json
{
  "identifier": "johndoe",
  "otp": "1234"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": "uuid",
      "first_name": "John",
      "last_name": "Doe",
      "dob": "1995-05-15",
      "address": "123 Main Street",
      "gender": "male",
      "nationality": "USA",
      "role": "user",
      "email": "john@example.com",
      "username": "johndoe",
      "phone_number": "+19876543210"
    }
  }
}
```

### 4. Refresh Token
**POST** `/auth/refresh-token`

Renew access token using refresh token.

**Request Body:**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

### 5. Logout
**POST** `/auth/logout` 🔒

Revoke refresh token.

**Request Body:**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 6. Get Profile
**GET** `/get-profile` 🔒

Get logged-in user's profile.

**Headers:**
```
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "dob": "1995-05-15",
    "address": "123 Main Street",
    "gender": "male",
    "nationality": "USA",
    "is_suspended": false,
    "role": "user",
    "email": "john@example.com",
    "username": "johndoe",
    "phone_number": "+19876543210",
    "created_at": "2026-01-08T10:00:00Z"
  }
}
```

### 7. Create User (Admin Only)
**POST** `/create-user` 🔒 👑

Admin creates new user.

**Headers:**
```
Authorization: Bearer <admin_access_token>
```

**Request Body:** Same as `/auth/register`

**Response (201):** Same as `/auth/register`

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Error description"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized - Invalid credentials"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Forbidden - Admin access required"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Security Notes

- All passwords are hashed with bcrypt (12 rounds)
- Access tokens expire in 1 day
- Refresh tokens expire in 30 days
- OTP expires in 5-10 minutes (random)
- Private keys must never be exposed
- Use HTTPS in production

## Default Admin Account

After seeding the database:
- **Username:** `admin`
- **Password:** `Admin@123456`
- **Email:** `admin@rsajwt.local`

## Password Requirements

- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (@$!%*?&#)

## Testing with cURL

```bash
# Login
curl -X POST http://localhost:1134/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin","password":"Admin@123456"}'

# Verify OTP (replace with actual OTP from email/logs)
curl -X POST http://localhost:1134/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin","otp":"1234"}'

# Get Profile (replace with actual access token)
curl -X GET http://localhost:1134/api/get-profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```
