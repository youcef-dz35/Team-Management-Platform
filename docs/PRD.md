# Product Requirements Document (PRD)
## Team Management Platform - Dual Independent Reporting System

**Version**: 1.0  
**Last Updated**: February 3, 2026  
**Document Owner**: Product Team  
**Status**: Implemented (MVP)

---

## 1. Executive Summary

### 1.1 Product Vision
A web-based enterprise management platform that implements a dual independent reporting system to ensure data integrity through conflict detection between two isolated reporting sources.

### 1.2 Problem Statement
Organizations need to verify employee work hours and project progress through independent reporting channels to prevent fraud, detect inconsistencies, and maintain accurate organizational metrics. Traditional single-source reporting systems are prone to manipulation and lack cross-validation mechanisms.

### 1.3 Solution Overview
The Team Management Platform implements two isolated reporting workflows:
- **Source A**: Project-based reports submitted by Site Development Directors (SDDs)
- **Source B**: Department-based reports submitted by Department Managers

An automated conflict detection system identifies discrepancies between these sources, alerting executives for resolution.

### 1.4 Success Metrics
- ✅ 100% source isolation (no cross-contamination between A and B)
- ✅ Sub-300ms API response times
- ✅ Automatic conflict detection with <5% false positives
- ✅ Audit trail for all sensitive operations
- ✅ Role-based access control for 8 distinct user types

---

## 2. User Personas

### 2.1 C-Level Executives

#### CEO (Chief Executive Officer)
**Primary Goals:**
- Monitor organizational health and conflicts
- Review audit logs for compliance
- Access all reports from both sources

**Access Level**: Full system access, conflict management, audit logs

#### CFO (Chief Financial Officer)
**Primary Goals:**
- Financial oversight via dual reporting validation
- Audit trail review
- Conflict resolution for budget discrepancies

**Access Level**: Full system access, conflict management, audit logs

### 2.2 Executive Management

#### GM (General Manager)
**Primary Goals:**
- Operational oversight via executive dashboard
- Conflict resolution and escalation management
- Cross-departmental reporting review

**Access Level**: Executive dashboard, conflict management, view-only access to all reports

#### Operations Manager
**Primary Goals:**
- Day-to-day conflict resolution
- Monitor submission rates
- Support SDDs and Department Managers

**Access Level**: Conflict management, operational metrics

### 2.3 Middle Management

#### Site Development Director (SDD)
**Primary Goals:**
- Submit weekly project reports (Source A)
- Track employee hours per project
- Manage project-specific team assignments

**Access Level**: Source A only, project reports, assigned projects

**Restrictions:**
- ❌ Cannot access department reports (Source B)
- ❌ Cannot view conflict alerts
- ❌ Cannot access audit logs

#### Department Manager
**Primary Goals:**
- Submit weekly department reports (Source B)
- Track all department employee hours
- Monitor department performance

**Access Level**: Source B only, department reports, own department employees

**Restrictions:**
- ❌ Cannot access project reports (Source A)
- ❌ Cannot view conflict alerts
- ❌ Cannot access audit logs

### 2.4 Workers
**Primary Goals:**
- Work on assigned projects and departments
- (Future: Self-reporting time entries)

**Access Level**: Limited (future phase)

---

## 3. Functional Requirements

### 3.1 Authentication & Authorization

#### FR-AUTH-001: User Authentication
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Users must authenticate via email/password
- Sessions must be secure with CSRF protection
- Login attempts rate-limited to 5 per minute
- Passwords must be hashed using bcrypt

**Acceptance Criteria:**
- ✅ User can log in with valid credentials
- ✅ Invalid credentials show error message
- ✅ Exceeded rate limit shows throttle message
- ✅ Session persists across page refreshes

#### FR-AUTH-002: Role-Based Access Control
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- System must enforce 8 distinct roles: CEO, CFO, GM, Ops Manager, SDD, Dept Manager, Worker, Director
- Each role has specific route access permissions
- Unauthorized access attempts return 403 Forbidden

**Acceptance Criteria:**
- ✅ CEO can access all routes
- ✅ SDD cannot access /department-reports routes (403)
- ✅ Dept Manager cannot access /project-reports routes (403)
- ✅ Workers cannot access management dashboards

### 3.2 Source A: Project Reports

#### FR-SRC-A-001: Create Project Report
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- SDDs must be able to create new project reports
- Report must include: project selection, reporting period, status (draft/submitted)
- Draft reports can be saved without submission
- Submitted reports cannot be deleted

