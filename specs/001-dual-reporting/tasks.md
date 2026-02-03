# Tasks: Dual Independent Reporting System - Production-Ready MVP

**Input**: Design documents from `/specs/001-dual-reporting/`
**Prerequisites**: plan.md, spec.md, data-model.md, contracts/, research.md
**Scope**: Complete MVP with User Stories 1-3 (P1-P2), RBAC, and core functionality

## Format: `[ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[US#]**: User story this task belongs to (required for story phases)
- File paths are relative to repository root

## Path Conventions

Based on existing project structure:
- **Backend (Laravel)**: `backend/`
- **Frontend (React)**: `frontend/`
- **Infrastructure**: `docker/`, `./docker-compose.yml`

---

## Phase 1: Setup (Project Foundation Completion)

**Purpose**: Complete missing infrastructure and configuration

- [x] T001 Create docker-compose.override.yml for dev-specific settings in `./docker-compose.override.yml`
- [x] T002 [P] Create backend/database/seeders/RoleSeeder.php with all 8 roles (ceo, cfo, gm, ops_manager, director, sdd, dept_manager, worker) in `backend/database/seeders/RoleSeeder.php`
- [x] T003 [P] Create backend/database/seeders/UserSeeder.php with test users for each role in `backend/database/seeders/UserSeeder.php`
- [x] T004 [P] Create backend/database/seeders/DepartmentSeeder.php with 5 departments (Frontend, Backend, Mobile, AI, BD) in `backend/database/seeders/DepartmentSeeder.php`
- [x] T005 [P] Create backend/database/seeders/ProjectSeeder.php with sample projects assigned to SDDs in `backend/database/seeders/ProjectSeeder.php`
- [x] T006 Update backend/database/seeders/DatabaseSeeder.php to call all seeders in correct order in `backend/database/seeders/DatabaseSeeder.php`

**Checkpoint**: `php artisan migrate:fresh --seed` creates all tables with test data

---

## Phase 2: Foundational (RBAC & Core Security)

**Purpose**: Complete role-based access control - MUST complete before user story implementation

**⚠️ CRITICAL**: All user story features depend on proper RBAC enforcement

### RBAC Models & Middleware

- [x] T007 Update backend/app/Models/User.php to use HasRoles trait and add role helper methods in `backend/app/Models/User.php`
- [x] T008 [P] Create backend/app/Http/Middleware/CheckRole.php for role-based route protection in `backend/app/Http/Middleware/CheckRole.php`
- [x] T009 [P] Create backend/app/Http/Middleware/SourceAIsolation.php to block dept_manager from Source A routes in `backend/app/Http/Middleware/SourceAIsolation.php`
- [x] T010 [P] Create backend/app/Http/Middleware/SourceBIsolation.php to block sdd from Source B routes in `backend/app/Http/Middleware/SourceBIsolation.php`
- [x] T011 Register all middleware in backend/bootstrap/app.php in `backend/bootstrap/app.php`

### Policy Updates

- [x] T012 [P] Update backend/app/Policies/ProjectReportPolicy.php with complete authorization rules in `backend/app/Policies/ProjectReportPolicy.php`
- [x] T013 [P] Update backend/app/Policies/DepartmentReportPolicy.php with complete authorization rules in `backend/app/Policies/DepartmentReportPolicy.php`
- [x] T014 [P] Create backend/app/Policies/ConflictAlertPolicy.php for conflict alert access control in `backend/app/Policies/ConflictAlertPolicy.php`
- [x] T015 Register all policies in backend/app/Providers/AppServiceProvider.php in `backend/app/Providers/AppServiceProvider.php`

### Route Protection

- [x] T016 Update backend/routes/api.php to apply Source A/B isolation middleware in `backend/routes/api.php`
- [x] T017 [P] Create backend/tests/Feature/RbacIsolationTest.php to verify SDD cannot access Source B in `backend/tests/Feature/RbacIsolationTest.php`
- [x] T018 [P] Create backend/tests/Feature/RbacIsolationTest.php to verify DeptManager cannot access Source A in `backend/tests/Feature/RbacIsolationTest.php`

### Audit Logging

- [x] T019 Update backend/app/Observers/AuditLogObserver.php to log all model events with user role in `backend/app/Observers/AuditLogObserver.php`
- [x] T020 [P] Create backend/app/Http/Controllers/Api/V1/AuditLogController.php for CEO/CFO audit log access in `backend/app/Http/Controllers/Api/V1/AuditLogController.php`
- [x] T021 Add audit log routes to backend/routes/api.php (CEO/CFO only) in `backend/routes/api.php`

