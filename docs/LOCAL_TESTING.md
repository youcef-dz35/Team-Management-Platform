# Local Testing Guide

This document describes how to run local tests for the Team Management Platform, covering the final validation tasks (T113-T118).

## Prerequisites

1. Docker Desktop or Docker Engine with Compose
2. Bash shell (Git Bash on Windows) or PowerShell
3. curl (for API testing)

## Quick Start

```bash
# Start all services
docker compose up -d

# Wait for services to be healthy
docker compose ps

# Run all validation tests
make test-all
# OR on Windows PowerShell:
.\scripts\test-local.ps1
```

## Available Test Commands

### Makefile Targets

| Command | Description | Task |
|---------|-------------|------|
| `make test-all` | Run complete validation suite | T113-T118 |
| `make test-migrate` | Test migrations and seeders | T113 |
| `make test-unit` | Run backend PHPUnit tests | T114 |
| `make test-sdd` | Test SDD report flow | T115 |
| `make test-deptmgr` | Test DeptManager report flow | T116 |
| `make test-gm` | Test GM conflict review flow | T117 |
| `make test-isolation` | Test Source A/B data isolation | T118 |
| `make test-frontend` | Run frontend Vitest tests | - |

### PowerShell Script (Windows)

```powershell
# Run all tests
.\scripts\test-local.ps1

# Run specific tests
.\scripts\test-local.ps1 -Migrations    # T113
.\scripts\test-local.ps1 -Tests         # T114
.\scripts\test-local.ps1 -Sdd           # T115
.\scripts\test-local.ps1 -DeptMgr       # T116
.\scripts\test-local.ps1 -Gm            # T117
.\scripts\test-local.ps1 -Isolation     # T118

# Combine flags
.\scripts\test-local.ps1 -Sdd -DeptMgr -Isolation
```

### Bash Script (Linux/Mac/Git Bash)

```bash
# Run all tests
./scripts/test-local.sh

# Run specific tests
./scripts/test-local.sh --migrations    # T113
./scripts/test-local.sh --tests         # T114
./scripts/test-local.sh --sdd           # T115
./scripts/test-local.sh --deptmgr       # T116
./scripts/test-local.sh --gm            # T117
./scripts/test-local.sh --isolation     # T118
```

## Test Details

### T113: Migrations and Seeders

Verifies that the database can be freshly migrated and seeded.

**What it tests:**
- All migrations run without error
- RoleSeeder creates 8 roles (ceo, cfo, gm, ops_manager, director, sdd, dept_manager, worker)
- UserSeeder creates test users for each role
- DepartmentSeeder creates 5 departments
- ProjectSeeder creates sample projects

**Manual verification:**
```bash
docker compose exec backend php artisan migrate:fresh --seed --force
docker compose exec backend php artisan tinker --execute="echo 'Users: ' . App\Models\User::count();"
```

### T114: Backend Tests

Runs all PHPUnit feature tests.

**What it tests:**
- `ProjectReportTest.php` - Project report CRUD operations
- `DepartmentReportTest.php` - Department report CRUD operations
- `ConflictDetectionTest.php` - Conflict detection algorithm
- `AuthenticationTest.php` - Login/logout flows
- `RbacIsolationTest.php` - Role-based access control
- `SourceIsolationTest.php` - Source A/B data isolation

**Manual verification:**
```bash
docker compose exec backend php artisan test --parallel
docker compose exec backend php artisan test --filter=ProjectReportTest
```

### T115: SDD Flow

Tests the complete SDD (Service Delivery Director) workflow.

**Flow tested:**
1. SDD logs in → Token received
2. SDD gets assigned projects
3. SDD creates a project report
4. SDD adds worker entries to the report
5. SDD submits the report → Status changes to "submitted"
6. SDD views the submitted report
7. SDD amends the report → Status changes to "amended"
8. SDD tries to delete → Blocked (405 or 403)

**Test credentials:**
- Email: `sdd@example.com`
- Password: `password`

### T116: Department Manager Flow

Tests the complete Department Manager workflow.

**Flow tested:**
1. Dept Manager logs in → Token received
2. Dept Manager gets assigned departments
3. Dept Manager creates a department report
4. Dept Manager adds employee entries
5. Dept Manager submits the report
6. Dept Manager tries to access project reports → **Blocked (403)**

