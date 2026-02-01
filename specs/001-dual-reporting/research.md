# Research: Dual Independent Reporting System

**Branch**: `001-dual-reporting` | **Date**: 2026-02-01

## Overview

This document consolidates research findings for the Dual Independent Reporting System implementation. All technical decisions are documented with rationale and alternatives considered.

---

## 1. Laravel Microservices Architecture

### Decision
Use Laravel 11 with separate service directories sharing a single PostgreSQL database, communicating via internal HTTP APIs (not message queues for MVP).

### Rationale
- **Shared Database**: Simpler than distributed databases for ~30 users; transaction consistency for conflict detection across Source A/B
- **Service Separation**: Logical isolation enables RBAC enforcement at service boundaries; Reporting Service handles both Source A and Source B but with strict model separation
- **Internal APIs**: Services call each other via HTTP (e.g., Analytics calls Reporting for data); Kong/Nginx as API gateway for external traffic

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| True microservices with separate DBs | Overkill for 30 users; distributed transactions complicate conflict detection |
| Laravel Modules (single app) | Harder to enforce isolation boundaries; shared service container risks cross-contamination |
| Event-driven with RabbitMQ | Adds operational complexity; synchronous calls sufficient for current scale |

---

## 2. RBAC Implementation Strategy

### Decision
Use Spatie Laravel-Permission package with custom Policies and Global Scopes for data isolation.

### Rationale
- **Spatie Permission**: Industry standard for Laravel RBAC; roles/permissions stored in DB; middleware integration
- **Global Scopes**: Eloquent global scopes automatically filter queries by user role; SDD queries never touch `department_reports`
- **Policies**: Laravel Policies provide authorization logic per model; `ProjectReportPolicy` checks user is SDD with assigned project

### Implementation Pattern
```php
// Global Scope for SDD users on ProjectReport model
class SddProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->user()->hasRole('sdd')) {
            $builder->whereIn('project_id', auth()->user()->assignedProjectIds());
        }
    }
}

// Policy for ProjectReport
class ProjectReportPolicy
{
    public function view(User $user, ProjectReport $report): bool
    {
        return $user->hasRole(['ceo', 'cfo', 'gm', 'ops_manager', 'director'])
            || ($user->hasRole('sdd') && $user->assignedProjectIds()->contains($report->project_id));
    }
}
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Custom RBAC from scratch | Reinventing the wheel; Spatie is battle-tested |
| Laravel Gates only | Less structured than Policies; harder to maintain for complex rules |
| Bouncer package | Less popular than Spatie; smaller community support |

---

## 3. Audit Logging Strategy

### Decision
Use Laravel Model Events with a dedicated `audit_logs` table, implementing immutable append-only logging.

### Rationale
- **Model Events**: `created`, `updated`, `deleted` events trigger automatic logging via Observers
- **Immutable Design**: `audit_logs` table has no `UPDATE` or `DELETE` permissions at database level; only `INSERT`
- **Structured Data**: JSON column stores before/after state for amendments

### Schema Design
```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    auditable_type VARCHAR(255) NOT NULL,  -- e.g., 'ProjectReport'
    auditable_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL,           -- 'created', 'updated', 'amended'
    user_id BIGINT NOT NULL,
    user_role VARCHAR(50) NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
    -- NO updated_at column (immutable)
);

-- Prevent updates/deletes at DB level
REVOKE UPDATE, DELETE ON audit_logs FROM app_user;
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Spatie Activity Log | Good but less control over immutability enforcement |
| Event Sourcing | Overkill for audit requirements; adds significant complexity |
| Database triggers | Less testable; business logic belongs in application layer |

---

## 4. Conflict Detection Algorithm

### Decision
Implement a scheduled job that compares aggregated hours per employee between Source A (sum of project reports) and Source B (department report) weekly.

