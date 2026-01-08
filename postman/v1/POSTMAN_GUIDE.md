# Postman Setup Guide - RSA JWT Auth API

This guide explains how to import, use, and share the API collection with your team.

## Quick Start

### 1. Import Collection and Environment

**Option A: Import Files (Recommended)**
1. Open Postman Desktop or Web
2. Click **Import** button (top left)
3. Drag and drop or select these files:
   - `RSA_JWT_Auth.postman_collection.json`
   - `RSA_JWT_Auth.postman_environment.json`
4. Click **Import**

**Option B: Import via Link**
1. Push files to Git repository
2. Share raw GitHub URL with team
3. Team members use **Import** → **Link** in Postman

### 2. Select Environment

1. Click environment dropdown (top right)
2. Select **RSA JWT Auth - Local**
3. Verify `base_url` is set to `http://localhost:1134/api`

### 3. Test the API

#### Step 1: Login and Get OTP
1. Open **Authentication** folder
2. Click **2. Login - Step 1 (Get OTP)**
3. Click **Send**
4. Check terminal for OTP:
   ```bash
   # Docker mode
   docker-compose logs backend | grep "OTP"

   # Local mode
   php artisan pail | grep "OTP"
   ```

#### Step 2: Verify OTP and Get Tokens
1. Copy the 4-digit OTP from logs
2. Click **3. Verify OTP - Step 2 (Get Tokens)**
3. Replace `"otp": "1234"` with your actual OTP
4. Click **Send**
5. Tokens are **automatically saved** to environment variables

#### Step 3: Access Protected Endpoints
1. Click **Get Profile** under **User Management**
2. Click **Send** (access_token is used automatically)
3. View your profile data

## Environment Variables

The collection uses these environment variables:

| Variable | Description | Auto-saved |
|----------|-------------|------------|
| `base_url` | API base URL | Manual |
| `access_token` | JWT access token (1 day expiry) | ✅ Yes |
| `refresh_token` | JWT refresh token (30 days expiry) | ✅ Yes |
| `user_email` | User email from login | ✅ Yes |

### View/Edit Environment Variables

1. Click the **eye icon** (👁️) next to environment dropdown
2. View current values
3. Click **Edit** to modify

## Authentication Flow

```
1. Register/Login
   ↓
2. Receive OTP (check logs)
   ↓
3. Verify OTP
   ↓
4. Receive Tokens (auto-saved)
   ↓
5. Use Access Token for protected endpoints
   ↓
6. Refresh Token when expired
```

## Collection Structure

### 📁 Authentication (Public Endpoints)
- **1. Register User** - Create new account
- **2. Login - Step 1** - Validate credentials, send OTP
- **3. Verify OTP - Step 2** - Validate OTP, get tokens
- **4. Refresh Token** - Renew access token
- **5. Logout** - Revoke refresh token

### 📁 User Management (Protected Endpoints)
- **Get Profile** 🔒 - Get authenticated user data
- **Create User (Admin Only)** 🔒👑 - Admin creates users

## Sharing with Team Members

### Method 1: Export and Share Files (Simple)

1. **Export Collection:**
   - Click ⋯ next to collection name
   - Select **Export**
   - Choose **Collection v2.1**
   - Save file

2. **Export Environment:**
   - Click ⋯ next to environment name
   - Select **Export**
   - Save file

3. **Share:**
   - Email files to team
   - Share via Slack/Teams
   - Commit to Git repository

### Method 2: Postman Workspace (Team Collaboration)

1. **Create Workspace:**
   - Click **Workspaces** dropdown
   - Select **Create Workspace**
   - Choose **Team** workspace
   - Name it: "RSA JWT Auth Team"

2. **Add Collection to Workspace:**
   - Move collection to team workspace
   - Right-click collection → **Share**
   - Add team members by email

3. **Invite Team:**
   - Click **Invite** in workspace
   - Enter team member emails
   - Set permissions (Viewer/Editor)

### Method 3: Public Link (Quick Share)

