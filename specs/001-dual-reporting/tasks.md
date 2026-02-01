# Tasks: Infrastructure & DevOps Layer - Dual Independent Reporting System

**Input**: Design documents from `/specs/001-dual-reporting/`
**Prerequisites**: plan.md (required), research.md (technology decisions)
**Scope**: Infrastructure and containerization setup ONLY (no application logic)

## Format: `[ID] [P?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- File paths are relative to repository root

## Path Conventions

Based on plan.md structure:
- **Backend services**: `backend/` (simplified from `services/` for MVP)
- **Frontend SPA**: `frontend/`
- **AI/ML service**: `ai-service/`
- **Docker configs**: `docker/`
- **Root configs**: `./` (docker-compose.yml, Makefile, etc.)

---

## Phase 1: Setup (Project Initialization)

**Purpose**: Create the project scaffolding and development tooling

- [x] T001 Create init.sh script to generate monorepo folder structure in `./init.sh`
- [x] T002 [P] Create root .gitignore with Docker, Node, PHP, Python patterns in `./.gitignore`
- [x] T003 [P] Create root .env.example with all service environment variables in `./.env.example`
- [x] T004 [P] Create root .editorconfig for consistent formatting in `./.editorconfig`

**Checkpoint**: Run `./init.sh` to create `/backend`, `/frontend`, `/ai-service` directories

---

## Phase 2: Docker Infrastructure (Foundational Layer)

**Purpose**: Core container orchestration - MUST complete before any service setup

**CRITICAL**: All service development depends on this phase being complete

### Docker Compose Orchestration

- [x] T005 Create root docker-compose.yml orchestrating all services in `./docker-compose.yml`
- [ ] T006 [P] Create docker-compose.override.yml for dev-specific settings in `./docker-compose.override.yml`

### Service Dockerfiles

- [x] T007 [P] Create backend Dockerfile for Laravel 11 + PHP 8.2 FPM in `backend/Dockerfile`
- [x] T008 [P] Create frontend Dockerfile for Node 20 + Vite dev server in `frontend/Dockerfile`
- [x] T009 [P] Create ai-service Dockerfile for Python 3.11 + FastAPI in `ai-service/Dockerfile`

### Nginx Reverse Proxy

- [x] T010 Create docker/nginx directory structure in `docker/nginx/`
- [x] T011 Create nginx default.conf as reverse proxy for all services in `docker/nginx/default.conf`
- [x] T012 [P] Create nginx Dockerfile if custom build needed in `docker/nginx/Dockerfile`

### Supporting Infrastructure Configs

- [x] T013 [P] Create docker/php directory for PHP-FPM configs in `docker/php/`
- [x] T014 [P] Create docker/php/php.ini with Laravel-optimized settings in `docker/php/php.ini`
- [x] T015 [P] Create docker/php/www.conf for PHP-FPM pool config in `docker/php/www.conf`

**Checkpoint**: `docker compose config` validates without errors

---

## Phase 3: Development Tooling (Makefile & Scripts)

**Purpose**: Developer experience shortcuts and automation

- [x] T016 Create root Makefile with common commands in `./Makefile`
- [x] T017 [P] Create scripts/wait-for-it.sh for container startup ordering in `scripts/wait-for-it.sh`
- [x] T018 [P] Create scripts/healthcheck.sh for container health verification in `scripts/healthcheck.sh`

**Checkpoint**: `make help` displays all available commands

---

## Phase 4: Service Placeholder Files

**Purpose**: Minimal placeholder files so containers can start

### Backend Placeholders

- [x] T019 [P] Create backend/.env.example with Laravel environment template in `backend/.env.example`
- [x] T020 [P] Create backend/composer.json with Laravel 11 dependencies in `backend/composer.json`
- [x] T021 [P] Create backend/artisan placeholder (copied from Laravel) in `backend/artisan`
- [x] T022 [P] Create backend/public/index.php entry point in `backend/public/index.php`

### Frontend Placeholders

- [x] T023 [P] Create frontend/.env.example with Vite environment template in `frontend/.env.example`
- [x] T024 [P] Create frontend/package.json with React + Vite dependencies in `frontend/package.json`
- [x] T025 [P] Create frontend/vite.config.ts with dev server config in `frontend/vite.config.ts`
- [x] T026 [P] Create frontend/index.html entry point in `frontend/index.html`
- [x] T027 [P] Create frontend/src/main.tsx React entry in `frontend/src/main.tsx`

### AI Service Placeholders

- [x] T028 [P] Create ai-service/.env.example with FastAPI environment template in `ai-service/.env.example`
- [x] T029 [P] Create ai-service/requirements.txt with FastAPI dependencies in `ai-service/requirements.txt`
- [x] T030 [P] Create ai-service/app/main.py FastAPI entry point in `ai-service/app/main.py`
- [x] T031 [P] Create ai-service/app/__init__.py package marker in `ai-service/app/__init__.py`