**Checkpoint**: All routes are protected by role. SDD sees 403 on /department-reports. DeptManager sees 403 on /project-reports.

---

## Phase 3: User Story 1 - SDD Submits Project Report (Priority: P1) 🎯 MVP

**Goal**: SDDs can create, edit, submit, and amend project reports tracking worker accomplishments

**Independent Test**: SDD logs in → creates project report → adds worker entries → submits → views in history → can amend but not delete

### Backend Completion for US1

- [x] T022 [US1] Update backend/app/Http/Controllers/Api/V1/ProjectReportController.php with proper validation and error handling in `backend/app/Http/Controllers/Api/V1/ProjectReportController.php`
- [x] T023 [P] [US1] Create backend/app/Http/Controllers/Api/V1/ProjectReportEntryController.php for entry CRUD in `backend/app/Http/Controllers/Api/V1/ProjectReportEntryController.php`
- [x] T024 [P] [US1] Create backend/app/Http/Requests/StoreProjectReportEntryRequest.php with validation rules in `backend/app/Http/Requests/StoreProjectReportEntryRequest.php`
- [x] T025 [P] [US1] Create backend/app/Http/Requests/UpdateProjectReportEntryRequest.php with validation rules in `backend/app/Http/Requests/UpdateProjectReportEntryRequest.php`
- [x] T026 [US1] Add project report entry routes to backend/routes/api.php in `backend/routes/api.php`
- [x] T027 [US1] Update backend/app/Models/ProjectReport.php with entries relationship and status helpers in `backend/app/Models/ProjectReport.php`

### Frontend Completion for US1

- [x] T028 [P] [US1] Update frontend/src/lib/api/reports.ts with complete API methods for project reports in `frontend/src/lib/api/reports.ts`
- [x] T029 [P] [US1] Create frontend/src/store/slices/projectReportsSlice.ts with Redux state management in `frontend/src/store/slices/projectReportsSlice.ts`
- [x] T030 [US1] Update frontend/src/pages/Reports/ReportForm.tsx with worker entry form and validation in `frontend/src/pages/Reports/ReportForm.tsx`
- [x] T031 [US1] Update frontend/src/pages/Reports/ReportList.tsx with status filters and SDD-scoped data in `frontend/src/pages/Reports/ReportList.tsx`
- [x] T032 [US1] Update frontend/src/pages/Reports/ReportView.tsx with read-only submitted report view in `frontend/src/pages/Reports/ReportView.tsx`
- [x] T033 [US1] Update frontend/src/pages/Reports/ReportAmend.tsx with amendment form and reason field in `frontend/src/pages/Reports/ReportAmend.tsx`
- [x] T034 [US1] Add project report routes to frontend/src/router/index.tsx with SDD role guard in `frontend/src/router/index.tsx`

### Integration for US1

- [x] T035 [US1] Create backend/database/seeders/ProjectReportSeeder.php with sample submitted reports in `backend/database/seeders/ProjectReportSeeder.php`
- [x] T036 [US1] Create frontend/src/components/reports/WorkerEntryRow.tsx for reusable entry input in `frontend/src/components/reports/WorkerEntryRow.tsx`

**Checkpoint**: SDD can login, create report, add entries, submit, view history, and amend. Report cannot be deleted after submission.

---

## Phase 4: User Story 2 - Department Manager Submits Department Report (Priority: P1) 🎯 MVP

**Goal**: Department Managers can create, edit, submit, and amend department reports tracking employee work

**Independent Test**: Dept Manager logs in → creates department report → adds employee entries → submits → views in history → CANNOT see any project reports

### Backend Completion for US2

- [x] T037 [US2] Update backend/app/Http/Controllers/Api/V1/DepartmentReportController.php with proper validation and error handling in `backend/app/Http/Controllers/Api/V1/DepartmentReportController.php`
- [x] T038 [P] [US2] Create backend/app/Http/Controllers/Api/V1/DepartmentReportEntryController.php for entry CRUD in `backend/app/Http/Controllers/Api/V1/DepartmentReportEntryController.php`
- [x] T039 [P] [US2] Create backend/app/Http/Requests/StoreDepartmentReportEntryRequest.php with validation rules in `backend/app/Http/Requests/StoreDepartmentReportEntryRequest.php`
- [x] T040 [P] [US2] Create backend/app/Http/Requests/UpdateDepartmentReportEntryRequest.php with validation rules in `backend/app/Http/Requests/UpdateDepartmentReportEntryRequest.php`
- [x] T041 [US2] Add department report entry routes to backend/routes/api.php in `backend/routes/api.php`
- [x] T042 [US2] Update backend/app/Models/DepartmentReport.php with entries relationship and status helpers in `backend/app/Models/DepartmentReport.php`
- [x] T043 [P] [US2] Create backend/app/Models/DepartmentReportAmendment.php for tracking amendments in `backend/app/Models/DepartmentReportAmendment.php`
- [x] T044 [P] [US2] Create backend/database/migrations/2026_02_08_000008_create_department_report_amendments_table.php in `backend/database/migrations/2026_02_08_000008_create_department_report_amendments_table.php`

