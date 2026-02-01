# Quickstart Guide: Dual Independent Reporting System

**Branch**: `001-dual-reporting` | **Date**: 2026-02-01

This guide covers local development setup and basic operations for the Team Management Platform.

## Prerequisites

- **Docker Desktop** (Windows/Mac) or **Docker Engine + Docker Compose** (Linux)
- **Git** for version control
- **Node.js 20+** (optional, for frontend development outside Docker)
- **PHP 8.2+** (optional, for running artisan commands locally)
- **Python 3.11+** (optional, for AI/ML service development)

## Quick Start

### 1. Clone and Setup

```bash
# Clone the repository
git clone <repository-url>
cd team-management-platform

# Copy environment files
cp .env.example .env
cp services/core/.env.example services/core/.env
cp services/reporting/.env.example services/reporting/.env
cp services/analytics/.env.example services/analytics/.env
cp services/ai-ml/.env.example services/ai-ml/.env
cp frontend/.env.example frontend/.env
```

### 2. Start All Services

```bash
# Build and start all containers
docker compose up -d --build

# Wait for services to be healthy (first run takes longer)
docker compose ps

# Run database migrations
docker compose exec core php artisan migrate --seed
```

### 3. Access the Application

| Service | URL | Description |
|---------|-----|-------------|
| Frontend | http://localhost:3000 | React SPA |
| Core API | http://localhost:8000/api | Laravel API |
| Reporting API | http://localhost:8001/api | Reporting Service |
| Analytics API | http://localhost:8002/api | Dashboard Service |
| AI/ML API | http://localhost:8080/api | FastAPI Service |
| API Docs | http://localhost:8000/docs | Scribe-generated docs |
| Mailpit | http://localhost:8025 | Email testing |
| MinIO Console | http://localhost:9001 | File storage admin |

### 4. Default Login Credentials

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

## Project Structure

```
team-management-platform/
├── docker-compose.yml          # Main orchestration
├── docker-compose.override.yml # Dev overrides
├── .env                        # Global environment
│
├── services/                   # Backend microservices
│   ├── core/                   # Auth, users, audit (Laravel)
│   ├── reporting/              # Source A & B reports (Laravel)
│   ├── analytics/              # Dashboards (Laravel)
│   ├── projects/               # Project management (Laravel)
│   ├── financial/              # Financials (Laravel)
│   ├── integration/            # External APIs (Laravel)
│   ├── notification/           # Notifications (Laravel)
│   └── ai-ml/                  # ML predictions (FastAPI)
│
├── frontend/                   # React SPA
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── store/
│   │   └── services/
│   └── package.json
│
├── docker/                     # Docker configurations
│   ├── nginx/
│   ├── php/
│   ├── node/
│   └── python/
│
└── specs/                      # Feature specifications
    └── 001-dual-reporting/
```

## Common Commands

### Docker Operations

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# View logs
docker compose logs -f [service-name]

# Rebuild specific service
docker compose build [service-name]
docker compose up -d [service-name]

# Access service shell
docker compose exec core bash
docker compose exec frontend sh
```

### Laravel Commands (Backend)

```bash
# Run migrations
docker compose exec core php artisan migrate

# Reset database with seeds
docker compose exec core php artisan migrate:fresh --seed

# Create new migration
docker compose exec core php artisan make:migration create_tablename_table

# Clear caches
docker compose exec core php artisan cache:clear
docker compose exec core php artisan config:clear

# Run tests
docker compose exec core php artisan test

# Generate API documentation
docker compose exec core php artisan scribe:generate
```

### Frontend Commands

```bash
# Install dependencies (if running outside Docker)
cd frontend && npm install

# Development server (outside Docker)
npm run dev

# Build for production
docker compose exec frontend npm run build

# Run tests
docker compose exec frontend npm test

# Lint code
docker compose exec frontend npm run lint
```

### AI/ML Service Commands

```bash
# Access Python shell
docker compose exec ai-ml python