**Checkpoint**: All containers start without crashing (may show "waiting for dependencies" logs)

---

## Phase 5: Validation & Documentation

**Purpose**: Ensure infrastructure is working and documented

- [x] T032 Create README.md with quick start instructions in `./README.md`
- [ ] T033 Verify `make up` starts all containers without errors
- [ ] T034 Verify `make install` installs dependencies in all services
- [ ] T035 Verify `make shell backend` enters PHP container
- [ ] T036 Verify `make shell frontend` enters Node container
- [ ] T037 Verify `make shell ai-service` enters Python container
- [ ] T038 Verify nginx proxies requests to correct backend services
- [ ] T039 Verify PostgreSQL accepts connections from backend container
- [ ] T040 Verify Redis accepts connections from backend container

**Checkpoint**: `make up && make install` results in all services healthy

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1: Setup
    ↓ (T001 must complete before Phase 4)
Phase 2: Docker Infrastructure
    ↓ (all Dockerfiles must exist before Phase 4)
Phase 3: Development Tooling (parallel with Phase 2)
    ↓
Phase 4: Service Placeholders (requires T005, T007-T009)
    ↓
Phase 5: Validation (requires Phase 4 complete)
```

### Critical Path

```
T001 (init.sh) → T005 (docker-compose.yml) → T007-T009 (Dockerfiles) → T019-T031 (placeholders) → T033-T040 (validation)
```

### Parallel Opportunities

**Can run in parallel after T001:**
- T002, T003, T004 (root config files)

**Can run in parallel after T005:**
- T006 (docker-compose.override)
- T007, T008, T009 (all Dockerfiles)
- T010-T015 (nginx and PHP configs)
- T016-T018 (Makefile and scripts)

**Can run in parallel after Dockerfiles:**
- T019-T031 (all placeholder files - different directories)

---

## File Specifications

### T001: init.sh

```bash
#!/bin/bash
# Creates the monorepo folder structure

set -e

echo "Creating project structure..."

# Backend (Laravel)
mkdir -p backend/{app,config,database,public,routes,storage,tests}
mkdir -p backend/app/{Http,Models,Services}

# Frontend (React + Vite)
mkdir -p frontend/{src,public,tests}
mkdir -p frontend/src/{components,pages,services,store,hooks,guards,types}

# AI Service (FastAPI)
mkdir -p ai-service/{app,tests,models}
mkdir -p ai-service/app/{routers,services}

# Docker configs
mkdir -p docker/{nginx,php,python}

# Scripts
mkdir -p scripts

echo "Project structure created successfully!"
```

### T005: docker-compose.yml Services

| Service | Image/Build | Ports | Depends On |
|---------|-------------|-------|------------|
| nginx | docker/nginx | 80:80, 443:443 | backend, frontend |
| backend | backend/Dockerfile | 9000 (internal) | postgres, redis |
| frontend | frontend/Dockerfile | 5173 (dev) | - |
| ai-service | ai-service/Dockerfile | 8000 (internal) | postgres |
| postgres | postgres:15-alpine | 5432 | - |
| redis | redis:7-alpine | 6379 | - |
| meilisearch | getmeili/meilisearch | 7700 | - |
| minio | minio/minio | 9000, 9001 | - |

### T011: nginx/default.conf Routing

| Location | Upstream | Purpose |
|----------|----------|---------|
| / | frontend:5173 | React SPA (dev) |
| /api | backend:9000 | Laravel API |
| /api/ml | ai-service:8000 | FastAPI ML endpoints |
| /storage | minio:9000 | File downloads |

### T016: Makefile Targets

| Target | Command | Description |
|--------|---------|-------------|
| up | docker compose up -d | Start all services |
| down | docker compose down | Stop all services |
| build | docker compose build | Rebuild containers |
| install | (multi-step) | Install all dependencies |
| shell | docker compose exec | Enter container shell |
| logs | docker compose logs -f | Follow service logs |
| migrate | docker compose exec backend php artisan migrate | Run migrations |
| seed | docker compose exec backend php artisan db:seed | Seed database |
| test | (multi-step) | Run tests in all services |
| help | (echo targets) | Show available commands |

---

## Notes

- [P] tasks = different files, no dependencies between them
- Tasks T007, T008, T009 can all be written simultaneously
- Tasks T019-T031 (placeholders) can all be written simultaneously
- Phase 5 tasks are sequential validation steps
- This task list covers ONLY infrastructure - no application logic
- Next phase: `/speckit.tasks` for application layer (User Stories 1-7)