### Frontend Completion for US2

- [x] T045 [P] [US2] Update frontend/src/lib/api/departmentReports.ts with complete API methods in `frontend/src/lib/api/departmentReports.ts`
- [x] T046 [P] [US2] Create frontend/src/store/slices/departmentReportsSlice.ts with Redux state management in `frontend/src/store/slices/departmentReportsSlice.ts`
- [x] T047 [US2] Update frontend/src/pages/DepartmentReports/ReportForm.tsx with employee entry form and validation in `frontend/src/pages/DepartmentReports/ReportForm.tsx`
- [x] T048 [US2] Update frontend/src/pages/DepartmentReports/ReportList.tsx with status filters and dept-scoped data in `frontend/src/pages/DepartmentReports/ReportList.tsx`
- [x] T049 [P] [US2] Create frontend/src/pages/DepartmentReports/ReportView.tsx for read-only submitted report view in `frontend/src/pages/DepartmentReports/ReportView.tsx`
- [x] T050 [P] [US2] Create frontend/src/pages/DepartmentReports/ReportAmend.tsx with amendment form in `frontend/src/pages/DepartmentReports/ReportAmend.tsx`
- [x] T051 [US2] Add department report routes to frontend/src/router/index.tsx with DeptManager role guard in `frontend/src/router/index.tsx`

### Integration for US2

- [x] T052 [US2] Create backend/database/seeders/DepartmentReportSeeder.php with sample submitted reports in `backend/database/seeders/DepartmentReportSeeder.php`
- [x] T053 [US2] Create frontend/src/components/reports/EmployeeEntryRow.tsx for reusable entry input in `frontend/src/components/reports/EmployeeEntryRow.tsx`

**Checkpoint**: Dept Manager can login, create report, add entries, submit, view history, and amend. Cannot access any Source A data.

---

## Phase 5: User Story 3 - GM Reviews Conflict Alerts (Priority: P2)

**Goal**: GM receives and reviews automated conflict alerts when Source A and Source B have discrepancies

**Independent Test**: Pre-submitted conflicting reports exist → GM logs in → sees conflict dashboard → views detail with side-by-side comparison → resolves with notes

### Backend Completion for US3

- [x] T054 [US3] Update backend/app/Services/ConflictDetectionService.php with complete detection algorithm in `backend/app/Services/ConflictDetectionService.php`
- [x] T055 [US3] Update backend/app/Jobs/WeeklyConflictDetectionJob.php with proper scheduling and logging in `backend/app/Jobs/WeeklyConflictDetectionJob.php`
- [x] T056 [US3] Update backend/app/Http/Controllers/Api/V1/ConflictAlertController.php with complete CRUD and resolve in `backend/app/Http/Controllers/Api/V1/ConflictAlertController.php`
- [x] T057 [P] [US3] Create backend/app/Http/Requests/ResolveConflictRequest.php with validation rules in `backend/app/Http/Requests/ResolveConflictRequest.php`
- [x] T058 [US3] Register WeeklyConflictDetectionJob in backend/routes/console.php for weekly schedule in `backend/routes/console.php`
- [x] T059 [P] [US3] Create backend/app/Notifications/ConflictAlertNotification.php for GM email notification in `backend/app/Notifications/ConflictAlertNotification.php`

### Frontend Completion for US3

- [x] T060 [P] [US3] Update frontend/src/lib/api/conflicts.ts with complete API methods in `frontend/src/lib/api/conflicts.ts`
- [x] T061 [P] [US3] Create frontend/src/store/slices/conflictsSlice.ts with Redux state management in `frontend/src/store/slices/conflictsSlice.ts`
- [x] T062 [US3] Update frontend/src/pages/Conflicts/ConflictList.tsx with status filters and GM-visible data in `frontend/src/pages/Conflicts/ConflictList.tsx`
- [x] T063 [US3] Update frontend/src/pages/Conflicts/ConflictDetail.tsx with side-by-side comparison and resolve form in `frontend/src/pages/Conflicts/ConflictDetail.tsx`
- [x] T064 [US3] Add conflict routes to frontend/src/router/index.tsx with GM/CEO/CFO role guard in `frontend/src/router/index.tsx`