# Run tests
docker compose exec ai-ml pytest

# Update dependencies
docker compose exec ai-ml pip install -r requirements.txt
```

## Development Workflows

### Creating a Project Report (Source A)

1. Login as SDD user
2. Navigate to "Project Reports" → "New Report"
3. Select project and reporting period
4. Add entries for each worker assigned to the project
5. Save as draft or submit
6. After submission, view in "My Reports" (cannot delete, only amend)

### Creating a Department Report (Source B)

1. Login as Department Manager
2. Navigate to "Department Reports" → "New Report"
3. Reporting period auto-selected (current week)
4. Add entries for each employee in department
5. Save as draft or submit
6. Note: Cannot access any project reports (Source A)

### Reviewing Conflict Alerts

1. Login as GM, CEO, or CFO
2. Navigate to "Conflict Alerts" dashboard
3. Review discrepancies (Source A hours vs Source B hours)
4. Click "View Details" for side-by-side comparison
5. Mark as resolved with resolution notes
6. Unresolved alerts auto-escalate after 7 days

### Generating CEO Board Presentation

1. Login as CEO
2. Navigate to CEO Dashboard
3. Click "Generate Board Presentation"
4. Select format (PDF/PPTX) and sections to include
5. Wait for generation (typically <30 seconds)
6. Download from notification or dashboard

## Testing RBAC Isolation

To verify Source A/B isolation:

```bash
# Login as SDD and try to access department reports
curl -X GET http://localhost:8001/api/v1/reporting/department-reports \
  -H "Authorization: Bearer $SDD_TOKEN"
# Expected: 403 Forbidden

# Login as Dept Manager and try to access project reports
curl -X GET http://localhost:8001/api/v1/reporting/project-reports \
  -H "Authorization: Bearer $DEPT_MGR_TOKEN"
# Expected: 403 Forbidden
```

## Environment Variables

### Core Service (.env)

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

### Frontend (.env)

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_REPORTING_API_URL=http://localhost:8001/api
VITE_ANALYTICS_API_URL=http://localhost:8002/api
VITE_WS_URL=ws://localhost:8000/ws
```

### AI/ML Service (.env)

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
docker compose exec core php artisan migrate:fresh --seed
```

### Frontend not connecting to API

1. Check CORS settings in Laravel services
2. Verify `VITE_API_BASE_URL` in frontend `.env`
3. Check browser console for errors
4. Ensure all services are running: `docker compose ps`

### Cache/Session issues

```bash
# Clear all caches
docker compose exec core php artisan cache:clear
docker compose exec core php artisan config:clear
docker compose exec core php artisan route:clear
docker compose exec core php artisan view:clear

# Clear Redis
docker compose exec redis redis-cli FLUSHALL
```

## API Testing with curl

### Login and get token

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sdd@example.com","password":"password"}'

# Store token for subsequent requests
export TOKEN="<token-from-response>"
```

### Create a project report

```bash
curl -X POST http://localhost:8001/api/v1/reporting/project-reports \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "reporting_period_start": "2026-01-27",
    "reporting_period_end": "2026-02-02"
  }'
```

### Add report entry

```bash
curl -X POST http://localhost:8001/api/v1/reporting/project-reports/1/entries \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 5,
    "hours_worked": 40,
    "tasks_completed": 3,
    "status": "on_track",
    "accomplishments": "Completed feature X and fixed bug Y"
  }'
```

### Submit report

```bash
curl -X POST http://localhost:8001/api/v1/reporting/project-reports/1/submit \
  -H "Authorization: Bearer $TOKEN"
```

## Next Steps

After completing setup:

1. Review the [Data Model](./data-model.md) for schema details
2. Explore [API Contracts](./contracts/) for endpoint specifications
3. Run the seeder to populate test data
4. Test role-based access by logging in as different users
5. Submit test reports and verify conflict detection works

For implementation tasks, run `/speckit.tasks` to generate the task list.
