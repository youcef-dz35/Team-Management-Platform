# Team Management Platform - Windows Setup Guide

This comprehensive guide explains how to set up and run the Team Management Platform on a Windows machine.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Windows-Specific Requirements](#windows-specific-requirements)
3. [Installation Steps](#installation-steps)
4. [Running the Application](#running-the-application)
5. [Accessing Services](#accessing-services)
6. [Common Windows Issues & Solutions](#common-windows-issues--solutions)
7. [Development Workflow on Windows](#development-workflow-on-windows)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software

| Software | Minimum Version | Download Link |
|----------|-----------------|---------------|
| **Docker Desktop** | 4.0+ | https://www.docker.com/products/docker-desktop |
| **Git** | 2.30+ | https://git-scm.com/download/win |
| **Windows Terminal** | Latest | Microsoft Store |
| **VS Code** (recommended) | Latest | https://code.visualstudio.com |

### System Requirements

- **OS**: Windows 10 Pro/Enterprise (Build 18362+) or Windows 11
- **RAM**: Minimum 8GB (16GB recommended)
- **Disk**: At least 20GB free space
- **CPU**: 64-bit processor with virtualization support (Intel VT-x or AMD-V)

---

## Windows-Specific Requirements

### 1. Enable WSL2 (Recommended)

WSL2 (Windows Subsystem for Linux 2) provides much better performance for Docker containers.

```powershell
# Run PowerShell as Administrator
wsl --install

# Set WSL2 as default
wsl --set-default-version 2

# Restart your computer
```

### 2. Configure Docker Desktop for WSL2

1. Open Docker Desktop
2. Go to **Settings** > **General**
3. Enable **"Use the WSL 2 based engine"**
4. Go to **Settings** > **Resources** > **WSL Integration**
5. Enable integration with your default WSL distro
6. Click **Apply & Restart**

### 3. Configure Git for Windows

```powershell
# Configure line endings (important for cross-platform compatibility)
git config --global core.autocrlf true

# Set your identity
git config --global user.name "Your Name"
git config --global user.email "your@email.com"
```

### 4. Allocate Docker Resources

Go to Docker Desktop > **Settings** > **Resources** > **Advanced**:
- **CPUs**: At least 4 cores
- **Memory**: At least 8GB (recommended 12GB)
- **Swap**: 2GB
- **Disk image size**: 64GB minimum

---

## Installation Steps

### Step 1: Clone the Repository

```powershell
# Using PowerShell or Git Bash
cd C:\Projects  # or your preferred directory

git clone <repository-url> team-management-platform
cd team-management-platform
```

### Step 2: Create Environment Files

**Option A: Using PowerShell**
```powershell
# Root environment
Copy-Item .env.example .env

# Backend environment
Copy-Item backend\.env.example backend\.env

# Frontend environment
Copy-Item frontend\.env.example frontend\.env

# AI Service environment (if exists)
if (Test-Path "ai-service\.env.example") {
    Copy-Item ai-service\.env.example ai-service\.env
}
```

**Option B: Using Git Bash**
```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp ai-service/.env.example ai-service/.env 2>/dev/null || true
```

### Step 3: Configure Environment Variables

Edit the `.env` file in the root directory and ensure these values are set:

```ini
# Application
APP_NAME="Team Management Platform"
APP_ENV=local
APP_DEBUG=true

# Database
POSTGRES_HOST=postgres
POSTGRES_PORT=5432
POSTGRES_DB=team_mgmt
POSTGRES_USER=app
POSTGRES_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Frontend
VITE_API_URL=http://localhost/api
```

### Step 4: Build and Start Containers

**Option A: Using Make (requires Git Bash or WSL)**
```bash
# Run in Git Bash or WSL terminal
make setup
```

**Option B: Using Docker Compose directly (PowerShell)**
```powershell
# Build images
docker compose build

# Start all services
docker compose up -d

# Wait for services to be healthy (about 30 seconds)
Start-Sleep -Seconds 30

# Install backend dependencies
docker compose exec backend composer install

# Install frontend dependencies
docker compose exec frontend npm install

# Run database migrations
docker compose exec backend php artisan migrate --force

# Seed the database with sample data
docker compose exec backend php artisan db:seed --force
```

### Step 5: Generate Application Key

```powershell
docker compose exec backend php artisan key:generate
```

### Step 6: Verify Installation

```powershell
# Check all containers are running
docker compose ps

# Expected output should show all services as "running":
# - tmp-nginx
# - tmp-backend
# - tmp-frontend
# - tmp-postgres
# - tmp-redis
# - tmp-ai-service (if configured)
# - tmp-meilisearch
# - tmp-minio
# - tmp-mailpit
```

---

## Running the Application

### Start All Services

```powershell
docker compose up -d
```

### Stop All Services

```powershell
docker compose down
```

### Restart Services

```powershell
docker compose restart

# Or restart a specific service
docker compose restart backend
```

### View Logs

```powershell
# All services
docker compose logs -f

# Specific service
docker compose logs -f backend
docker compose logs -f frontend
```

---

## Accessing Services

Once the application is running, access the following URLs:

| Service | URL | Description |
|---------|-----|-------------|
| **Frontend** | http://localhost:5173 | React application (Vite dev server) |
| **Frontend (via Nginx)** | http://localhost | Production-like access |
| **Backend API** | http://localhost/api | Laravel API endpoints |
| **API Documentation** | http://localhost/api/documentation | API docs (if enabled) |
| **Mailpit** | http://localhost:8025 | Email testing interface |
| **MinIO Console** | http://localhost:9001 | File storage management |
| **Meilisearch** | http://localhost:7700 | Search engine dashboard |
| **PostgreSQL** | localhost:5432 | Database (use pgAdmin or similar) |
| **Redis** | localhost:6379 | Cache/Queue (use RedisInsight) |

### Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| CEO | ceo@example.com | password |
| CFO | cfo@example.com | password |
| General Manager | gm@example.com | password |
| Operations Manager | ops@example.com | password |
| Director | dammy@example.com | password |
| SDD (Service Delivery Director) | sdd1@example.com | password |
| Department Manager | deptmgr.backend@example.com | password |
| Worker | worker.backend1@example.com | password |

---

## Common Windows Issues & Solutions

### Issue 1: "Docker Desktop requires Windows 10 Pro or Enterprise"

**Solution**:
- Upgrade to Windows 10 Pro or use Windows 11 Home (which supports WSL2)
- Alternatively, use Docker Toolbox (legacy, not recommended)

### Issue 2: Containers fail to start - "port already in use"

**Solution**:
```powershell
# Find what's using the port (e.g., port 80)
netstat -ano | findstr :80

# Kill the process using its PID
taskkill /PID <PID> /F

# Common conflicts:
# - IIS (World Wide Web Publishing Service)
# - Apache/XAMPP
# - Skype (uses ports 80/443)
```

To disable IIS:
```powershell
# Run as Administrator
Stop-Service W3SVC
Set-Service W3SVC -StartupType Disabled
```

### Issue 3: File watching not working (Hot Reload fails)

The project is pre-configured for Windows polling. If HMR still fails:

**Solution 1**: Verify environment variables in docker-compose.yml:
```yaml
frontend:
  environment:
    - CHOKIDAR_USEPOLLING=true
    - WATCHPACK_POLLING=true
```

**Solution 2**: Move project to WSL2 filesystem:
```powershell
# Access WSL filesystem
\\wsl$\Ubuntu\home\<username>\projects

# Clone project there instead
wsl
cd ~/projects
git clone <repo-url>
```

### Issue 4: "Permission denied" errors

**Solution**:
```powershell
# Restart Docker Desktop
# Then recreate containers
docker compose down -v
docker compose up -d --build
```

### Issue 5: Slow file system performance

Docker on Windows is slower due to file system translation.

**Solutions**:
1. **Use WSL2 backend** (most important)
2. Store project in WSL2 filesystem (`\\wsl$\Ubuntu\...`)
3. Exclude project folder from Windows Defender:
   ```powershell
   # Run as Administrator
   Add-MpPreference -ExclusionPath "C:\Projects\team-management-platform"
   ```

### Issue 6: "No space left on device"

**Solution**:
```powershell
# Clean up Docker resources
docker system prune -a --volumes

# Or in Docker Desktop: Settings > Resources > Disk image size
```

### Issue 7: Database connection refused

**Solution**:
```powershell
# Wait for PostgreSQL to be ready
docker compose exec backend php artisan migrate:status

# If it fails, check PostgreSQL logs
docker compose logs postgres

# Restart PostgreSQL
docker compose restart postgres
Start-Sleep -Seconds 10
docker compose exec backend php artisan migrate
```

### Issue 8: Line ending issues (CRLF vs LF)

**Solution**:
```bash
# Fix line endings for shell scripts
git config core.autocrlf true

# Or convert specific files
# In Git Bash:
find . -name "*.sh" -exec sed -i 's/\r$//' {} \;
```

---

## Development Workflow on Windows

### Using PowerShell

```powershell
# Run Laravel Artisan commands
docker compose exec backend php artisan <command>

# Examples:
docker compose exec backend php artisan migrate
docker compose exec backend php artisan tinker
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan test

# Run npm commands
docker compose exec frontend npm <command>

# Examples:
docker compose exec frontend npm install <package>
docker compose exec frontend npm run build
docker compose exec frontend npm test
```

### Using Git Bash (Recommended for Makefile)

```bash
# All Makefile commands work in Git Bash
make up
make down
make logs
make test
make migrate
make seed
```

### Using VS Code

1. Install the **Remote - Containers** extension
2. Open the project folder
3. Click the green button in the bottom-left corner
4. Select "Reopen in Container"

This gives you a full Linux development environment inside the container.

### Database Management

**Using pgAdmin 4** (recommended):
1. Download from https://www.pgadmin.org/download/
2. Add new server:
   - Host: `localhost`
   - Port: `5432`
   - Database: `team_mgmt`
   - Username: `app`
   - Password: `secret`

**Using DBeaver**:
1. Download from https://dbeaver.io/download/
2. Create new PostgreSQL connection with same credentials

### Redis Management

**Using RedisInsight**:
1. Download from https://redis.com/redis-enterprise/redis-insight/
2. Add database:
   - Host: `localhost`
   - Port: `6379`

---

## Testing

### Run Backend Tests

```powershell
# All tests
docker compose exec backend php artisan test

# Specific test file
docker compose exec backend php artisan test --filter=AuthenticationTest

# With coverage
docker compose exec backend php artisan test --coverage
```

### Run Frontend Tests

```powershell
# All tests
docker compose exec frontend npm test

# With UI
docker compose exec frontend npm run test:ui

# With coverage
docker compose exec frontend npm run test:coverage
```

### Run Full Test Suite

Using Git Bash or WSL:
```bash
make test-all
```

Or in PowerShell:
```powershell
# Run the PowerShell test script if available
.\scripts\test-local.ps1

# Or manually
docker compose exec backend php artisan test
docker compose exec frontend npm test -- --run
```

---

## Troubleshooting

### Check Service Health

```powershell
# Check all containers
docker compose ps

# Check specific service logs
docker compose logs backend --tail=50
docker compose logs frontend --tail=50
docker compose logs postgres --tail=50

# Check if services respond
# Backend health
curl http://localhost/api/health

# Frontend
curl http://localhost:5173

# PostgreSQL
docker compose exec postgres pg_isready -U app -d team_mgmt

# Redis
docker compose exec redis redis-cli ping
```

### Reset Everything

If nothing works, try a complete reset:

```powershell
# Stop and remove everything
docker compose down -v --remove-orphans

# Remove all project images
docker images | Select-String "team" | ForEach-Object {
    docker rmi ($_ -split '\s+')[2] -f
}

# Rebuild from scratch
docker compose build --no-cache
docker compose up -d

# Reinstall dependencies
docker compose exec backend composer install
docker compose exec frontend npm install

# Reset database
docker compose exec backend php artisan migrate:fresh --seed
```

### Get Help

1. Check the logs: `docker compose logs -f`
2. Check container status: `docker compose ps`
3. Review this guide's troubleshooting section
4. Check the project's GitHub issues

---

## Quick Reference Commands

```powershell
# Start
docker compose up -d

# Stop
docker compose down

# Restart
docker compose restart

# Logs
docker compose logs -f [service]

# Shell access
docker compose exec backend bash
docker compose exec frontend sh

# Laravel commands
docker compose exec backend php artisan <command>

# npm commands
docker compose exec frontend npm <command>

# Database reset
docker compose exec backend php artisan migrate:fresh --seed

# Clear all caches
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan route:clear
docker compose exec backend php artisan view:clear
```

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        Windows Host                              │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                   Docker Desktop (WSL2)                    │  │
│  │  ┌─────────────────────────────────────────────────────┐  │  │
│  │  │                    Nginx (:80)                       │  │  │
│  │  │              Reverse Proxy / Load Balancer           │  │  │
│  │  └─────────────────────────────────────────────────────┘  │  │
│  │           │              │              │                  │  │
│  │           ▼              ▼              ▼                  │  │
│  │  ┌───────────┐   ┌───────────┐   ┌───────────┐           │  │
│  │  │  Backend  │   │ Frontend  │   │ AI Service│           │  │
│  │  │  Laravel  │   │   React   │   │  FastAPI  │           │  │
│  │  │  (:9000)  │   │  (:5173)  │   │  (:8000)  │           │  │
│  │  └─────┬─────┘   └───────────┘   └─────┬─────┘           │  │
│  │        │                               │                  │  │
│  │        ▼                               ▼                  │  │
│  │  ┌─────────────────────────────────────────┐             │  │
│  │  │              PostgreSQL (:5432)          │             │  │
│  │  │                 Database                 │             │  │
│  │  └─────────────────────────────────────────┘             │  │
│  │        │                                                  │  │
│  │        ▼                                                  │  │
│  │  ┌───────────┐   ┌───────────┐   ┌───────────┐          │  │
│  │  │   Redis   │   │Meilisearch│   │   MinIO   │          │  │
│  │  │  (:6379)  │   │  (:7700)  │   │  (:9001)  │          │  │
│  │  │Cache/Queue│   │  Search   │   │  Storage  │          │  │
│  │  └───────────┘   └───────────┘   └───────────┘          │  │
│  │                                                           │  │
│  │  ┌───────────┐                                           │  │
│  │  │  Mailpit  │                                           │  │
│  │  │  (:8025)  │                                           │  │
│  │  │   Email   │                                           │  │
│  │  └───────────┘                                           │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘

Port Summary:
- 80     → Nginx (main entry point)
- 5173   → Frontend (Vite dev server)
- 5432   → PostgreSQL
- 6379   → Redis
- 7700   → Meilisearch
- 8025   → Mailpit (email UI)
- 9001   → MinIO (file storage UI)
```

---

## Additional Resources

- [Docker Desktop for Windows Documentation](https://docs.docker.com/desktop/install/windows-install/)
- [WSL2 Installation Guide](https://learn.microsoft.com/en-us/windows/wsl/install)
- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev)
- [Vite Documentation](https://vitejs.dev)

---

**Last Updated**: February 2026
**Version**: 1.0
