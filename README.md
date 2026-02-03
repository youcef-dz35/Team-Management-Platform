# Team Management Platform

Enterprise Dual Independent Reporting System built with the Antigravity stack.

## Overview

This platform implements a dual reporting system where organizational data flows through two independent channels:

-   **Source A (Project Reports)**: Submitted by Sub-Department Directors (SDDs)
-   **Source B (Department Reports)**: Submitted by Department Managers

The system detects conflicts between these sources and provides executive dashboards for organizational oversight.

## Tech Stack

-   **Backend**: Laravel 11 (PHP 8.2)
-   **Frontend**: React 18 + Vite 5 + TypeScript
-   **AI/ML Service**: Python 3.11 + FastAPI
-   **Database**: PostgreSQL 15
-   **Cache/Queue**: Redis 7
-   **Search**: Meilisearch
-   **Storage**: MinIO (S3-compatible)
-   **WebSockets**: Laravel Reverb
-   **Orchestration**: Docker Compose

## Prerequisites

-   **Docker Desktop** (Windows/Mac) or **Docker Engine + Docker Compose** (Linux)
-   **Git** for version control
-   **Node.js 20+** (optional, for frontend development outside Docker)
-   **PHP 8.2+** (optional, for running artisan commands locally)
-   **Python 3.11+** (optional, for AI/ML service development)

## Quick Start

1.  **Clone the repository**:
    ```bash
    git clone <repository-url> # Replace <repository-url> with your actual repository URL
    cd team-management-platform
    ```

2.  **Copy environment files**:
    ```bash
    cp .env.example .env
    cp backend/.env.example backend/.env
    cp frontend/.env.example frontend/.env
    cp ai-service/.env.example ai-service/.env
    # Add other service .env.example files if they exist (e.g., services/core, services/reporting etc. based on your project structure)
    ```

3.  **Initialize directories**:
    ```bash
    ./init.sh
    ```

4.  **Build and start all services, run migrations and seed the database**:
    ```bash
    make setup
    # This command typically runs:
    # docker compose up -d --build
    # docker compose exec backend php artisan migrate --seed
    ```

5.  **Wait for services to be healthy** (first run takes longer). You can check their status with:
    ```bash
    docker compose ps
    ```

6.  **Access the application**:
    | Service         | URL                    | Description             |
    | :-------------- | :--------------------- | :---------------------- |
    | Frontend        | `http://localhost:5173` | React SPA               |
    | Backend API     | `http://localhost:8000/api` | Laravel API             |
    | AI/ML API       | `http://localhost:8080/api` | FastAPI Service         |
    | API Docs        | `http://localhost:8000/docs` | Scribe-generated docs   |
    | Mailpit         | `http://localhost:8025` | Email testing           |
    | MinIO Console   | `http://localhost:9001` | File storage admin      |

## Default Login Credentials

```
CEO:          ceo@example.com / password
CFO:          cfo@example.com / password
GM:           gm@example.com / password
Ops Manager:  ops@example.com / password
Director:     director@example.com / password
SDD:          sdd@example.com / password
Dept Manager: deptmgr@example.com / password
Worker:       worker@example.com / password
```

## Available Commands (`make help`)

```bash
make help          # Show all available commands
make setup         # Build and start all services, runs migrations and seeds the database
make up            # Start all services
make down          # Stop all services
make build         # Build/rebuild Docker images
make shell-backend # Open shell in backend container (php artisan commands)
make shell-frontend # Open shell in frontend container (npm commands)
make shell-ai      # Open shell in ai-service container (python commands)
make logs          # View logs from all services
make migrate       # Run database migrations
make seed          # Run database seeders
make test          # Run all tests (backend and frontend)
make clean         # Remove all containers, volumes, and images
```

## Project Structure