**Acceptance Criteria:**
- ✅ SDD can select from assigned projects only
- ✅ Reporting period is selectable by week
- ✅ Report saves as draft by default
- ✅ Validation errors shown for incomplete fields

#### FR-SRC-A-002: Add Report Entries
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Each report must contain entries for employees/workers
- Each entry must specify: employee ID, hours worked, date
- Entries can be added, edited, deleted before submission

**Acceptance Criteria:**
- ✅ SDD can add multiple entries to a report
- ✅ Hours worked must be numeric and positive
- ✅ Date must be within reporting period
- ✅ Entries display in table format

#### FR-SRC-A-003: Submit Project Report
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Reports can be submitted when complete
- Submission changes status to "submitted"
- Submitted reports cannot be edited (except via amendment)
- Submission triggers audit log entry

**Acceptance Criteria:**
- ✅ Submit button changes report status
- ✅ Confirmation message shown on success
- ✅ Submitted reports show read-only view
- ✅ Audit log created with timestamp and user

#### FR-SRC-A-004: Amend Submitted Report
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- Submitted reports can be amended with justification
- Amendment requires notes explaining changes
- Amendment creates new version, preserves original

**Acceptance Criteria:**
- ✅ "Amend" button visible on submitted reports
- ✅ Amendment notes field is required
- ✅ Amended report shows amendment history
- ✅ Audit log records amendment action

### 3.3 Source B: Department Reports

#### FR-SRC-B-001: Create Department Report
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Department Managers can create department reports
- Report pre-selects manager's own department
- Reporting period defaults to current week
- Draft/submit workflow same as Source A

**Acceptance Criteria:**
- ✅ Department is pre-selected and locked
- ✅ Cannot create reports for other departments
- ✅ Validation enforces department scoping
- ✅ Same draft/submit workflow as project reports

#### FR-SRC-B-002: Add Department Report Entries
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Entries specify employee, hours, date
- Only department employees are selectable
- Entry management (add/edit/delete)

**Acceptance Criteria:**
- ✅ Employee dropdown shows only dept employees
- ✅ Cannot add entry for employee from other dept
- ✅ Entry validation same as Source A

#### FR-SRC-B-003: Submit & Amend Department Report
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Same submission workflow as Source A
- Amendment process identical to Source A

**Acceptance Criteria:**
- ✅ Submission changes status to "submitted"
- ✅ Amendment requires justification notes
- ✅ Audit logging for all actions

### 3.4 Source Isolation

#### FR-ISO-001: Enforce Source Isolation
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- SDDs must NOT access any Source B endpoints
- Dept Managers must NOT access any Source A endpoints
- Isolation enforced at middleware level
- Violations return 403 Forbidden with audit log

**Acceptance Criteria:**
- ✅ SDD accessing /department-reports returns 403
- ✅ Dept Manager accessing /project-reports returns 403
- ✅ Source isolation middleware logs access violations
- ✅ Frontend hides unauthorized routes

### 3.5 Conflict Detection

#### FR-CONFLICT-001: Automatic Conflict Detection
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- System must automatically detect hour discrepancies
- Conflicts created when employee hours differ between Source A and Source B
- Conflict includes: employee, reporting period, Source A hours, Source B hours, variance
- Detection runs on report submission

**Acceptance Criteria:**
- ✅ Submitted reports trigger conflict check
- ✅ Conflicts created for hour mismatches
- ✅ Conflict shows side-by-side comparison
- ✅ Variance calculated correctly

#### FR-CONFLICT-002: Conflict Resolution
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- Executives (CEO/CFO/GM/Ops Manager) can resolve conflicts
- Resolution requires notes explaining decision
- Resolved conflicts marked with resolver and timestamp
- Status changes: open → escalated (after 7 days) → resolved

**Acceptance Criteria:**
- ✅ Only executives can access conflicts
- ✅ Resolution notes are required
- ✅ Status updates correctly
- ✅ Resolver name and time recorded

#### FR-CONFLICT-003: Conflict Dashboard
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- Executives see conflict statistics
- Filter by status (open/escalated/resolved)
- Sort by date, employee, variance
- Paginated results

**Acceptance Criteria:**
- ✅ Stats show total, open, escalated, resolved counts
- ✅ Filter dropdown functional
- ✅ Sorting works on all columns
- ✅ Pagination shows 20 items per page

### 3.6 Audit Logging

#### FR-AUDIT-001: Comprehensive Audit Trail
**Priority**: P0 (Critical)  
**Status**: ✅ Implemented

**Requirements:**
- All sensitive actions must be logged:
  - User logins
  - Report submissions/amendments
  - Conflict resolutions
  - Source isolation violations
