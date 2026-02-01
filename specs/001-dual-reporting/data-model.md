# Data Model: Dual Independent Reporting System

**Branch**: `001-dual-reporting` | **Date**: 2026-02-01

## Overview

This document defines the database schema for the Dual Independent Reporting System. The schema enforces Constitution principles, particularly:
- **Principle III**: Source A and Source B use completely separate tables
- **Principle II**: Immutable audit trail via `audit_logs` table
- **Principle I**: RBAC via `roles`, `permissions`, and relationship tables

---

## Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     users       │       │   departments   │       │    projects     │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │       │ id              │
│ email           │       │ name            │       │ name            │
│ password        │       │ manager_id (FK) │───────│ sdd_id (FK)     │
│ name            │       │ created_at      │       │ status          │
│ role_id (FK)    │───┐   │ updated_at      │       │ budget          │
│ department_id   │───┼───│ deleted_at      │       │ start_date      │
│ created_at      │   │   └─────────────────┘       │ end_date        │
│ updated_at      │   │                             │ created_at      │
│ deleted_at      │   │                             │ updated_at      │
└─────────────────┘   │                             │ deleted_at      │
         │            │                             └─────────────────┘
         │            │                                      │
         │            │   ┌─────────────────────┐            │
         │            └───│  project_assignments │───────────┘
         │                ├─────────────────────┤
         │                │ id                  │
         │                │ project_id (FK)     │
         │                │ user_id (FK)        │
         │                │ allocated_hours     │
         │                │ created_at          │
         │                └─────────────────────┘

═══════════════════════════════════════════════════════════════════════
                    SOURCE A (Project Reports)
═══════════════════════════════════════════════════════════════════════

┌───────────────────────┐       ┌───────────────────────────┐
│   project_reports     │       │  project_report_entries   │
├───────────────────────┤       ├───────────────────────────┤
│ id                    │       │ id                        │
│ project_id (FK)       │───────│ project_report_id (FK)    │
│ submitted_by (FK)     │       │ employee_id (FK)          │
│ reporting_period_start│       │ hours_worked              │
│ reporting_period_end  │       │ tasks_completed           │
│ status                │       │ status                    │
│ submitted_at          │       │ accomplishments           │
│ created_at            │       │ created_at                │
│ updated_at            │       │ updated_at                │
└───────────────────────┘       └───────────────────────────┘

═══════════════════════════════════════════════════════════════════════
                   SOURCE B (Department Reports)
═══════════════════════════════════════════════════════════════════════

┌────────────────────────┐      ┌────────────────────────────┐
│  department_reports    │      │ department_report_entries  │
├────────────────────────┤      ├────────────────────────────┤
│ id                     │      │ id                         │
│ department_id (FK)     │──────│ department_report_id (FK)  │
│ submitted_by (FK)      │      │ employee_id (FK)           │
│ reporting_period_start │      │ hours_worked               │
│ reporting_period_end   │      │ tasks_completed            │
│ status                 │      │ status                     │
│ submitted_at           │      │ work_description           │
│ created_at             │      │ created_at                 │
│ updated_at             │      │ updated_at                 │
└────────────────────────┘      └────────────────────────────┘

═══════════════════════════════════════════════════════════════════════
                      CONFLICT DETECTION
═══════════════════════════════════════════════════════════════════════

