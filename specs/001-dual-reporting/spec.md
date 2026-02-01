# Feature Specification: Dual Independent Reporting System

**Feature Branch**: `001-dual-reporting`
**Created**: 2026-02-01
**Status**: Draft
**Input**: Enterprise Team Management Platform with Dual Independent Reporting System

## User Scenarios & Testing *(mandatory)*

### User Story 1 - SDD Submits Project Report (Priority: P1)

As an SDD (Project Manager), I need to submit weekly project reports that track what each worker accomplished on my project, so that project progress and resource utilization are documented from the project perspective.

**Why this priority**: This is one of the two core data sources (Source A) required for the dual-reporting system. Without project reports, there is no basis for conflict detection.

**Independent Test**: Can be fully tested by an SDD logging in, selecting a project, entering worker accomplishments/hours, and submitting. The report persists and is visible only to the SDD, Directors above them, and C-Level.

**Acceptance Scenarios**:

1. **Given** an authenticated SDD with assigned projects, **When** they navigate to "Submit Project Report," **Then** they see only their assigned projects and workers allocated to those projects.
2. **Given** an SDD completing a project report form, **When** they enter hours and accomplishments for each worker, **Then** the system validates that hours are non-negative and descriptions are non-empty.
3. **Given** an SDD submitting a completed report, **When** they click Submit, **Then** the report is saved with an immutable timestamp, the SDD receives confirmation, and the report cannot be deleted (only amended).
4. **Given** an SDD who submitted a report, **When** they view their report history, **Then** they see all submitted reports and any amendments with full audit trail.

---

### User Story 2 - Department Manager Submits Department Report (Priority: P1)

As a Department Manager, I need to submit weekly department reports that track what each employee in my department worked on, so that employee productivity is documented from the department perspective.

**Why this priority**: This is the second core data source (Source B) required for dual-reporting. Source A and Source B together enable conflict detection.

**Independent Test**: Can be fully tested by a Dept Manager logging in, selecting their department, entering employee work details/hours, and submitting. The report persists and is visible only to the Dept Manager and C-Level.

**Acceptance Scenarios**:

1. **Given** an authenticated Dept Manager, **When** they navigate to "Submit Department Report," **Then** they see only employees in their department.
2. **Given** a Dept Manager completing a department report form, **When** they enter hours and work descriptions for each employee, **Then** the system validates inputs are complete and valid.
3. **Given** a Dept Manager submitting a report, **When** they click Submit, **Then** the report is saved immutably, they receive confirmation, and the report becomes part of the permanent audit trail.
4. **Given** a Dept Manager, **When** they attempt to view any project report (Source A), **Then** the system denies access completely—no visibility into Source A data.

---

### User Story 3 - GM Reviews Conflict Alerts (Priority: P2)

As the General Manager (Ramzi), I need to review automated conflict alerts when Source A and Source B have discrepancies, so I can investigate potential issues like time misreporting or miscommunication.

**Why this priority**: Conflict detection is the core value proposition of dual-reporting. This story delivers the "payoff" of having two independent data sources.

**Independent Test**: Can be tested by having pre-submitted conflicting reports (e.g., SDD reports John worked 40hrs on Project X, Dept Manager reports John worked 20hrs total) and verifying the GM sees an alert with both data points.

**Acceptance Scenarios**:

1. **Given** the weekly validation has run, **When** discrepancies exist between Source A and Source B, **Then** the GM sees a Conflict Alerts dashboard listing each conflict.
2. **Given** a conflict alert, **When** the GM clicks to view details, **Then** they see the specific data from Source A and Source B side-by-side with the calculated discrepancy.
3. **Given** a conflict alert, **When** the GM marks it as "Resolved" with a resolution note, **Then** the resolution is logged immutably and the alert moves to resolved status.
4. **Given** a conflict alert, **When** the GM has not resolved it within 7 days, **Then** the system escalates the alert to CEO/CFO.

---

### User Story 4 - CEO Strategic Dashboard (Priority: P2)

As the CEO, I need a real-time strategic dashboard showing OKR progress, attrition risk, client churn risk, and a one-click board presentation generator, so I can make informed strategic decisions quickly.

**Why this priority**: The CEO dashboard is a key differentiator and "wow factor" for executive buy-in, though it depends on underlying data from Stories 1-3.

**Independent Test**: Can be tested with sample data populating OKRs, employee metrics, and client data, then verifying the CEO can view all widgets and generate a presentation export.

**Acceptance Scenarios**:

