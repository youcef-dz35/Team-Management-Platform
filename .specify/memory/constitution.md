<!--
  SYNC IMPACT REPORT
  ==================
  Version change: N/A → 1.0.0
  Bump type: MAJOR (initial constitution ratification)

  Added Principles:
    - I. Zero Trust Architecture (new)
    - II. Data Integrity (new)
    - III. Dual-Reporting Core (new)
    - IV. Validation First (new)
    - V. Tech Standard (new)

  Added Sections:
    - Security & Access Control Requirements
    - Development Workflow
    - Governance

  Removed Sections: N/A (initial version)

  Templates Requiring Updates:
    - .specify/templates/plan-template.md ✅ (no updates needed - Constitution Check
      section is generic and will reference this constitution)
    - .specify/templates/spec-template.md ✅ (no updates needed - generic template)
    - .specify/templates/tasks-template.md ✅ (no updates needed - generic template)

  Follow-up TODOs: None
-->

# Team Management Platform Constitution

## Core Principles

### I. Zero Trust Architecture

All access control decisions MUST follow strict Role-Based Access Control (RBAC) with the
following non-negotiable rules:

- **CEO and CFO**: MUST have unrestricted ("God-mode") visibility across all reports,
  departments, and projects without exception.
- **SDDs (Project Managers)**: MUST be siloed to Project-Based Reports (Source A) only.
  SDDs MUST NOT have access to Department-Based Reports (Source B) under any circumstance.
- **Department Managers**: MUST be siloed to Department-Based Reports (Source B) only.
  Department Managers MUST NOT have access to Project-Based Reports (Source A) under any
  circumstance.
- **Collusion Prevention**: Cross-silo visibility between SDDs and Department Managers
  is explicitly forbidden to prevent reporting collusion.
- **Authentication**: Every API endpoint and UI route MUST validate user role before
  returning data. Unauthenticated requests MUST be rejected.

**Rationale**: Dual independent reporting requires strict separation to maintain report
integrity and detect discrepancies that could indicate fraud or errors.

### II. Data Integrity

All system actions MUST maintain an immutable audit trail:

- **Immutability**: Reports, once submitted, MUST NOT be deletable. Corrections MUST be
  recorded as amendments linked to the original report.
- **Audit Trail**: Every create, update, and amendment operation MUST log: timestamp,
  user ID, user role, action type, previous value, new value.
- **Transparency**: Amendment history MUST be visible to users with appropriate access
  (CEO/CFO see all; role-specific users see their own silo's amendments).
- **Soft Deletes Only**: If "deletion" is required for UX, implement as soft-delete with
  `deleted_at` timestamp. Original data MUST be preserved and auditable.

**Rationale**: Immutable audit trails ensure accountability and enable forensic analysis
when discrepancies are detected between Source A and Source B.

### III. Dual-Reporting Core

The database schema MUST support two independent truth sources:

- **Source A (Project-Based Reports)**: Managed by SDDs/Project Managers. Contains
  project-centric metrics, timelines, resource allocations, and deliverables.
- **Source B (Department-Based Reports)**: Managed by Department Managers. Contains
  department-centric metrics, headcount, budget utilization, and operational data.
- **Independence Requirement**: Source A and Source B MUST NEVER auto-populate from
  each other. Each source MUST be manually entered by its designated role.
- **No Cross-Contamination**: Database triggers, application logic, or any automated
  process MUST NOT synchronize or copy data between sources.
- **Separate Tables**: Source A and Source B MUST use distinct database tables (not
  merely different rows in the same table).

**Rationale**: Independent data entry by separate roles enables discrepancy detection
that would be impossible with auto-populated or synchronized data.

### IV. Validation First

Automated conflict detection MUST be implemented as a core system feature:

- **Weekly Validation**: Automated scripts MUST run at minimum weekly to compare Source A
  and Source B for discrepancies.
- **Discrepancy Flags**: When validation detects conflicts (e.g., project hours vs
  department hours mismatch), the system MUST flag them for CEO/CFO review.
- **Alert System**: Flagged discrepancies MUST generate notifications to CEO and CFO.
- **Resolution Tracking**: Discrepancy flags MUST remain open until explicitly resolved
  by CEO or CFO with documented resolution reason.
- **Validation Logs**: All validation runs MUST be logged with timestamp, comparisons
  performed, discrepancies found, and alert status.

**Rationale**: Automated validation is the enforcement mechanism for the dual-reporting
architecture, making discrepancies visible to executive leadership.

### V. Tech Standard

The project MUST use the "Antigravity" stack without substitution:

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: React with Vite build tooling
- **Infrastructure**: Docker Compose for local development and deployment orchestration
- **Database**: PostgreSQL (no MySQL, SQLite in development only for testing)
- **No Substitutions**: These technology choices are non-negotiable. Proposals to swap
  components MUST be rejected unless this constitution is formally amended.

**Rationale**: Standardized technology reduces onboarding friction, ensures consistent
development practices, and simplifies infrastructure management.

## Security & Access Control Requirements

All implementations MUST satisfy these security constraints derived from the Zero Trust
Architecture principle:

- **Middleware Enforcement**: Role-based access checks MUST be implemented at the
  middleware/policy layer, not scattered across controllers.
- **Query Scoping**: Database queries MUST automatically scope to permitted data based
  on authenticated user's role. SDDs MUST NOT be able to query Source B tables.
- **API Design**: Endpoints MUST NOT expose `role` or `silo` as query parameters that
  users can manipulate. Role determination MUST come from authenticated session only.
- **Frontend Guards**: React routes MUST implement role-based guards, but these are
  UX conveniences only. Backend MUST be the authoritative access control layer.
- **Secrets Management**: Database credentials, API keys, and sensitive configuration
  MUST be managed via environment variables, never committed to version control.

## Development Workflow

- **Branch Strategy**: Feature branches from `main`, PRs required for merge.
- **Testing Requirements**: All RBAC logic MUST have integration tests verifying role
  isolation. Validation scripts MUST have unit tests.
- **Database Migrations**: Schema changes MUST use Laravel migrations. Direct database
  manipulation in production is forbidden.
- **Docker First**: Local development MUST use Docker Compose. "Works on my machine"
  without Docker is not acceptable.
- **Code Review Focus**: PRs touching access control, audit logging, or validation MUST
  receive explicit sign-off confirming compliance with this constitution.

## Governance

This constitution is the authoritative source for architectural and policy decisions in
the Team Management Platform project.

- **Supremacy**: This constitution supersedes all other documentation when conflicts arise.
- **Amendment Process**: Changes to this constitution require:
  1. Written proposal documenting the change and rationale
  2. Impact analysis on existing implementations
  3. Explicit approval from project stakeholders
  4. Version increment following semantic versioning
  5. Migration plan for affected code if principles change
- **Compliance Review**: All pull requests MUST be evaluated for constitution compliance.
  Reviewers MUST verify RBAC, audit trail, dual-reporting independence, and tech stack
  adherence.
- **Versioning Policy**:
  - MAJOR: Principle removal, redefinition, or backward-incompatible governance change
  - MINOR: New principle added or existing principle materially expanded
  - PATCH: Clarifications, typo fixes, non-semantic refinements

**Version**: 1.0.0 | **Ratified**: 2026-02-01 | **Last Amended**: 2026-02-01