1. Right-click collection → **Share**
2. Toggle **Get public link**
3. Copy link and share with team
4. Team members click link → **Fork Collection**

### Method 4: Git Repository (Version Control)

```bash
# Add Postman files to repository
git add RSA_JWT_Auth.postman_collection.json
git add RSA_JWT_Auth.postman_environment.json
git add POSTMAN_GUIDE.md

git commit -m "Add Postman collection and environment"
git push

# Team members clone and import
git pull
# Then import files in Postman
```

## Default Admin Credentials

For testing, use these credentials:

```json
{
  "identifier": "admin",
  "password": "Admin@123456"
}
```

**Email:** admin@rsajwt.local

## Tips and Best Practices

### 1. Auto-Save Tokens
The collection includes scripts that automatically save tokens after login. No manual copying needed!

### 2. Check Logs for OTP
OTPs are sent via email (or logged in development):
```bash
# Docker
docker-compose logs -f backend | grep "OTP"

# Local
tail -f storage/logs/laravel.log | grep "OTP"
```

### 3. Token Expiry
- **Access Token:** 1 day (86400 seconds)
- **Refresh Token:** 30 days
- Use **Refresh Token** endpoint when access token expires

### 4. Environment Variables
Create different environments for different setups:
- **RSA JWT Auth - Local** (http://localhost:1134/api)
- **RSA JWT Auth - Docker** (http://localhost:1134/api)
- **RSA JWT Auth - Staging** (https://staging.example.com/api)
- **RSA JWT Auth - Production** (https://api.example.com/api)

### 5. Test Different Roles
1. Login as admin
2. Create regular user via **Create User** endpoint
3. Login as regular user
4. Test admin-only endpoints (should return 403)

## Common Issues

### Issue: "Unauthorized" Error
**Solution:**
1. Check if access_token is set in environment
2. Verify token hasn't expired (1 day)
3. Use Refresh Token endpoint

### Issue: "Forbidden - Admin access required"
**Solution:**
- Login with admin account
- Regular users cannot access admin-only endpoints

### Issue: "Invalid OTP"
**Solution:**
1. Check logs for correct OTP
2. Verify OTP hasn't expired (5-10 minutes)
3. Request new OTP by logging in again

### Issue: Base URL not working
**Solution:**
1. Verify Docker container is running: `docker-compose ps`
2. Check backend is accessible: `curl http://localhost:1134/api`
3. Ensure environment is selected in Postman

## Advanced: Using Postman CLI (Newman)

Run collection via command line:

```bash
# Install Newman
npm install -g newman

# Run collection
newman run RSA_JWT_Auth.postman_collection.json \
  -e RSA_JWT_Auth.postman_environment.json

# Generate HTML report
newman run RSA_JWT_Auth.postman_collection.json \
  -e RSA_JWT_Auth.postman_environment.json \
  -r html
```

## Team Workflow Example

### For New Team Members:

1. **Clone repository:**
   ```bash
   git clone <repository-url>
   cd rsa_jwt_role_base_two_factor_auth
   ```

2. **Start Docker:**
   ```bash
   docker-compose up -d
   ```

3. **Import Postman files:**
   - Open Postman
   - Import `RSA_JWT_Auth.postman_collection.json`
   - Import `RSA_JWT_Auth.postman_environment.json`

4. **Select environment:**
   - Choose "RSA JWT Auth - Local"

5. **Test API:**
   - Run Login → Verify OTP → Get Profile

6. **Start developing:**
   - Use collection to test new endpoints
   - Update collection when API changes
   - Commit collection changes to Git

## Documentation

- **API Documentation:** `API_DOCUMENTATION.md`
- **Project Setup:** `CLAUDE.md`
- **Postman Guide:** This file

## Support

If you encounter issues:
1. Check `API_DOCUMENTATION.md` for endpoint details
2. Review Docker logs: `docker-compose logs -f backend`
3. Verify database is running: `docker-compose ps`
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Happy Testing! 🚀**