- Logs include: user, action, timestamp, IP address, details

**Acceptance Criteria:**
- ✅ Login events logged with timestamp
- ✅ Report submissions logged with report ID
- ✅ Conflict resolutions logged with notes
- ✅ Audit logs immutable (no deletion)

#### FR-AUDIT-002: Audit Log Review
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- CEO and CFO can view all audit logs
- Filter by user, action type, date range
- Export capability (future)

**Acceptance Criteria:**
- ✅ Only CEO/CFO can access /audit-logs
- ✅ Filter by user shows relevant logs
- ✅ Date range filter works
- ✅ Pagination for large result sets

### 3.7 Dashboards

#### FR-DASH-001: SDD Dashboard
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- Shows assigned projects
- Recent project report submissions
- Reporting period status
- Quick stats (draft/submitted counts)

**Acceptance Criteria:**
- ✅ Dashboard shows only assigned projects
- ✅ Stats accurate and real-time
- ✅ Links to create new report

#### FR-DASH-002: Department Manager Dashboard
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- Shows own department only
- Department employee list
- Recent department report submissions
- Quick stats

**Acceptance Criteria:**
- ✅ Dashboard scoped to own department
- ✅ Employee list current
- ✅ Stats reflect department only

#### FR-DASH-003: Executive Dashboard (GM)
**Priority**: P1 (High)  
**Status**: ✅ Implemented

**Requirements:**
- Company-wide metrics
- Conflict alert summary
- Submission rates for both sources
- Top-level KPIs

**Acceptance Criteria:**
- ✅ Accessible by CEO/CFO/GM/Ops Manager
- ✅ Shows aggregated data from both sources
- ✅ Conflict alerts prominently displayed
- ✅ Real-time or near-real-time updates

---

## 4. Non-Functional Requirements

### 4.1 Performance

#### NFR-PERF-001: API Response Time
**Requirement**: All API endpoints must respond in <300ms on average  
**Status**: ✅ Achieved (238ms average after optimization)

**Measured Performance:**
- Login: 238ms
- Report list: 200-250ms
- Dashboard load: 250-300ms

#### NFR-PERF-002: Page Load Time
**Requirement**: Initial page load <2 seconds on broadband  
**Status**: ⚠️ Needs measurement

#### NFR-PERF-003: Database Query Optimization
**Requirement**: No N+1 queries, proper indexing  
**Status**: ⚠️ Partial (indexes created but not applied)

### 4.2 Security

#### NFR-SEC-001: Data Encryption
**Requirement**: All data in transit encrypted via HTTPS  
**Status**: ❌ Not configured (HTTP only in dev)

#### NFR-SEC-002: Password Security
**Requirement**: Passwords hashed with bcrypt (cost factor 10+)  
**Status**: ✅ Implemented

#### NFR-SEC-003: Session Security
**Requirement**: HTTP-only secure cookies, CSRF protection  
**Status**: ✅ Implemented

#### NFR-SEC-004: Rate Limiting
**Requirement**: Prevent brute force attacks  
**Status**: ✅ Implemented (login endpoint only)

### 4.3 Scalability

#### NFR-SCALE-001: Concurrent Users
**Requirement**: Support 500 concurrent users  
**Status**: ⚠️ Not tested

#### NFR-SCALE-002: Data Volume
**Requirement**: Handle 1M+ report entries  
**Status**: ⚠️ Not tested

### 4.4 Availability

#### NFR-AVAIL-001: Uptime
**Requirement**: 99.5% uptime SLA  
**Status**: ⚠️ No monitoring configured

#### NFR-AVAIL-002: Backup & Recovery
**Requirement**: Daily backups, 4-hour RTO  
**Status**: ❌ Not configured

### 4.5 Usability

#### NFR-UX-001: Browser Compatibility
**Requirement**: Support Chrome, Firefox, Safari, Edge (latest 2 versions)  
**Status**: ⚠️ Needs testing

#### NFR-UX-002: Responsive Design
**Requirement**: Functional on desktop (1920x1080), tablet (768x1024), mobile (375x667)  
**Status**: ✅ Implemented (Tailwind CSS responsive)

#### NFR-UX-003: Accessibility
**Requirement**: WCAG 2.1 Level AA compliance  
**Status**: ⚠️ Not assessed

---

## 5. User Stories

### 5.1 Source A Workflow (SDD)

**US-SDD-001**: Create Weekly Project Report  
**As a** Site Development Director  
**I want to** create a weekly project report for my assigned projects  
**So that** I can track employee hours and project progress