```
team-management-platform/
├── backend/              # Laravel 11 API
│   ├── app/              # Application code
│   ├── config/           # Configuration files
│   ├── database/         # Migrations and seeders
│   ├── routes/           # API routes
│   └── tests/            # PHP tests
├── frontend/             # React + Vite SPA
│   ├── src/              # Source code
│   └── tests/            # Frontend tests
├── ai-service/           # Python FastAPI service
│   ├── app/              # Application code
│   └── models/           # ML model storage
├── docker/               # Docker configurations
│   ├── nginx/            # Nginx reverse proxy
│   └── php/              # PHP-FPM config
├── scripts/              # Utility scripts
├── specs/                # Feature specifications
├── docker-compose.yml    # Service orchestration
├── Makefile              # Development commands
└── README.md
```

## Architecture

### Organizational Hierarchy

1.  C-Level Executives (CEO, CFO)
2.  Executive Management
3.  Directors
4.  Middle Management (SDDs, Department Managers)
5.  Workers

### Data Flow

```
Workers → SDDs → Project Reports (Source A) ─┐
                                             ├→ Conflict Detection → Executive Dashboards
Workers → Dept Managers → Dept Reports (Source B) ─┘
```

### Executive Dashboards

-   **CEO Dashboard**: OKR tracking, attrition metrics, retention analytics
-   **CFO Dashboard**: P&L statements, budget vs actual, cash runway

## Development Workflows

### Creating a Project Report (Source A)

1.  Login as SDD user
2.  Navigate to "Project Reports" → "New Report"
3.  Select project and reporting period
4.  Add entries for each worker assigned to the project
5.  Save as draft or submit
6.  After submission, view in "My Reports" (cannot delete, only amend)

### Creating a Department Report (Source B)

1.  Login as Department Manager
2.  Navigate to "Department Reports" → "New Report"
3.  Reporting period auto-selected (current week)
4.  Add entries for each employee in department
5.  Save as draft or submit
6.  Note: Cannot access any project reports (Source A)

### Reviewing Conflict Alerts

1.  Login as GM, CEO, or CFO
2.  Navigate to "Conflict Alerts" dashboard
3.  Review discrepancies (Source A hours vs Source B hours)
4.  Click "View Details" for side-by-side comparison
5.  Mark as resolved with resolution notes
6.  Unresolved alerts auto-escalate after 7 days

## Environment Variables

Key environment variables are defined in `.env.example` files at the root and within each service directory.
Ensure these are copied and configured appropriately:

### Project Root (`.env`)

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=team_mgmt
DB_USERNAME=app
DB_PASSWORD=secret
REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=reverb
MINIO_ENDPOINT=http://minio:9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_BUCKET=reports
```

### Frontend (`frontend/.env`)

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REPORTING_API_URL=http://localhost:8001/api
VITE_ANALYTICS_API_URL=http://localhost:8002/api
VITE_WS_URL=ws://localhost:8000/ws
```

### AI/ML Service (`ai-service/.env`)

```env
DATABASE_URL=postgresql://app:secret@postgres:5432/team_mgmt
MODEL_STORAGE_PATH=/app/models
LOG_LEVEL=INFO
SERVICE_TOKEN_SECRET=<shared-secret-with-laravel>
```

## Troubleshooting

### Services not starting

```bash
# Check container status
docker compose ps

# View logs for failing service
docker compose logs [service-name]

# Rebuild from scratch
docker compose down -v
docker compose up -d --build
```

### Database connection errors

```bash
# Ensure postgres is running
docker compose ps postgres

# Reset database
docker compose exec backend php artisan migrate:fresh --seed
```

### Frontend not connecting to API

1.  Check CORS settings in Laravel services
2.  Verify `VITE_API_BASE_URL` in `frontend/.env`
3.  Check browser console for errors
4.  Ensure all services are running: `docker compose ps`

### Cache/Session issues

```bash
# Clear all caches
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan route:clear
docker compose exec backend php artisan view:clear

# Clear Redis
docker compose exec redis redis-cli FLUSHALL
```

## License

Proprietary - All rights reserved.