# Implementation Plan: Dual Independent Reporting System

**Branch**: `001-dual-reporting` | **Date**: 2026-02-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-dual-reporting/spec.md`

## Summary

Build an enterprise Team Management Platform with a Dual Independent Reporting System that enables conflict detection between project-based reports (Source A, submitted by SDDs) and department-based reports (Source B, submitted by Department Managers). The system includes executive dashboards for CEO (OKRs, attrition, churn) and CFO (P&L, budget, cash runway), strict role-based data isolation, and immutable audit trails. Architecture uses Laravel 11 microservices backend, React+Vite frontend, FastAPI AI/ML service, all orchestrated via Docker Compose on PostgreSQL.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 11), TypeScript (React 18+), Python 3.11+ (FastAPI)
**Primary Dependencies**:
- Backend: Laravel 11, Sanctum, Spatie Permission, Laravel Excel, DomPDF, Laravel Reverb
- Frontend: React 18, Vite 5, Redux Toolkit, RTK Query, Material-UI, Recharts, React Hook Form, Zod
- AI/ML: FastAPI, scikit-learn, pandas, spaCy, MLflow
- Infrastructure: Docker Compose, Nginx, Redis 7+, Meilisearch

**Storage**: PostgreSQL 15+ (primary), Redis (cache/queue), MinIO/S3 (file storage)
**Testing**: PHPUnit + Pest (backend), Vitest + React Testing Library (frontend), pytest (AI/ML)
**Target Platform**: Linux containers (Docker), Web browsers (Chrome, Firefox, Safari, Edge)
**Project Type**: Multi-service web application (microservices backend + SPA frontend)
**Performance Goals**: 50+ concurrent users, dashboard load <3s, report submission <10min
**Constraints**: Real-time updates <5min latency, immutable audit logs, strict RBAC isolation
**Scale/Scope**: ~30 users (10 SDDs, 7 Dept Managers, C-level, Directors), 5 departments, ~10 projects

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Requirement | Status | Implementation |
|-----------|-------------|--------|----------------|
| I. Zero Trust Architecture | RBAC with CEO/CFO God-mode, SDD↔DeptMgr isolation | ✅ PASS | Spatie Permission + custom policies per silo |
| II. Data Integrity | Immutable audit trail, soft deletes, amendment logging | ✅ PASS | Laravel model events + audit_logs table |
| III. Dual-Reporting Core | Separate Source A/B tables, no auto-population | ✅ PASS | `project_reports` / `department_reports` tables |
| IV. Validation First | Weekly conflict detection, GM alerts, escalation | ✅ PASS | Laravel scheduler + conflict_alerts table |
| V. Tech Standard | Laravel 11, React+Vite, Docker Compose, PostgreSQL | ✅ PASS | Stack matches exactly |

**Security & Access Control Compliance**:
- Middleware Enforcement: Laravel middleware + Spatie gates per service
- Query Scoping: Global scopes on Eloquent models per user role
- API Design: Role derived from JWT/session, never from query params
- Frontend Guards: React route guards + RTK Query auth interceptors
- Secrets Management: `.env` files, Docker secrets, never committed

## Project Structure

### Documentation (this feature)

```text
specs/001-dual-reporting/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (OpenAPI specs)
│   ├── core-api.yaml
│   ├── reporting-api.yaml
│   ├── analytics-api.yaml
│   └── ai-ml-api.yaml
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
# Microservices Backend (Laravel)
services/
├── core/                          # Core API Service
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Policies/
│   │   ├── Services/
│   │   └── Observers/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   └── tests/
│       ├── Feature/
│       └── Unit/
│
├── reporting/                     # Reporting Service (Source A & B)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   │   ├── ProjectReport.php      # Source A
│   │   │   ├── DepartmentReport.php   # Source B
│   │   │   └── ConflictAlert.php
│   │   ├── Services/
│   │   │   ├── ConflictDetectionService.php
│   │   │   └── ReportValidationService.php
│   │   └── Jobs/
│   │       └── WeeklyConflictDetectionJob.php
│   ├── database/migrations/
│   └── tests/
│
├── analytics/                     # Analytics & Dashboard Service
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── CeoDashboardController.php
│   │   │   └── CfoDashboardController.php
│   │   ├── Services/
│   │   │   ├── OkrTrackingService.php
│   │   │   ├── AttritionPredictionService.php
│   │   │   └── FinancialMetricsService.php
│   │   └── Cache/
│   └── tests/
│
├── projects/                      # Project Management Service
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Project.php
│   │   │   └── Task.php
│   │   └── Services/
│   └── tests/
│
├── financial/                     # Financial Service
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Budget.php
│   │   │   ├── Invoice.php
│   │   │   └── Transaction.php
│   │   └── Services/
│   └── tests/
│
├── integration/                   # Integration Service
│   ├── app/
│   │   ├── Services/
│   │   │   ├── ClickUpIntegration.php
│   │   │   └── GitLabIntegration.php
│   │   └── Webhooks/
│   └── tests/
│
├── notification/                  # Notification Service
│   ├── app/
│   │   ├── Notifications/
│   │   ├── Channels/
│   │   └── Services/
│   └── tests/
│
└── ai-ml/                         # AI/ML Service (Python FastAPI)
    ├── app/
    │   ├── main.py
    │   ├── routers/
    │   │   ├── predictions.py
    │   │   └── analytics.py
    │   ├── models/
    │   │   ├── attrition_model.py
    │   │   └── churn_model.py
    │   └── services/
    ├── tests/
    └── requirements.txt

# React Frontend
frontend/
├── src/
│   ├── components/
│   │   ├── common/
│   │   ├── dashboards/
│   │   │   ├── CeoDashboard/
│   │   │   ├── CfoDashboard/
│   │   │   ├── GmDashboard/
│   │   │   ├── DirectorDashboard/
│   │   │   ├── SddDashboard/
│   │   │   └── DeptManagerDashboard/
│   │   └── reports/
│   │       ├── ProjectReportForm/     # Source A
│   │       └── DepartmentReportForm/  # Source B
│   ├── pages/
│   ├── services/
│   │   └── api/
│   ├── store/
│   │   ├── slices/
│   │   └── api/
│   ├── hooks/
│   ├── guards/
│   │   └── RoleGuard.tsx
│   └── types/
├── tests/
└── vite.config.ts

# Infrastructure
docker/
├── nginx/
│   └── nginx.conf
├── php/
│   └── Dockerfile
├── node/
│   └── Dockerfile
└── python/
    └── Dockerfile

docker-compose.yml
docker-compose.override.yml
```

**Structure Decision**: Microservices architecture with 7 Laravel services + 1 FastAPI AI/ML service + 1 React SPA. Services communicate via internal HTTP APIs. Shared PostgreSQL database with schema isolation per service where needed.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| 8 microservices instead of monolith | Clear separation of concerns for dual-reporting, financial, and AI/ML domains; independent scaling; team ownership boundaries | Monolith would mix Source A/B logic, making isolation harder to enforce and audit |
| FastAPI separate from Laravel | ML models require Python ecosystem (scikit-learn, pandas, TensorFlow); Laravel cannot efficiently host ML inference | PHP ML libraries immature; API boundary provides clear separation |
| Repository pattern in services | RBAC query scoping requires consistent data access layer; audit logging needs model events | Direct Eloquent queries would scatter access control logic across controllers |

## Phase Dependencies

```
Phase 0: Research
    ↓
Phase 1: Design (data-model.md, contracts/, quickstart.md)
    ↓
Phase 2: Tasks (/speckit.tasks command - NOT part of this plan)
```