### Escalation Logic for US3

- [x] T065 [US3] Create backend/app/Jobs/EscalateConflictAlertsJob.php to escalate unresolved alerts after 7 days in `backend/app/Jobs/EscalateConflictAlertsJob.php`
- [x] T066 [US3] Register EscalateConflictAlertsJob in backend/routes/console.php for daily schedule in `backend/routes/console.php`
- [x] T067 [P] [US3] Create backend/app/Notifications/ConflictEscalatedNotification.php for CEO/CFO notification in `backend/app/Notifications/ConflictEscalatedNotification.php`

### Seeder for US3

- [x] T068 [US3] Create backend/database/seeders/ConflictAlertSeeder.php with sample conflicts in `backend/database/seeders/ConflictAlertSeeder.php`

**Checkpoint**: GM can view conflict dashboard, see discrepancy details side-by-side, and resolve with notes. Unresolved alerts escalate after 7 days.

---

## Phase 6: User Story 6 - Role-Based Data Isolation (Priority: P1) 🎯 MVP

**Goal**: System enforces strict role-based data isolation to prevent collusion

**Independent Test**: SDD cannot see any Source B data. DeptManager cannot see any Source A data. Access attempts are logged.

### Backend Security Hardening for US6

- [x] T069 [US6] Create backend/app/Http/Middleware/LogAccessAttempt.php to log all access attempts in `backend/app/Http/Middleware/LogAccessAttempt.php`
- [x] T070 [US6] Apply LogAccessAttempt middleware to all protected routes in backend/routes/api.php in `backend/routes/api.php`
- [x] T071 [P] [US6] Create backend/app/Models/Scopes/DeptManagerDepartmentScope.php for dept manager query scoping in `backend/app/Models/Scopes/DeptManagerDepartmentScope.php`
- [x] T072 [US6] Apply global scopes to DepartmentReport model in `backend/app/Models/DepartmentReport.php`
- [x] T073 [US6] Update backend/app/Models/Scopes/SddProjectScope.php with complete scope logic in `backend/app/Models/Scopes/SddProjectScope.php`
- [x] T074 [US6] Apply global scopes to ProjectReport model in `backend/app/Models/ProjectReport.php`

### Frontend Guards for US6

- [x] T075 [P] [US6] Create frontend/src/guards/RoleGuard.tsx for route-level role checking in `frontend/src/guards/RoleGuard.tsx`
- [x] T076 [P] [US6] Create frontend/src/guards/SourceAGuard.tsx to block dept_manager from Source A routes in `frontend/src/guards/SourceAGuard.tsx`
- [x] T077 [P] [US6] Create frontend/src/guards/SourceBGuard.tsx to block sdd from Source B routes in `frontend/src/guards/SourceBGuard.tsx`
- [x] T078 [US6] Apply guards to all routes in frontend/src/router/index.tsx in `frontend/src/router/index.tsx`

### Security Verification for US6

- [x] T079 [US6] Create backend/tests/Feature/SourceIsolationTest.php with comprehensive access control tests in `backend/tests/Feature/SourceIsolationTest.php`

**Checkpoint**: Complete data isolation verified. All unauthorized access attempts logged. No data leakage possible.

---

## Phase 7: Frontend Shell & Navigation

**Purpose**: Complete the dashboard layout and navigation for all roles

### Dashboard Layout

- [x] T080 [P] Update frontend/src/components/layouts/DashboardLayout.tsx with role-based navigation in `frontend/src/components/layouts/DashboardLayout.tsx`
- [x] T081 [P] Create frontend/src/components/common/Sidebar.tsx with role-specific menu items in `frontend/src/components/common/Sidebar.tsx`
- [x] T082 [P] Create frontend/src/components/common/Header.tsx with user info and logout in `frontend/src/components/common/Header.tsx`
- [x] T083 [P] Create frontend/src/components/common/LoadingSpinner.tsx for loading states in `frontend/src/components/common/LoadingSpinner.tsx`
- [x] T084 [P] Create frontend/src/components/common/ErrorBoundary.tsx for error handling in `frontend/src/components/common/ErrorBoundary.tsx`