┌─────────────────────────┐
│    conflict_alerts      │
├─────────────────────────┤
│ id                      │
│ employee_id (FK)        │
│ reporting_period_start  │
│ reporting_period_end    │
│ source_a_hours          │
│ source_b_hours          │
│ discrepancy             │
│ status                  │ (open, resolved, escalated)
│ resolved_by (FK)        │
│ resolution_notes        │
│ resolved_at             │
│ escalated_at            │
│ created_at              │
└─────────────────────────┘
```

---

## Core Tables

### users

Central user table for all roles.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Login email |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| name | VARCHAR(255) | NOT NULL | Full name |
| employee_id | VARCHAR(50) | UNIQUE | HR employee ID |
| department_id | BIGINT | FK → departments.id | Employee's department |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |
| deleted_at | TIMESTAMPTZ | | Soft delete |

**Indexes**: `email`, `employee_id`, `department_id`

### roles

Defines available roles in the system.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| name | VARCHAR(50) | UNIQUE, NOT NULL | Role identifier |
| display_name | VARCHAR(100) | | Human-readable name |
| guard_name | VARCHAR(255) | DEFAULT 'web' | Spatie Permission guard |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Seed Data**:
- `ceo` - Chief Executive Officer
- `cfo` - Chief Financial Officer
- `gm` - General Manager
- `ops_manager` - Operations Manager
- `director` - Director
- `sdd` - Service Delivery Director (Project Manager)
- `dept_manager` - Department Manager
- `worker` - Employee/Worker

### model_has_roles

Spatie Permission pivot table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | BIGINT | FK → roles.id | |
| model_type | VARCHAR(255) | | Polymorphic type |
| model_id | BIGINT | | User ID |

**Primary Key**: (role_id, model_id, model_type)

### departments

Organizational departments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| name | VARCHAR(100) | UNIQUE, NOT NULL | Department name |
| manager_id | BIGINT | FK → users.id | Department Manager |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |
| deleted_at | TIMESTAMPTZ | | Soft delete |

**Seed Data**: Frontend, Backend, Mobile, AI, BD (Business Development)

### projects

Project entities managed by SDDs.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| name | VARCHAR(255) | NOT NULL | Project name |
| description | TEXT | | Project description |
| sdd_id | BIGINT | FK → users.id, NOT NULL | Assigned SDD |
| status | VARCHAR(50) | DEFAULT 'active' | active, completed, on_hold, cancelled |
| budget | DECIMAL(15,2) | | Total budget |
| start_date | DATE | | |
| end_date | DATE | | |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |
| deleted_at | TIMESTAMPTZ | | Soft delete |

**Indexes**: `sdd_id`, `status`

### project_assignments

Links workers to projects.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| project_id | BIGINT | FK → projects.id | |
| user_id | BIGINT | FK → users.id | Worker assigned |
| allocated_hours | DECIMAL(8,2) | | Weekly hours allocated |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Unique Constraint**: (project_id, user_id)

---

## Source A Tables (Project Reports)

### project_reports

Header table for project-based reports submitted by SDDs.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| project_id | BIGINT | FK → projects.id, NOT NULL | |
| submitted_by | BIGINT | FK → users.id, NOT NULL | SDD who submitted |
| reporting_period_start | DATE | NOT NULL | Period start (Monday) |
| reporting_period_end | DATE | NOT NULL | Period end (Sunday) |
| status | VARCHAR(50) | DEFAULT 'draft' | draft, submitted, amended |
| submitted_at | TIMESTAMPTZ | | When first submitted |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Unique Constraint**: (project_id, reporting_period_start, reporting_period_end)
**Indexes**: `submitted_by`, `reporting_period_start`, `status`

**NOTE**: No `deleted_at` column. Reports cannot be deleted per Constitution Principle II.

### project_report_entries

Line items in a project report (one per worker).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| project_report_id | BIGINT | FK → project_reports.id | |
| employee_id | BIGINT | FK → users.id, NOT NULL | Worker being reported on |
| hours_worked | DECIMAL(5,2) | NOT NULL, CHECK >= 0 | Hours worked this period |
| tasks_completed | INTEGER | DEFAULT 0 | Count of tasks completed |
| status | VARCHAR(50) | | on_track, at_risk, blocked |
| accomplishments | TEXT | | Narrative description |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Unique Constraint**: (project_report_id, employee_id)
**Indexes**: `employee_id`, `hours_worked`

### project_report_amendments

Tracks amendments to submitted project reports.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| project_report_id | BIGINT | FK → project_reports.id | |
| amended_by | BIGINT | FK → users.id | |
| amendment_reason | TEXT | NOT NULL | Why amended |
| changes | JSONB | NOT NULL | Before/after values |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |

---

## Source B Tables (Department Reports)

### department_reports

Header table for department-based reports submitted by Department Managers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| department_id | BIGINT | FK → departments.id, NOT NULL | |
| submitted_by | BIGINT | FK → users.id, NOT NULL | Dept Manager who submitted |
| reporting_period_start | DATE | NOT NULL | Period start (Monday) |
| reporting_period_end | DATE | NOT NULL | Period end (Sunday) |
| status | VARCHAR(50) | DEFAULT 'draft' | draft, submitted, amended |
| submitted_at | TIMESTAMPTZ | | When first submitted |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Unique Constraint**: (department_id, reporting_period_start, reporting_period_end)
**Indexes**: `submitted_by`, `reporting_period_start`, `status`

**NOTE**: No `deleted_at` column. Reports cannot be deleted per Constitution Principle II.

### department_report_entries

Line items in a department report (one per employee).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| department_report_id | BIGINT | FK → department_reports.id | |
| employee_id | BIGINT | FK → users.id, NOT NULL | Employee being reported on |
| hours_worked | DECIMAL(5,2) | NOT NULL, CHECK >= 0 | Hours worked this period |
| tasks_completed | INTEGER | DEFAULT 0 | Count of tasks completed |
| status | VARCHAR(50) | | productive, underperforming, on_leave |
| work_description | TEXT | | Narrative of work done |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

**Unique Constraint**: (department_report_id, employee_id)
**Indexes**: `employee_id`, `hours_worked`

### department_report_amendments

Tracks amendments to submitted department reports.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| department_report_id | BIGINT | FK → department_reports.id | |
| amended_by | BIGINT | FK → users.id | |
| amendment_reason | TEXT | NOT NULL | Why amended |
| changes | JSONB | NOT NULL | Before/after values |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |

---

## Conflict Detection Tables

### conflict_alerts

Stores detected discrepancies between Source A and Source B.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| employee_id | BIGINT | FK → users.id, NOT NULL | Employee with discrepancy |
| reporting_period_start | DATE | NOT NULL | |
| reporting_period_end | DATE | NOT NULL | |
| source_a_hours | DECIMAL(5,2) | NOT NULL | Sum from project reports |
| source_b_hours | DECIMAL(5,2) | NOT NULL | From department report |
| discrepancy | DECIMAL(5,2) | NOT NULL | source_a - source_b |
| status | VARCHAR(50) | DEFAULT 'open' | open, resolved, escalated |
| resolved_by | BIGINT | FK → users.id | CEO/CFO/GM who resolved |
| resolution_notes | TEXT | | Explanation of resolution |
| resolved_at | TIMESTAMPTZ | | |
| escalated_at | TIMESTAMPTZ | | When escalated to CEO/CFO |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |

**Indexes**: `employee_id`, `status`, `created_at`

### validation_runs

Logs each conflict detection run.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| reporting_period_start | DATE | NOT NULL | |
| reporting_period_end | DATE | NOT NULL | |
| employees_checked | INTEGER | | Count of employees compared |
| conflicts_found | INTEGER | | Count of new conflicts |
| run_duration_ms | INTEGER | | Execution time |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |

---

## Audit & Compliance Tables

### audit_logs

Immutable audit trail for all system actions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| auditable_type | VARCHAR(255) | NOT NULL | Model class name |
| auditable_id | BIGINT | NOT NULL | Model ID |
| action | VARCHAR(50) | NOT NULL | created, updated, amended, accessed |
| user_id | BIGINT | FK → users.id | Actor |
| user_role | VARCHAR(50) | NOT NULL | Role at time of action |
| old_values | JSONB | | Previous state (for updates) |
| new_values | JSONB | | New state |
| ip_address | INET | | Client IP |
| user_agent | TEXT | | Browser/client info |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |

**Indexes**: `auditable_type, auditable_id`, `user_id`, `created_at`

**Database-Level Protection**:
```sql
-- Revoke UPDATE and DELETE on audit_logs
REVOKE UPDATE, DELETE ON audit_logs FROM app_user;
```

---

## Financial Tables

### budgets

Project and department budgets.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| budgetable_type | VARCHAR(255) | NOT NULL | Project or Department |
| budgetable_id | BIGINT | NOT NULL | |
| fiscal_year | INTEGER | NOT NULL | |
| amount | DECIMAL(15,2) | NOT NULL | |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

### transactions

Financial transactions for P&L tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| type | VARCHAR(50) | NOT NULL | revenue, expense |
| category | VARCHAR(100) | | e.g., salary, software, client_payment |
| project_id | BIGINT | FK → projects.id | If project-related |
| department_id | BIGINT | FK → departments.id | If department-related |
| amount | DECIMAL(15,2) | NOT NULL | |
| description | TEXT | | |
| transaction_date | DATE | NOT NULL | |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |
| deleted_at | TIMESTAMPTZ | | Soft delete |

### invoices

Accounts receivable/payable.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| type | VARCHAR(50) | NOT NULL | receivable, payable |
| client_vendor_name | VARCHAR(255) | NOT NULL | |
| project_id | BIGINT | FK → projects.id | |
| amount | DECIMAL(15,2) | NOT NULL | |
| issue_date | DATE | NOT NULL | |
| due_date | DATE | NOT NULL | |
| paid_date | DATE | | |
| status | VARCHAR(50) | DEFAULT 'pending' | pending, paid, overdue |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

---

## Executive Dashboard Tables

### okrs

Objectives and Key Results for CEO dashboard.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| objective | TEXT | NOT NULL | The objective statement |
| owner_id | BIGINT | FK → users.id | Responsible person |
| period_start | DATE | | OKR period start |
| period_end | DATE | | OKR period end |
| status | VARCHAR(50) | DEFAULT 'on_track' | on_track, at_risk, achieved, missed |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

### key_results

Key results linked to OKRs.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| okr_id | BIGINT | FK → okrs.id | |
| description | TEXT | NOT NULL | Key result description |
| target_value | DECIMAL(15,2) | | Target metric |
| current_value | DECIMAL(15,2) | DEFAULT 0 | Current progress |
| unit | VARCHAR(50) | | %, count, currency, etc. |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |

### clients

Client data for churn prediction.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | |
| name | VARCHAR(255) | NOT NULL | |
| contract_start | DATE | | |
| contract_end | DATE | | |
| monthly_revenue | DECIMAL(15,2) | | |
| health_score | DECIMAL(3,2) | | 0.00 to 1.00 |
| churn_risk | DECIMAL(3,2) | | Predicted churn probability |
| last_interaction | DATE | | |
| created_at | TIMESTAMPTZ | DEFAULT NOW() | |
| updated_at | TIMESTAMPTZ | | |
| deleted_at | TIMESTAMPTZ | | Soft delete |

---

## Materialized Views

### mv_ceo_dashboard_summary

Pre-computed CEO dashboard metrics.

```sql
CREATE MATERIALIZED VIEW mv_ceo_dashboard_summary AS
SELECT
    (SELECT COUNT(*) FROM projects WHERE status = 'active') as active_projects,
    (SELECT COUNT(*) FROM conflict_alerts WHERE status = 'open') as open_conflicts,
    (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_employees,
    (SELECT AVG(current_value / NULLIF(target_value, 0)) FROM key_results) as avg_okr_progress,
    (SELECT COUNT(*) FROM users WHERE attrition_risk > 0.7) as high_attrition_count,
    NOW() as refreshed_at;
```

### mv_cfo_dashboard_summary

Pre-computed CFO dashboard metrics.

```sql
CREATE MATERIALIZED VIEW mv_cfo_dashboard_summary AS
SELECT
    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'revenue'
     AND transaction_date >= date_trunc('month', CURRENT_DATE)) as mtd_revenue,
    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'expense'
     AND transaction_date >= date_trunc('month', CURRENT_DATE)) as mtd_expenses,
    (SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE type = 'receivable' AND status = 'pending') as outstanding_ar,
    (SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE type = 'payable' AND status = 'pending') as outstanding_ap,
    NOW() as refreshed_at;
```

---

## State Transitions

### Report Status Flow

```
draft → submitted → amended
         ↑______________|
```

- `draft`: Initial state, editable by submitter
- `submitted`: Locked, immutable, triggers conflict detection eligibility
- `amended`: Post-submission correction, original preserved, new values recorded

### Conflict Alert Status Flow

```
open → resolved
  ↓
escalated → resolved
```

- `open`: Newly detected, awaiting GM review
- `escalated`: Auto-escalated after 7 days to CEO/CFO
- `resolved`: Closed with resolution notes

---

## Validation Rules

| Entity | Field | Rule |
|--------|-------|------|
| project_report_entries | hours_worked | >= 0, <= 168 (max hours/week) |
| department_report_entries | hours_worked | >= 0, <= 168 |
| conflict_alerts | discrepancy threshold | Flag if abs(source_a - source_b) > 2 hours |
| invoices | due_date | >= issue_date |
| okrs | period_end | >= period_start |