**Acceptance Criteria:**
- ✅ I can select from my assigned projects only
- ✅ I can choose the reporting week
- ✅ I can save as draft before submitting
- ✅ Validation prevents incomplete submissions

**US-SDD-002**: Add Employee Hours  
**As a** Site Development Director  
**I want to** add multiple employee hour entries to a report  
**So that** I can document all work performed on the project

**Acceptance Criteria:**
- ✅ I can add entries for multiple employees
- ✅ I can edit entries before submission
- ✅ I can delete incorrect entries
- ✅ Hours must be positive numbers

**US-SDD-003**: Submit Project Report  
**As a** Site Development Director  
**I want to** submit my completed project report  
**So that** it's recorded in the system and executives can review it

**Acceptance Criteria:**
- ✅ Submit button changes status to "submitted"
- ✅ I receive confirmation message
- ✅ Report becomes read-only after submission
- ✅ I can amend if needed later

### 5.2 Source B Workflow (Dept Manager)

**US-DEPT-001**: Create Weekly Department Report  
**As a** Department Manager  
**I want to** create a weekly report for my department  
**So that** I can track all department employee hours

**Acceptance Criteria:**
- ✅ Department is pre-selected (my department)
- ✅ I can only add entries for my department employees
- ✅ Workflow identical to Source A

**US-DEPT-002**: Cannot Access Project Reports  
**As a** Department Manager  
**I should not** be able to access Source A (project reports)  
**So that** source isolation is maintained

**Acceptance Criteria:**
- ✅ Attempting to access /project-reports returns 403
- ✅ Project report links hidden in UI
- ✅ API calls to Source A endpoints rejected

### 5.3 Executive Workflow

**US-EXEC-001**: Review Conflict Alerts  
**As an** Executive (CEO/CFO/GM)  
**I want to** see a list of all conflict alerts  
**So that** I can identify discrepancies between sources

**Acceptance Criteria:**
- ✅ Dashboard shows conflict count
- ✅ Conflicts filterable by status
- ✅ Each conflict shows variance amount

**US-EXEC-002**: Resolve Conflicts  
**As an** Executive  
**I want to** resolve conflict alerts with notes  
**So that** discrepancies are investigated and documented

**Acceptance Criteria:**
- ✅ Resolution form requires notes
- ✅ Resolution records my name and timestamp
- ✅ Status changes to "resolved"
- ✅ Audit log entry created

**US-EXEC-003**: Review Audit Logs  
**As a** CEO or CFO  
**I want to** view audit logs of all system actions  
**So that** I can ensure compliance and detect fraud

**Acceptance Criteria:**
- ✅ All sensitive actions are logged
- ✅ Logs include user, action, timestamp
- ✅ Logs are filterable and searchable
- ✅ Logs cannot be deleted

---

## 6. System Architecture

### 6.1 Tech Stack

**Frontend:**
- React 18 + TypeScript
- Vite 5 (build tool)
- Tailwind CSS (styling)
- React Query (data fetching)
- Redux Toolkit (state management)

**Backend:**
- Laravel 11 (PHP 8.2)
- PostgreSQL 15 (database)
- Redis 7 (cache/queue/sessions)
- Laravel Sanctum (authentication)

**Infrastructure:**
- Docker Compose (orchestration)
- Nginx (reverse proxy)
- MinIO (S3-compatible storage)
- Meilisearch (search engine)

### 6.2 Data Model (Simplified)

**Users**: id, email, password, role, department_id  
**Projects**: id, name, description  
**Departments**: id, name  
**ProjectReports**: id, project_id, user_id, reporting_period_start, status  
**ProjectReportEntries**: id, report_id, employee_id, hours, date  
**DepartmentReports**: id, department_id, user_id, reporting_period_start, status  
**DepartmentReportEntries**: id, report_id, employee_id, hours, date  
**ConflictAlerts**: id, employee_id, reporting_period, source_a_hours, source_b_hours, status, resolver_id  
**AuditLogs**: id, user_id, action, details, ip_address, timestamp

### 6.3 API Structure

**Base URL**: `/api/v1`

**Public Endpoints:**
- `POST /auth/login`

**Protected Endpoints** (require `auth:sanctum`):
- `GET /auth/me`
- `POST /auth/logout`
- `GET /projects` (all authenticated users)
- `GET /departments` (all authenticated users)

**Source A** (SDD only, `source.a` middleware):
- `GET /project-reports`
- `POST /project-reports`
- `GET /project-reports/{id}`
- `PUT /project-reports/{id}`
- `DELETE /project-reports/{id}`
- `POST /project-reports/{id}/submit`
- `POST /project-reports/{id}/amend`
- Report entries CRUD