### State Management Setup

- [x] T085 Create frontend/src/store/index.ts with Redux store configuration in `frontend/src/store/index.ts`
- [x] T086 [P] Create frontend/src/store/slices/authSlice.ts with authentication state in `frontend/src/store/slices/authSlice.ts`
- [x] T087 [P] Create frontend/src/store/slices/uiSlice.ts with UI state (loading, errors, notifications) in `frontend/src/store/slices/uiSlice.ts`
- [x] T088 Update frontend/src/main.tsx to wrap app with Redux Provider in `frontend/src/main.tsx`

### API Integration

- [x] T089 Update frontend/src/lib/axios.ts with proper interceptors and error handling in `frontend/src/lib/axios.ts`
- [x] T090 [P] Create frontend/src/hooks/useApi.ts for generic API call hook in `frontend/src/hooks/useApi.ts`

**Checkpoint**: All pages have consistent layout. Navigation shows role-appropriate items. Loading and error states work correctly.

---

## Phase 8: Role-Specific Dashboards

**Purpose**: Create landing dashboards for each role showing relevant summary data

### SDD Dashboard

- [x] T091 [P] Create frontend/src/pages/Dashboards/SddDashboard.tsx with project summary and report status in `frontend/src/pages/Dashboards/SddDashboard.tsx`
- [x] T092 [P] Create backend/app/Http/Controllers/Api/V1/SddDashboardController.php with summary data in `backend/app/Http/Controllers/Api/V1/SddDashboardController.php`
- [x] T093 Add SDD dashboard route to backend/routes/api.php in `backend/routes/api.php`

### Department Manager Dashboard

- [x] T094 [P] Create frontend/src/pages/Dashboards/DeptManagerDashboard.tsx with department summary in `frontend/src/pages/Dashboards/DeptManagerDashboard.tsx`
- [x] T095 [P] Create backend/app/Http/Controllers/Api/V1/DeptManagerDashboardController.php with summary data in `backend/app/Http/Controllers/Api/V1/DeptManagerDashboardController.php`
- [x] T096 Add DeptManager dashboard route to backend/routes/api.php in `backend/routes/api.php`

### GM Dashboard

- [x] T097 [P] Create frontend/src/pages/Dashboards/GmDashboard.tsx with conflict summary and team overview in `frontend/src/pages/Dashboards/GmDashboard.tsx`
- [x] T098 [P] Create backend/app/Http/Controllers/Api/V1/GmDashboardController.php with summary data in `backend/app/Http/Controllers/Api/V1/GmDashboardController.php`
- [x] T099 Add GM dashboard route to backend/routes/api.php in `backend/routes/api.php`

### Dashboard Routing

- [x] T100 Update frontend/src/pages/Dashboard.tsx to redirect to role-specific dashboard in `frontend/src/pages/Dashboard.tsx`

**Checkpoint**: Each role sees their appropriate dashboard on login with relevant summary data.

---

## Phase 9: Polish & Production Readiness

**Purpose**: Final touches for production-ready MVP

### Error Handling & Validation

- [x] T101 [P] Create frontend/src/components/common/Toast.tsx for success/error notifications in `frontend/src/components/common/Toast.tsx`
- [x] T102 [P] Create frontend/src/utils/validation.ts with shared validation helpers in `frontend/src/utils/validation.ts`
- [x] T103 Update all form components to display backend validation errors in `frontend/src/pages/`

### Performance & UX

- [x] T104 [P] Add loading states to all data-fetching components in `frontend/src/pages/`
- [x] T105 [P] Add empty states to all list components in `frontend/src/pages/`
- [x] T106 [P] Create frontend/src/components/common/Pagination.tsx for paginated lists in `frontend/src/components/common/Pagination.tsx`

### Testing

- [x] T107 [P] Create backend/tests/Feature/ProjectReportTest.php with complete CRUD tests in `backend/tests/Feature/ProjectReportTest.php`
- [x] T108 [P] Create backend/tests/Feature/DepartmentReportTest.php with complete CRUD tests in `backend/tests/Feature/DepartmentReportTest.php`
- [x] T109 [P] Create backend/tests/Feature/ConflictDetectionTest.php with detection algorithm tests in `backend/tests/Feature/ConflictDetectionTest.php`
- [x] T110 Create backend/tests/Feature/AuthenticationTest.php with login/logout tests in `backend/tests/Feature/AuthenticationTest.php`

### Documentation