### Rationale
- **Weekly Cadence**: Matches reporting cadence; gives time for amendments before alerts
- **Employee-Centric Comparison**: Link reports via `employee_id`; compare total hours reported in Source A vs Source B
- **Threshold-Based**: Flag discrepancies >2 hours (configurable) to avoid noise from rounding

### Algorithm
```
For each employee E in reporting_period P:
    source_a_hours = SUM(project_report_entries.hours WHERE employee_id = E AND period = P)
    source_b_hours = department_report_entry.hours WHERE employee_id = E AND period = P

    IF |source_a_hours - source_b_hours| > THRESHOLD:
        CREATE conflict_alert(
            employee_id = E,
            period = P,
            source_a_hours,
            source_b_hours,
            discrepancy = source_a_hours - source_b_hours,
            status = 'open'
        )
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Real-time validation on submit | Blocks submission; conflicts are investigative, not preventive |
| Daily validation | Too frequent; increases alert fatigue |
| Manual GM trigger | Defeats automation purpose; prone to being forgotten |

---

## 5. Executive Dashboard Data Strategy

### Decision
Use materialized views + Redis caching for dashboard metrics, refreshed every 5 minutes.

### Rationale
- **Materialized Views**: PostgreSQL materialized views pre-compute complex aggregations (OKR progress, P&L summaries)
- **Redis Cache**: Dashboard API responses cached with 5-minute TTL; invalidated on relevant data changes
- **Lazy ML Integration**: AI/ML predictions (attrition, churn) called on-demand from FastAPI service, cached for 24 hours

### Cache Keys
```
cache:ceo_dashboard:{user_id}:okrs -> 5min TTL
cache:ceo_dashboard:{user_id}:attrition -> 24hr TTL (ML)
cache:cfo_dashboard:{user_id}:pl_summary -> 5min TTL
cache:cfo_dashboard:{user_id}:budget_variance -> 5min TTL
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Real-time queries | Too slow for complex aggregations; poor UX |
| Pre-computed nightly batch | Data too stale for "real-time" requirement |
| GraphQL subscriptions | Overengineered for 5-minute refresh requirement |

---

## 6. AI/ML Service Integration

### Decision
Standalone FastAPI service exposing REST endpoints, consumed by Laravel Analytics service. MVP uses rule-based predictions with ML models added incrementally.

### Rationale
- **Python Ecosystem**: scikit-learn, pandas, spaCy require Python; no mature PHP equivalents
- **API Boundary**: Clean separation; Laravel calls `/api/predictions/attrition` endpoint
- **MVP Simplicity**: Start with rule-based scoring (e.g., missed reports = +10 attrition risk); ML models trained on accumulated data later

### MVP Rule-Based Attrition Scoring
```python
def calculate_attrition_risk(employee_data: dict) -> float:
    score = 0.0

    if employee_data['missed_reports_last_30_days'] > 2:
        score += 0.3
    if employee_data['performance_trend'] == 'declining':
        score += 0.25
    if employee_data['tenure_months'] < 6:
        score += 0.15
    if employee_data['recent_conflict_flags'] > 0:
        score += 0.2

    return min(score, 1.0)  # Cap at 100%
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| PHP ML libraries (php-ml) | Immature; limited model support |
| External ML SaaS (AWS SageMaker) | Cost; vendor lock-in; simpler to self-host for MVP |
| Embed Python in Laravel (FFI) | Fragile; hard to maintain and debug |

---

## 7. Real-Time Updates Strategy

### Decision
Use Laravel Reverb (WebSocket server) for real-time notifications and dashboard updates. Fallback to polling for unsupported clients.

### Rationale
- **Laravel Reverb**: First-party WebSocket solution in Laravel 11; integrates with broadcasting
- **Selective Broadcasting**: Only broadcast high-value events (conflict alerts, report submissions) not every DB change
- **Graceful Degradation**: Polling fallback at 30-second intervals if WebSocket unavailable

### Events to Broadcast
```php
// Broadcast when conflict detected
class ConflictAlertCreated implements ShouldBroadcast
{
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('dashboard.gm');
    }
}