**Source B** (Dept Manager only, `source.b` middleware):
- `GET /department-reports`
- Similar CRUD to Source A

**Conflicts** (CEO/CFO/GM/Ops Manager only):
- `GET /conflicts`
- `GET /conflicts/stats`
- `POST /conflicts/{id}/resolve`

**Audit Logs** (CEO/CFO only):
- `GET /audit-logs`

**Dashboards**:
- `GET /dashboard/sdd` (SDD only)
- `GET /dashboard/dept-manager` (Dept Manager only)
- `GET /dashboard/gm` (CEO/CFO/GM/Ops Manager)

---

## 7. Out of Scope (Future Phases)

### Phase 2 Features
- Password reset functionality
- Two-factor authentication (2FA)
- Email notifications for conflicts
- Real-time WebSocket updates
- Mobile app

### Phase 3 Features
- Advanced analytics and BI dashboards
- Predictive conflict detection (ML)
- Automated report generation
- Multi-language support
- Export to Excel/PDF

### Phase 4 Features
- Integration with payroll systems
- AI-powered fraud detection
- Custom reporting builder
- API for third-party integrations

---

## 8. Assumptions & Constraints

### Assumptions
1. All users have company email addresses
2. Reporting periods are weekly (Monday-Sunday)
3. Hours worked are tracked per day (not sub-day granularity)
4. Deployment on private infrastructure (not multi-tenant SaaS)
5. English-only interface

### Constraints
1. No budget for commercial BI tools (using open-source Meilisearch)
2. Must support 500 concurrent users (infrastructure limitation)
3. Deployment via Docker Compose (no Kubernetes initially)
4. Backend must remain Laravel (organizational standard)

---

## 9. Dependencies

### External Services
- SMTP email service (production)
- SSL certificate provider (Let's Encrypt)
- Cloud storage (optional MinIO replacement with AWS S3)

### Third-Party Libraries
- Laravel Sanctum (authentication)
- Laravel Scout (search)
- React Query (data fetching)
- Tailwind CSS (styling)

---

## 10. Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Source isolation breach | CRITICAL | Low | Comprehensive middleware testing, audit logging |
| Performance degradation under load | HIGH | Medium | Load testing, database indexes, caching |
| Data loss (no backups) | CRITICAL | Medium | **Implement automated daily backups** |
| Security breach (no HTTPS) | CRITICAL | High | **Enable SSL before production** |
| Conflict detection false positives | MEDIUM | Medium | Tunable variance threshold, manual review |

---

## 11. Success Criteria

### MVP Launch Criteria (All Must Pass)
- ✅ All P0 features implemented
- ✅ Source isolation 100% enforced
- ✅ API response times <300ms
- ⚠️ Security audit passed (needs SSL/TLS)
- ⚠️ Load testing passed (not tested)
- ❌ Automated backups configured
- ❌ Production environment setup

### Post-Launch Metrics (3 months)
- 95% user adoption (all SDDs and Dept Managers using system)
- <1% source isolation violations
- <10% conflict rate (hour discrepancies)
- 99.5% uptime
- <5 critical bugs reported

---

## 12. Timeline (Retrospective - Already Built)

**Discovery & Planning**: 2 days  
**Backend API Development**: 5 days  
**Frontend Development**: 5 days  
**Conflict Detection Logic**: 2 days  
**Testing & Bug Fixes**: 3 days  
**Performance Optimization**: 1 day  
**Documentation**: 1 day  

**Total MVP Build Time**: ~3 weeks

---

## 13. Appendix

### A. Glossary
- **Source A**: Project-based reporting system (SDD workflow)
- **Source B**: Department-based reporting system (Dept Manager workflow)
- **SDD**: Site Development Director
- **Conflict**: Detected hour discrepancy between Source A and Source B
- **RBAC**: Role-Based Access Control

### B. References
- Production Readiness Report: [`production_readiness_report.md`](file:///C:/Users/dz-mr/.gemini/antigravity/brain/1dc0a2f2-57c9-4010-a8d8-b9ce2d55f6aa/production_readiness_report.md)
- Performance Optimization: [`walkthrough.md`](file:///C:/Users/dz-mr/.gemini/antigravity/brain/1dc0a2f2-57c9-4010-a8d8-b9ce2d55f6aa/walkthrough.md)
- API Routes: [`backend/routes/api.php`](file:///C:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/backend/routes/api.php)