1. **Given** an authenticated CEO, **When** they access the CEO Dashboard, **Then** they see real-time OKR tracking with progress percentages and status indicators.
2. **Given** employee data exists, **When** the CEO views the Attrition Prediction widget, **Then** they see employees flagged as high attrition risk with contributing factors.
3. **Given** client data exists, **When** the CEO views the Client Churn Risk widget, **Then** they see clients ranked by churn probability.
4. **Given** the CEO is on their dashboard, **When** they click "Generate Board Presentation," **Then** the system produces a downloadable presentation summarizing key metrics.
5. **Given** an authenticated CEO, **When** they access any data in the system, **Then** they have unrestricted visibility (God-mode) across all projects, departments, and reports.

---

### User Story 5 - CFO Financial Dashboard (Priority: P2)

As the CFO, I need a financial dashboard showing live P&L, cash runway, budget vs actuals per project, ROI analysis, and AR/AP aging, so I can maintain financial oversight and forecasting.

**Why this priority**: The CFO dashboard provides critical financial visibility and is a parallel "wow factor" to the CEO dashboard.

**Independent Test**: Can be tested with sample financial data, verifying the CFO sees accurate P&L, budget comparisons, and aging reports.

**Acceptance Scenarios**:

1. **Given** an authenticated CFO, **When** they access the CFO Dashboard, **Then** they see a live Profit & Loss statement updated with latest transactions.
2. **Given** budget data exists for projects, **When** the CFO views Budget vs Actuals, **Then** they see each project's budgeted amount, actual spend, and variance.
3. **Given** financial data exists, **When** the CFO views Cash Runway, **Then** they see projected months of runway based on current burn rate.
4. **Given** receivables and payables data, **When** the CFO views AR/AP Aging, **Then** they see invoices grouped by aging buckets (0-30, 31-60, 61-90, 90+ days).
5. **Given** an authenticated CFO, **When** they access any data, **Then** they have unrestricted visibility (God-mode) across all financial and operational data.

---

### User Story 6 - Role-Based Data Isolation (Priority: P1)

As a system administrator, the system must enforce strict role-based data isolation to prevent collusion between SDDs and Department Managers.

**Why this priority**: Without strict isolation, the entire dual-reporting integrity is compromised. This is a foundational security requirement.

**Independent Test**: Can be tested by logging in as an SDD and verifying zero visibility into Source B (department reports), and vice versa for Dept Managers and Source A.

**Acceptance Scenarios**:

1. **Given** an authenticated SDD, **When** they attempt any action to view Source B data, **Then** the system returns an access denied error with no data leakage.
2. **Given** an authenticated Dept Manager, **When** they attempt any action to view Source A data, **Then** the system returns an access denied error with no data leakage.
3. **Given** any user, **When** they attempt to access data outside their role permissions, **Then** the access attempt is logged in the security audit trail.
4. **Given** an SDD and a Dept Manager, **When** they compare their visible data in any way, **Then** there is no overlap that would allow them to coordinate or collude.

---

### User Story 7 - Directors Manage SDDs (Priority: P3)

As a Director (Dammy or Mami), I need to oversee the SDDs I manage, view their submitted project reports, and ensure timely submissions.

**Why this priority**: Director oversight is important for management but is not required for the core dual-reporting mechanic.

**Independent Test**: Can be tested by a Director logging in and viewing aggregated reports from their assigned SDDs.

**Acceptance Scenarios**:

1. **Given** an authenticated Director, **When** they access their dashboard, **Then** they see only SDDs assigned to them and those SDDs' project reports.
2. **Given** a Director viewing SDD reports, **When** an SDD has not submitted their weekly report, **Then** the Director sees a "Missing Report" indicator.
3. **Given** a Director, **When** they attempt to view Department Reports (Source B), **Then** the system denies access.

---

### Edge Cases

- What happens when an employee works on multiple projects under different SDDs? Each SDD reports independently on the hours/work for their project; the employee may appear in multiple Source A reports but the total should reconcile with Source B.
- What happens when a Department Manager is also assigned project work? Their own work is tracked by their manager in Source B; they cannot self-report.
- What happens when a conflict cannot be resolved? Conflicts escalate to CEO/CFO after 7 days and remain flagged until explicitly resolved with documented reason.
- What happens when a user's role changes mid-week? Historical reports remain under the original submitter; new reports use new role permissions.
- What happens during the first week before any historical data exists? The system displays empty dashboards with appropriate "No data yet" messaging.

## Requirements *(mandatory)*

### Functional Requirements

**Authentication & Authorization**
- **FR-001**: System MUST authenticate users via email/password with optional enterprise SSO integration.
- **FR-002**: System MUST enforce role-based access control with these roles: CEO, CFO, General Manager, Operations Manager, Director, SDD, Department Manager, Worker.
- **FR-003**: System MUST deny SDDs any access to Source B (Department Reports) data.
- **FR-004**: System MUST deny Department Managers any access to Source A (Project Reports) data.
- **FR-005**: System MUST grant CEO and CFO unrestricted visibility across all data (God-mode).

