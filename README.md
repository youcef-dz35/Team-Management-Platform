# Team Management Platform

Enterprise Dual Independent Reporting System built with the Antigravity stack.

## Overview

This platform implements a dual reporting system where organizational data flows through two independent channels:

- **Source A (Project Reports)**: Submitted by Sub-Department Directors (SDDs)
- **Source B (Department Reports)**: Submitted by Department Managers

The system detects conflicts between these sources and provides executive dashboards for organizational oversight.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: React 18 + Vite 5 + TypeScript
- **AI/ML Service**: Python 3.11 + FastAPI
- **Database**: PostgreSQL 15
- **Cache/Queue**: Redis 7
- **Search**: Meilisearch
- **Storage**: MinIO (S3-compatible)
- **WebSockets**: Laravel Reverb
- **Orchestration**: Docker Compose

## Prerequisites

- Docker Desktop 4.x+
- Docker Compose v2.x+
- Git

## Quick Start

1. **Clone and setup**:
   ```bash
   git clone <repository-url>
   cd team-management-platform
   cp .env.example .env
   ```

2. **Initialize directories**:
   ```bash
   ./init.sh
   ```

3. **Start all services**:
   ```bash
   make setup
   ```

4. **Access the application**:
   - Frontend: http://localhost:5173
   - API: http://localhost/api
   - API Documentation: http://localhost:8000/docs
   - Mailpit: http://localhost:8025
   - MinIO Console: http://localhost:9001

## Available Commands

```bash
make help          # Show all available commands
make setup         # Build and start all services with dependencies
make up            # Start all services
make down          # Stop all services
make build         # Build/rebuild Docker images
make shell         # Open shell in backend container
make logs          # View logs from all services
make migrate       # Run database migrations
make seed          # Run database seeders
make test          # Run all tests
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

1. C-Level Executives (CEO, CFO)
2. Executive Management
3. Directors
4. Middle Management (SDDs, Department Managers)
5. Workers

### Data Flow

```
Workers → SDDs → Project Reports (Source A) ─┐
                                             ├→ Conflict Detection → Executive Dashboards
Workers → Dept Managers → Dept Reports (Source B) ─┘
```

### Executive Dashboards

- **CEO Dashboard**: OKR tracking, attrition metrics, retention analytics
- **CFO Dashboard**: P&L statements, budget vs actual, cash runway

## Development

### Backend Development

```bash
make shell                    # Enter backend container
php artisan make:model Foo    # Create model
php artisan make:migration    # Create migration
php artisan test              # Run tests
```

### Frontend Development

```bash
docker compose exec frontend sh
npm run dev                   # Start dev server (already running)
npm run test                  # Run tests
npm run build                 # Production build
```

### AI Service Development

```bash
docker compose exec ai-service bash
pytest                        # Run tests
python -m app.main            # Start server
```

## Environment Variables

See `.env.example` for all available configuration options.

## License

Proprietary - All rights reserved.