// Broadcast when report submitted
class ReportSubmitted implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard.ceo'),
            new PrivateChannel('dashboard.cfo'),
        ];
    }
}
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Pusher | External dependency; cost at scale |
| Socket.io with Node.js | Additional runtime; Laravel Reverb is native |
| Server-Sent Events (SSE) | One-directional; WebSocket more flexible |

---

## 8. File Storage and Export Strategy

### Decision
Use MinIO (S3-compatible) for file storage with Laravel's Filesystem abstraction. Generate Excel/PDF server-side using Laravel Excel and DomPDF.

### Rationale
- **MinIO**: Self-hosted S3-compatible storage; works in Docker; easy migration to AWS S3 later
- **Server-Side Generation**: Excel/PDF generated on backend ensures consistent formatting; client downloads pre-rendered file
- **Async Generation**: Large reports queued and user notified when ready

### Export Flow
```
User clicks "Export" → Job queued → Excel/PDF generated → Stored in MinIO →
Notification sent with download link → Link expires in 24 hours
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Client-side generation (jsPDF, SheetJS) | Inconsistent formatting; large data sets crash browser |
| Local filesystem storage | Not scalable; container restarts lose files |
| AWS S3 directly | Requires AWS account; MinIO allows local-first development |

---

## 9. Search Implementation

### Decision
Use Meilisearch for full-text search on projects, employees, and reports. Laravel Scout for integration.

### Rationale
- **Meilisearch**: Fast, typo-tolerant search; easy to self-host; good Laravel Scout driver
- **Scout Integration**: Automatic index sync via model observers; search API via Scout facade
- **Scoped Search**: Search results filtered by user role before returning

### Searchable Models
```php
class Project extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| PostgreSQL full-text search | Less feature-rich; no typo tolerance |
| Elasticsearch | Heavier resource usage; overkill for ~30 users |
| Algolia | External service; cost considerations |

---

## 10. Authentication Strategy

### Decision
Laravel Sanctum with SPA authentication mode for the React frontend. JWT tokens for service-to-service communication.

### Rationale
- **Sanctum SPA Mode**: Cookie-based authentication for same-origin SPA; CSRF protection built-in
- **Service Tokens**: Internal service calls use signed JWT tokens with short TTL
- **Role in Token**: User role included in session/token; reduces DB lookups for authorization

### Auth Flow
```
1. User logs in via /api/login → Sanctum creates session cookie
2. React SPA includes cookie in all requests (same-origin)
3. Backend middleware validates session → loads user with role
4. Service-to-service: Internal JWT signed with shared secret, 1-minute TTL
```

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| JWT-only (no session) | Harder to invalidate; requires token blacklist for logout |
| OAuth2/OpenID Connect | Overkill without external identity provider |
| Laravel Passport | More complex than Sanctum for SPA use case |

---

## Summary of Decisions

| Area | Decision | Key Technology |
|------|----------|----------------|
| Architecture | Microservices with shared DB | Laravel 11 services |
| RBAC | Spatie Permission + Global Scopes | Spatie Laravel-Permission |
| Audit Logging | Model Events + Immutable table | Laravel Observers |
| Conflict Detection | Weekly scheduled job | Laravel Scheduler |
| Dashboard Data | Materialized views + Redis | PostgreSQL, Redis |
| AI/ML | Standalone FastAPI service | Python, FastAPI |
| Real-Time | Laravel Reverb WebSockets | Laravel Reverb |
| File Storage | MinIO (S3-compatible) | MinIO, Laravel Filesystem |
| Search | Meilisearch via Scout | Meilisearch, Laravel Scout |
| Authentication | Sanctum SPA mode | Laravel Sanctum |

---

## Open Questions Resolved

All technical clarifications have been resolved. No NEEDS CLARIFICATION items remain.
