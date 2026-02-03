# Implementation Plan - Verification Tests

## Goal Description
Implement automated integration tests for the Dual Information Reporting System to simulate real user behavior from the frontend. These tests will verify User Stories 1, 2, 3, and 6, ensuring quality and regression protection without requiring a live backend or browser driver (using Vitest + React Testing Library).

## User Review Required
> [!IMPORTANT]
> **Mocked Backend**: These tests run against **MOCKED** API responses. They verify the Frontend logic, routing, and form validation, but they do NOT verify the actual Backend API endpoints.
> To verify Backend, run `php artisan test` (already implemented).
> This satisfies the "simulated front end user" requirement by ensuring the UI behaves correctly given expected API responses.

## Proposed Changes

### Frontend - Test Infrastructure
#### [NEW] [setup.ts](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/setup.ts)
- Create test setup file to import `@testing-library/jest-dom`.

#### [NEW] [test-utils.tsx](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/utils.tsx)
- Create custom `render` function that wraps components in `Provider` (Redux) and `BrowserRouter`.
- This allows testing pages that depend on the store and router.

### Frontend - Test Suites
#### [NEW] [RbacIsolation.test.tsx](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/integration/RbacIsolation.test.tsx)
- **Goal**: Verify Test Case 2.1 & 2.2 from Checklist.
- **Scenarios**:
  - SDD cannot access Department Reports (mock 403 response).
  - Dept Mgr cannot access Project Reports.

#### [NEW] [SddReportingFlow.test.tsx](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/integration/SddReportingFlow.test.tsx)
- **Goal**: Verify Test Case 3.1 (User Story 1).
- **Scenarios**:
  - Render `ReportForm`.
  - Simulate entering Worker, Hours, Accomplishments.
  - Submitting calls the `createProjectReport` API.
  - Verify success toast/redirect.

#### [NEW] [DeptManagerReportingFlow.test.tsx](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/integration/DeptManagerReportingFlow.test.tsx)
- **Goal**: Verify Test Case 3.2 (User Story 2).
- **Scenarios**:
  - Render `ReportForm` (Dept).
  - Simulate entering Employee, Hours, Tasks.
  - Submitting calls `createDepartmentReport` API.

#### [NEW] [ConflictResolution.test.tsx](file:///c:/Users/dz-mr/Documents/SandBox/Team%20Management%20Platform/frontend/src/test/integration/ConflictResolution.test.tsx)
- **Goal**: Verify Test Case 4 (User Story 3).
- **Scenarios**:
  - Render `ConflictDetail` page.
  - Mock conflict data (Source A vs Source B).
  - Simulate GM clicking "Resolve".
  - Verify API call to resolve conflict.

## Verification Plan

### Automated Tests
Run the newly created tests using Vitest:
```bash
cd frontend
npm run test
```
(I will execute this via `run_command` as `npm run test` or `npx vitest`)

### Validation Criteria
- All 4 new test files must pass.
- Tests must simulate user interactions (click, change inputs).
- Mocks must capture the correct API calls.
