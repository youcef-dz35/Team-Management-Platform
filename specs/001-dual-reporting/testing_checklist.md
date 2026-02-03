# Testing Checklist: Dual Independent Reporting System

**Feature**: Dual Reporting (Source A/B Isolation & Conflict Detection)
**Status**: Ready for Verification
**Test Environment**: Localhost (Docker)

## 1. Environment Setup Verification
- [ ] **Database & Seeds**: Run `php artisan migrate:fresh --seed`
- [ ] **Infrastructure**: Verify all containers are running (`docker-compose ps`)
    - [ ] `tmp-backend` (Laravel)
    - [ ] `tmp-frontend` (React)
    - [ ] `tmp-postgres`
    - [ ] `tmp-redis`
    - [ ] `tmp-meilisearch`
    - [ ] `tmp-ai-service`
- [ ] **Credentials**: Verify test users exist (Password: `password`)
    - SDD: `sdd1@example.com`
    - Dept Mgr: `deptmgr.frontend@example.com`
    - GM: `gm@example.com`
    - CEO: `ceo@example.com`
    - CFO: `cfo@example.com`

## 2. Role-Based Access Control (RBAC) & Isolation
**Goal**: Verify strict separation of Source A and Source B.

### Test Case 2.1: SDD Isolation (Source A)
- [ ] **Login** as `sdd1@example.com`
- [ ] **Verify Dashboard**: Should see "SDD Dashboard"
- [ ] **Access Project Reports**:
    - [ ] Navigate to `/project-reports` -> Should succeed
    - [ ] Click "Create Report" -> Should open form
- [ ] **Attempt Unauthorized Access (Source B)**:
    - [ ] Try direct URL `/department-reports` -> Should get 403 Forbidden / Access Denied
    - [ ] Verify no "Department Reports" link in sidebar

### Test Case 2.2: Department Manager Isolation (Source B)
- [ ] **Login** as `deptmgr.frontend@example.com`
- [ ] **Verify Dashboard**: Should see "Department Manager Dashboard"
- [ ] **Access Department Reports**:
    - [ ] Navigate to `/department-reports` -> Should succeed
    - [ ] Click "Create Report" -> Should open form
- [ ] **Attempt Unauthorized Access (Source A)**:
    - [ ] Try direct URL `/project-reports` -> Should get 403 Forbidden / Access Denied
    - [ ] Verify no "Project Reports" link in sidebar

## 3. Functional Workflows

### Test Case 3.1: SDD Reporting Flow (User Story 1)
- [ ] **Login** as `sdd1@example.com`
- [ ] **Create Report**:
    - [ ] Select Project: "Backend Replatforming" (assigned to SDD1)
    - [ ] Reporting Period: Current Week
    - [ ] **Add Entry**:
        - Worker: "Worker Backend 1"
        - Hours: 40
        - Status: Completed
        - Accomplishments: "API Refactor"
    - [ ] **Submit**: Click "Submit Report"
- [ ] **Verify**:
    - [ ] Toast notification "Report submitted successfully"
    - [ ] Redirect to Report View
    - [ ] Status shows "Submitted"
    - [ ] **Edit**: Verify "Edit" button is disabled/gone (Immutable)
    - [ ] **Amend**: Verify "Amend" button exists

### Test Case 3.2: Dept Manager Reporting Flow (User Story 2)
- [ ] **Login** as `deptmgr.frontend@example.com`
- [ ] **Create Report**:
    - [ ] Select Department: "Frontend"
    - [ ] Reporting Period: Current Week
    - [ ] **Add Entry**:
        - Employee: "Worker Frontend 1"
        - Hours: 35 (Note: Intentionally different from Source A if testing conflict)
        - Tasks: "UI Components"
    - [ ] **Submit**: Click "Submit Report"
- [ ] **Verify**:
    - [ ] Toast notification "Report submitted successfully"
    - [ ] Status shows "Submitted"

## 4. Conflict Detection (User Story 3)
**Prerequisite**: Complete 3.1 and 3.2 with conflicting data for the same worker (e.g. Worker Backend 1).
*Note: You may need to adjust users to match seeds.*

- [ ] **Trigger Validation**:
    - [ ] Run command: `php artisan app:run-conflict-detection` (or wait for scheduler)
- [ ] **Login** as `gm@example.com`
- [ ] **Check Alerts**:
    - [ ] Navigate to "Conflict Alerts"
    - [ ] Verify new alert appears for the worker
- [ ] **Resolve Conflict**:
    - [ ] Click "View Details"
    - [ ] Compare Source A (40 hrs) vs Source B (35 hrs)
    - [ ] Click "Resolve"
    - [ ] Enter Note: "SDD confirmed correct hours"
    - [ ] Submit
- [ ] **Verify**: Alert moves to "Resolved" tab.

## 5. Executive Dashboards (User Stories 4 & 5)

### Test Case 5.1: CEO Dashboard
- [ ] **Login** as `ceo@example.com`
- [ ] **Verify Widgets**:
    - [ ] OKR Progress
    - [ ] Attrition Risk
    - [ ] Client Churn
- [ ] **God Mode**:
    - [ ] Access `/project-reports` -> Should succeed
    - [ ] Access `/department-reports` -> Should succeed

### Test Case 5.2: CFO Dashboard
- [ ] **Login** as `cfo@example.com`
- [ ] **Verify Widgets**:
    - [ ] P&L Summary
    - [ ] Cash Runway
    - [ ] Budget vs Actuals

## 6. UI/UX Scan
- [ ] **Responsiveness**: Check layout on mobile view (devtools)
- [ ] **Loading States**: Check spinners appear during data fetch
- [ ] **Error Handling**:
    - [ ] Try creating report with 0 hours -> Verify validation error
    - [ ] Try submitting empty form -> Verify required field errors
- [ ] **Navigation**: Verify active states on sidebar links

## 7. Automated Test Validation
- [ ] Run `php artisan test` -> Expect ALL GREEN