- [x] T111 Update README.md with complete setup instructions in `./README.md`
- [x] T112 [P] Create docs/API.md with API documentation summary in `docs/API.md`

### Final Validation

- [x] Create comprehensive testing checklist (`specs/001-dual-reporting/testing_checklist.md`)
- [x] Verify Docker environment is running and healthy
- [x] Verify Backend tests pass (`php artisan test`)
- [x] Verify Frontend tests pass (Vitest integration tests)
- [x] Test SDD Submission Flow (Frontend Integration)
- [x] Test Dept Manager Submission Flow (Frontend Integration)
- [x] Test Conflict Resolution Flow (Frontend Integration)
- [x] Verify Role-Based Data Isolation (Frontend Integration)
- [ ] T117 Manually test GM flow: login → view conflicts → resolve with notes
- [ ] T118 Verify Source A/B isolation: SDD cannot access Source B, DeptManager cannot access Source A

**Checkpoint**: Production-ready MVP complete. All core flows work. All tests pass. Documentation complete.

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1: Setup
    ↓ (seeders needed for testing)
Phase 2: Foundational (RBAC) ← BLOCKS ALL USER STORIES
    ↓
Phase 3: User Story 1 (SDD Reports)     ← Can start after Phase 2
Phase 4: User Story 2 (Dept Reports)    ← Can start after Phase 2 (parallel with US1)
Phase 5: User Story 3 (Conflict Alerts) ← Depends on US1 + US2 completion
Phase 6: User Story 6 (Data Isolation)  ← Can start after Phase 2 (parallel with US1/US2)
    ↓
Phase 7: Frontend Shell                 ← Can start after Phase 2 (parallel with US1/US2)
Phase 8: Role Dashboards               ← Depends on Phase 7
    ↓
Phase 9: Polish                        ← Depends on all user stories
```

### Critical Path

```
T001-T006 (Setup) → T007-T021 (RBAC) → T022-T036 (US1) + T037-T053 (US2) → T054-T068 (US3) → T113-T118 (Validation)
```

### Parallel Opportunities

**After Phase 1:**
- T002, T003, T004, T005 (all seeders)

**After Phase 2:**
- User Story 1 (T022-T036)
- User Story 2 (T037-T053)
- User Story 6 (T069-T079)
- Phase 7 (T080-T090)

**Within Each User Story:**
- All [P] tasks can run in parallel
- Backend and frontend work can proceed in parallel

---

## Implementation Strategy

### MVP First (User Stories 1, 2, 6)

1. Complete Phase 1: Setup (seeders and config)
2. Complete Phase 2: Foundational (RBAC - critical blocker)
3. Complete Phase 3: User Story 1 (SDD can submit reports)
4. Complete Phase 4: User Story 2 (DeptManager can submit reports)
5. Complete Phase 6: User Story 6 (Data isolation verified)
6. **STOP and VALIDATE**: Test both reporting flows independently
7. Deploy/demo MVP

### Add Conflict Detection

8. Complete Phase 5: User Story 3 (GM reviews conflicts)
9. Complete Phase 7: Frontend Shell
10. Complete Phase 8: Role Dashboards
11. Complete Phase 9: Polish

### Production Readiness Checklist

- [ ] All migrations run without error
- [ ] All seeders populate test data
- [ ] All API endpoints return proper status codes
- [ ] All forms validate and display errors
- [ ] All roles see only their authorized data
- [ ] All audit logs capture user actions
- [ ] All tests pass
- [ ] Manual testing confirms all flows work

---

## Summary

| Phase | Tasks | Description |
|-------|-------|-------------|
| Phase 1 | T001-T006 | Setup (seeders) |
| Phase 2 | T007-T021 | Foundational (RBAC) |
| Phase 3 | T022-T036 | User Story 1 (SDD Reports) |
| Phase 4 | T037-T053 | User Story 2 (Dept Reports) |
| Phase 5 | T054-T068 | User Story 3 (Conflict Alerts) |
| Phase 6 | T069-T079 | User Story 6 (Data Isolation) |
| Phase 7 | T080-T090 | Frontend Shell |
| Phase 8 | T091-T100 | Role Dashboards |
| Phase 9 | T101-T118 | Polish & Validation |

**Total Tasks**: 118
**MVP Tasks (Phase 1-4, 6)**: 79
**Parallel Opportunities**: 45 tasks marked [P]

**Suggested MVP Scope**: Complete Phases 1-4 and 6 for a working dual-reporting system with full RBAC. This delivers User Stories 1, 2, and 6 (all P1 priority).