**Test credentials:**
- Email: `deptmgr@example.com`
- Password: `password`

### T117: GM Conflict Review Flow

Tests the General Manager's conflict review workflow.

**Flow tested:**
1. GM logs in → Token received
2. GM views conflict alerts list
3. GM views conflict detail (if conflicts exist)
4. GM resolves conflict with resolution notes

**Prerequisites:**
- Conflicting reports must exist (run seeders first)
- Or manually run conflict detection: `php artisan conflict:detect`

**Test credentials:**
- Email: `gm@example.com`
- Password: `password`

### T118: Source A/B Isolation

Verifies strict data isolation between Source A and Source B.

**What it tests:**
1. SDD cannot access department reports (Source B) → Returns 403
2. Department Manager cannot access project reports (Source A) → Returns 403
3. Access attempts are logged in audit logs
4. CEO can view audit logs

## Manual Browser Testing

Some features require manual browser testing at `http://localhost:5173`:

### SDD Manual Test Checklist
- [ ] Login as sdd@example.com
- [ ] Navigate to Project Reports
- [ ] Create a new report
- [ ] Add multiple worker entries
- [ ] Save as draft
- [ ] Edit the draft
- [ ] Submit the report
- [ ] Verify cannot edit submitted report
- [ ] Verify can amend with reason
- [ ] Verify no access to Department Reports in navigation

### Department Manager Manual Test Checklist
- [ ] Login as deptmgr@example.com
- [ ] Navigate to Department Reports
- [ ] Create a new report
- [ ] Add multiple employee entries
- [ ] Submit the report
- [ ] Verify Project Reports is NOT visible in navigation
- [ ] Try to navigate to /reports/projects → Should redirect or show 403

### GM Manual Test Checklist
- [ ] Login as gm@example.com
- [ ] View conflict dashboard
- [ ] Click on a conflict to see detail
- [ ] View side-by-side comparison
- [ ] Resolve with notes
- [ ] Verify conflict status changes

## API Testing with curl

### Login and Get Token

```bash
# Login as SDD
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"sdd@example.com","password":"password"}'

# Store token
export TOKEN="<token-from-response>"
```

### Test SDD Access

```bash
# Should succeed - SDD accessing project reports
curl -X GET http://localhost/api/v1/project-reports \
  -H "Authorization: Bearer $TOKEN"

# Should fail with 403 - SDD accessing department reports
curl -X GET http://localhost/api/v1/department-reports \
  -H "Authorization: Bearer $TOKEN"
```

### Test Isolation

```bash
# Login as Dept Manager
DM_TOKEN=$(curl -s -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"deptmgr@example.com","password":"password"}' | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

# Should fail with 403 - DeptManager accessing project reports
curl -X GET http://localhost/api/v1/project-reports \
  -H "Authorization: Bearer $DM_TOKEN"
```

## Troubleshooting

### Tests Fail to Connect

```bash
# Check if containers are running
docker compose ps

# Check backend logs
docker compose logs backend

# Restart services
docker compose restart
```

### Database Connection Issues

```bash
# Reset database completely
docker compose exec backend php artisan migrate:fresh --seed --force

# Check database connection
docker compose exec backend php artisan tinker --execute="DB::connection()->getPdo();"
```

### Authentication Failures

```bash
# Clear all caches
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear

# Regenerate app key if needed
docker compose exec backend php artisan key:generate
```

### Test Data Missing

```bash
# Re-run all seeders
docker compose exec backend php artisan db:seed --force

# Run specific seeder
docker compose exec backend php artisan db:seed --class=ConflictAlertSeeder
```

## Continuous Integration

For CI/CD pipelines, use the non-interactive mode:

```bash
# GitHub Actions / GitLab CI
docker compose up -d
sleep 30  # Wait for services
docker compose exec -T backend php artisan migrate:fresh --seed --force
docker compose exec -T backend php artisan test --parallel
./scripts/test-local.sh
```

## Test Coverage

To generate test coverage reports:

```bash
# Backend coverage
docker compose exec backend php artisan test --coverage

# Frontend coverage
docker compose exec frontend npm run test:coverage
```