**Source A - Project Reports**
- **FR-006**: System MUST allow SDDs to create project reports organized by Project → Worker.
- **FR-007**: System MUST track per-worker: hours worked, tasks completed, status, and accomplishments for each project report.
- **FR-008**: System MUST prevent deletion of submitted project reports; only amendments are allowed.
- **FR-009**: System MUST log all project report submissions and amendments with timestamp, user ID, and action type.

**Source B - Department Reports**
- **FR-010**: System MUST allow Department Managers to create department reports organized by Department → Employee.
- **FR-011**: System MUST track per-employee: hours worked, tasks completed, status, and work descriptions for each department report.
- **FR-012**: System MUST prevent deletion of submitted department reports; only amendments are allowed.
- **FR-013**: System MUST log all department report submissions and amendments with timestamp, user ID, and action type.

**Conflict Detection**
- **FR-014**: System MUST run automated validation comparing Source A and Source B at minimum weekly.
- **FR-015**: System MUST flag discrepancies where reported hours or status differ between Source A and Source B for the same employee.
- **FR-016**: System MUST notify the General Manager when conflicts are detected.
- **FR-017**: System MUST escalate unresolved conflicts to CEO/CFO after 7 days.
- **FR-018**: System MUST require resolution notes when marking conflicts as resolved.

**Executive Dashboards**
- **FR-019**: System MUST provide CEO dashboard with OKR tracking, attrition prediction, client churn risk, and board presentation generation.
- **FR-020**: System MUST provide CFO dashboard with P&L, cash runway, budget vs actuals, ROI analysis, and AR/AP aging.
- **FR-021**: System MUST update executive dashboards in real-time or near-real-time (within 5 minutes of data changes).

**Audit & Compliance**
- **FR-022**: System MUST maintain immutable audit logs for all create, update, and access operations.
- **FR-023**: System MUST log failed access attempts with user ID, attempted resource, and timestamp.

### Key Entities

- **User**: Represents a person in the organization with a role (CEO, CFO, GM, Ops Manager, Director, SDD, Dept Manager, Worker), email, and authentication credentials.
- **Project**: A work initiative managed by an SDD, with name, status, assigned workers, budget, and timeline.
- **Department**: An organizational unit (Frontend, Backend, Mobile, AI, BD) managed by a Department Manager, containing employees.
- **Project Report (Source A)**: A submission by an SDD tracking worker accomplishments on a specific project for a reporting period.
- **Department Report (Source B)**: A submission by a Dept Manager tracking employee work for a reporting period.
- **Conflict Alert**: A system-generated flag when Source A and Source B have discrepancies for the same employee.
- **Audit Log Entry**: An immutable record of any system action including timestamp, actor, action type, and affected data.
- **OKR**: Objective and Key Result for strategic tracking on CEO dashboard.
- **Financial Transaction**: Revenue, expense, or budget entry for CFO dashboard calculations.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: SDDs can submit a complete project report in under 10 minutes.
- **SC-002**: Department Managers can submit a complete department report in under 10 minutes.
- **SC-003**: Conflict detection identifies 100% of hour discrepancies greater than 2 hours between Source A and Source B.
- **SC-004**: GM receives conflict alerts within 24 hours of weekly validation completing.
- **SC-005**: CEO can generate a board presentation in under 30 seconds from dashboard.
- **SC-006**: CFO dashboard displays budget vs actuals with variance calculations updated within 5 minutes of new data.
- **SC-007**: Zero instances of SDDs accessing Source B data or Dept Managers accessing Source A data (verified via security audit logs).
- **SC-008**: System supports at least 50 concurrent users submitting reports without performance degradation.
- **SC-009**: All audit log entries are immutable and retrievable for compliance review.
- **SC-010**: Executive dashboards load within 3 seconds for CEO and CFO users.

## Assumptions

- The organization has approximately 10 SDDs and 7 Department Managers as stated, with a workforce distributed across 5 departments (Frontend, Backend, Mobile, AI, BD).
- Weekly reporting cadence is the default; reports are due by end of each week (configurable by admin).
- Standard email/password authentication is the initial auth method; SSO can be added later without architectural changes.
- Financial data for CFO dashboards will be entered manually or imported via CSV until ERP integration is scoped.
- Attrition prediction and client churn risk will use rule-based scoring initially (e.g., missed reports, declining performance ratings) rather than ML models.
- The General Manager (Ramzi) has visibility into both Source A and Source B for conflict resolution purposes.
- Operations Manager (Youcef) has similar visibility to GM for operational oversight.
