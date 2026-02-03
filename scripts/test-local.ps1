#!/usr/bin/env pwsh
# Local Testing Script for Team Management Platform (PowerShell)
# This script automates the validation tasks T113-T118

param(
    [switch]$Migrations,
    [switch]$Tests,
    [switch]$Sdd,
    [switch]$DeptMgr,
    [switch]$Gm,
    [switch]$Isolation,
    [switch]$Help
)

$ErrorActionPreference = 'Continue'

# Configuration
$API_BASE = "http://localhost/api/v1"

# Colors
function Write-ColorOutput {
    param([string]$Color, [string]$Message)
    switch ($Color) {
        "Green"  { Write-Host $Message -ForegroundColor Green }
        "Red"    { Write-Host $Message -ForegroundColor Red }
        "Yellow" { Write-Host $Message -ForegroundColor Yellow }
        "Blue"   { Write-Host $Message -ForegroundColor Cyan }
        default  { Write-Host $Message }
    }
}

function Print-Result {
    param([bool]$Success, [string]$TestName, [string]$Error = "")
    if ($Success) {
        Write-Host "✓ PASS" -ForegroundColor Green -NoNewline
        Write-Host ": $TestName"
    } else {
        Write-Host "✗ FAIL" -ForegroundColor Red -NoNewline
        Write-Host ": $TestName"
        if ($Error) {
            Write-Host "  Error: $Error" -ForegroundColor Yellow
        }
    }
}

function Get-AuthToken {
    param([string]$Email, [string]$Password = "password")

    try {
        $body = @{
            email = $Email
            password = $Password
        } | ConvertTo-Json

        $response = Invoke-RestMethod -Uri "$API_BASE/auth/login" `
            -Method Post `
            -ContentType "application/json" `
            -Body $body `
            -ErrorAction Stop

        return $response.access_token
    }
    catch {
        return $null
    }
}

# ========================================
# T113: Verify migrations and seeders work
# ========================================
function Test-MigrationsAndSeeders {
    Write-ColorOutput "Blue" "`n[T113] Testing migrations and seeders..."

    # Run fresh migration with seed
    $result = docker compose exec -T backend php artisan migrate:fresh --seed --force 2>&1
    $success = $LASTEXITCODE -eq 0
    Print-Result $success "migrate:fresh --seed completed"

    # Verify users were seeded
    $userCount = docker compose exec -T backend php artisan tinker --execute="echo App\Models\User::count();" 2>$null | Select-Object -Last 1
    $success = [int]$userCount -gt 0
    Print-Result $success "Users seeded: $userCount users found"

    # Verify roles were seeded
    $roleCount = docker compose exec -T backend php artisan tinker --execute="echo Spatie\Permission\Models\Role::count();" 2>$null | Select-Object -Last 1
    $success = [int]$roleCount -ge 8
    Print-Result $success "Roles seeded: $roleCount roles found"

    # Verify departments were seeded
    $deptCount = docker compose exec -T backend php artisan tinker --execute="echo App\Models\Department::count();" 2>$null | Select-Object -Last 1
    $success = [int]$deptCount -ge 5
    Print-Result $success "Departments seeded: $deptCount departments found"

    # Verify projects were seeded
    $projectCount = docker compose exec -T backend php artisan tinker --execute="echo App\Models\Project::count();" 2>$null | Select-Object -Last 1
    $success = [int]$projectCount -gt 0
    Print-Result $success "Projects seeded: $projectCount projects found"
}

# ========================================
# T114: Run all backend tests
# ========================================
function Test-BackendTests {
    Write-ColorOutput "Blue" "`n[T114] Running backend tests..."

    docker compose exec -T backend php artisan test --parallel 2>&1
    $success = $LASTEXITCODE -eq 0
    Print-Result $success "All backend tests pass"
}

# ========================================
# T115: Test SDD flow
# ========================================
function Test-SddFlow {
    Write-ColorOutput "Blue" "`n[T115] Testing SDD flow..."

    # Login as SDD
    $sddToken = Get-AuthToken "sdd@example.com"
    if (-not $sddToken) {
        Print-Result $false "SDD login" "Failed to get auth token"
        return
    }
    Print-Result $true "SDD login"

    $headers = @{
        "Authorization" = "Bearer $sddToken"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    # Get projects
    try {
        $projects = Invoke-RestMethod -Uri "$API_BASE/projects" -Headers $headers -Method Get
        $projectId = $projects.data[0].id
        Print-Result $true "Get SDD projects: Found project $projectId"
    }
    catch {
        Print-Result $false "Get SDD projects" $_.Exception.Message
        return
    }

    # Create report
    $weekStart = (Get-Date).AddDays(-(Get-Date).DayOfWeek.value__ + 1).ToString("yyyy-MM-dd")
    $weekEnd = (Get-Date).AddDays(7 - (Get-Date).DayOfWeek.value__).ToString("yyyy-MM-dd")

    try {
        $reportBody = @{
            project_id = $projectId
            reporting_period_start = $weekStart
            reporting_period_end = $weekEnd
        } | ConvertTo-Json

        $report = Invoke-RestMethod -Uri "$API_BASE/project-reports" -Headers $headers -Method Post -Body $reportBody
        $reportId = $report.data.id
        Print-Result $true "Create project report: ID $reportId"
    }
    catch {
        Print-Result $false "Create project report" $_.Exception.Message
        return
    }

    # Add entry
    try {
        $entryBody = @{
            employee_id = 1
            hours_worked = 40
            tasks_completed = 5
            status = "on_track"
            accomplishments = "Test accomplishment"
        } | ConvertTo-Json

        $entry = Invoke-RestMethod -Uri "$API_BASE/project-reports/$reportId/entries" -Headers $headers -Method Post -Body $entryBody
        Print-Result $true "Add worker entry: ID $($entry.data.id)"
    }
    catch {
        Print-Result $false "Add worker entry" $_.Exception.Message
    }

    # Submit
    try {
        $submitted = Invoke-RestMethod -Uri "$API_BASE/project-reports/$reportId/submit" -Headers $headers -Method Post
        $success = $submitted.data.status -eq "submitted"
        Print-Result $success "Submit report"
    }
    catch {
        Print-Result $false "Submit report" $_.Exception.Message
    }

    # View
    try {
        $viewed = Invoke-RestMethod -Uri "$API_BASE/project-reports/$reportId" -Headers $headers -Method Get
        Print-Result $true "View submitted report"
    }
    catch {
        Print-Result $false "View submitted report" $_.Exception.Message
    }

    # Amend
    try {
        $amendBody = @{ amendment_reason = "Test amendment for verification" } | ConvertTo-Json
        $amended = Invoke-RestMethod -Uri "$API_BASE/project-reports/$reportId/amend" -Headers $headers -Method Post -Body $amendBody
        $success = $amended.data.status -eq "amended"
        Print-Result $success "Amend report"
    }
    catch {
        Print-Result $false "Amend report" $_.Exception.Message
    }

    # Delete should fail
    try {
        Invoke-RestMethod -Uri "$API_BASE/project-reports/$reportId" -Headers $headers -Method Delete
        Print-Result $false "Delete should be blocked" "Delete succeeded when it should have failed"
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        $success = $statusCode -eq 405 -or $statusCode -eq 403
        Print-Result $success "Delete blocked (HTTP $statusCode)"
    }
}

# ========================================
# T116: Test DeptManager flow
# ========================================
function Test-DeptManagerFlow {
    Write-ColorOutput "Blue" "`n[T116] Testing Department Manager flow..."

    # Login as Dept Manager
    $dmToken = Get-AuthToken "deptmgr@example.com"
    if (-not $dmToken) {
        Print-Result $false "Dept Manager login" "Failed to get auth token"
        return
    }
    Print-Result $true "Dept Manager login"

    $headers = @{
        "Authorization" = "Bearer $dmToken"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    # Get departments
    try {
        $departments = Invoke-RestMethod -Uri "$API_BASE/departments" -Headers $headers -Method Get
        $deptId = $departments.data[0].id
        Print-Result $true "Get departments: Found department $deptId"
    }
    catch {
        Print-Result $false "Get departments" $_.Exception.Message
        return
    }

    # Create report
    $weekStart = (Get-Date).AddDays(-(Get-Date).DayOfWeek.value__ + 1).ToString("yyyy-MM-dd")
    $weekEnd = (Get-Date).AddDays(7 - (Get-Date).DayOfWeek.value__).ToString("yyyy-MM-dd")

    try {
        $reportBody = @{
            department_id = $deptId
            reporting_period_start = $weekStart
            reporting_period_end = $weekEnd
        } | ConvertTo-Json

        $report = Invoke-RestMethod -Uri "$API_BASE/department-reports" -Headers $headers -Method Post -Body $reportBody
        $reportId = $report.data.id
        Print-Result $true "Create department report: ID $reportId"
    }
    catch {
        Print-Result $false "Create department report" $_.Exception.Message
        return
    }

    # Add entry
    try {
        $entryBody = @{
            employee_id = 1
            hours_worked = 40
            tasks_completed = 3
            status = "productive"
            work_description = "Test work description"
        } | ConvertTo-Json

        $entry = Invoke-RestMethod -Uri "$API_BASE/department-reports/$reportId/entries" -Headers $headers -Method Post -Body $entryBody
        Print-Result $true "Add employee entry"
    }
    catch {
        Print-Result $false "Add employee entry" $_.Exception.Message
    }

    # Submit
    try {
        $submitted = Invoke-RestMethod -Uri "$API_BASE/department-reports/$reportId/submit" -Headers $headers -Method Post
        $success = $submitted.data.status -eq "submitted"
        Print-Result $success "Submit report"
    }
    catch {
        Print-Result $false "Submit report" $_.Exception.Message
    }

    # Verify NO access to Source A
    try {
        Invoke-RestMethod -Uri "$API_BASE/project-reports" -Headers $headers -Method Get
        Print-Result $false "Source A should be blocked" "DeptManager accessed project reports!"
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        $success = $statusCode -eq 403
        Print-Result $success "Source A blocked (HTTP $statusCode)"
    }
}

# ========================================
# T117: Test GM conflict review flow
# ========================================
function Test-GmFlow {
    Write-ColorOutput "Blue" "`n[T117] Testing GM conflict review flow..."

    # Login as GM
    $gmToken = Get-AuthToken "gm@example.com"
    if (-not $gmToken) {
        Print-Result $false "GM login" "Failed to get auth token"
        return
    }
    Print-Result $true "GM login"

    $headers = @{
        "Authorization" = "Bearer $gmToken"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    # View conflict alerts
    try {
        $conflicts = Invoke-RestMethod -Uri "$API_BASE/conflict-alerts" -Headers $headers -Method Get
        Print-Result $true "View conflict alerts"

        if ($conflicts.data.Count -gt 0) {
            $conflictId = $conflicts.data[0].id

            # View detail
            try {
                $detail = Invoke-RestMethod -Uri "$API_BASE/conflict-alerts/$conflictId" -Headers $headers -Method Get
                Print-Result $true "View conflict detail: ID $conflictId"
            }
            catch {
                Print-Result $false "View conflict detail" $_.Exception.Message
            }

            # Resolve
            try {
                $resolveBody = @{ resolution_notes = "Resolved during local testing" } | ConvertTo-Json
                $resolved = Invoke-RestMethod -Uri "$API_BASE/conflict-alerts/$conflictId/resolve" -Headers $headers -Method Post -Body $resolveBody
                $success = $resolved.data.status -eq "resolved"
                Print-Result $success "Resolve conflict with notes"
            }
            catch {
                Print-Result $false "Resolve conflict" $_.Exception.Message
            }
        }
        else {
            Write-ColorOutput "Yellow" "  Note: No conflicts found to test resolution"
        }
    }
    catch {
        Print-Result $false "View conflict alerts" $_.Exception.Message
    }
}

# ========================================
# T118: Verify Source A/B isolation
# ========================================
function Test-SourceIsolation {
    Write-ColorOutput "Blue" "`n[T118] Testing Source A/B isolation..."

    # SDD trying to access Source B
    $sddToken = Get-AuthToken "sdd@example.com"
    if ($sddToken) {
        $headers = @{
            "Authorization" = "Bearer $sddToken"
            "Accept" = "application/json"
        }

        try {
            Invoke-RestMethod -Uri "$API_BASE/department-reports" -Headers $headers -Method Get
            Print-Result $false "SDD should be blocked from Source B" "SDD accessed department reports!"
        }
        catch {
            $statusCode = $_.Exception.Response.StatusCode.value__
            $success = $statusCode -eq 403
            Print-Result $success "SDD blocked from Source B (HTTP $statusCode)"
        }
    }

    # DeptManager trying to access Source A
    $dmToken = Get-AuthToken "deptmgr@example.com"
    if ($dmToken) {
        $headers = @{
            "Authorization" = "Bearer $dmToken"
            "Accept" = "application/json"
        }

        try {
            Invoke-RestMethod -Uri "$API_BASE/project-reports" -Headers $headers -Method Get
            Print-Result $false "DeptManager should be blocked from Source A" "DeptManager accessed project reports!"
        }
        catch {
            $statusCode = $_.Exception.Response.StatusCode.value__
            $success = $statusCode -eq 403
            Print-Result $success "DeptManager blocked from Source A (HTTP $statusCode)"
        }
    }

    # Verify audit logs
    $ceoToken = Get-AuthToken "ceo@example.com"
    if ($ceoToken) {
        $headers = @{
            "Authorization" = "Bearer $ceoToken"
            "Accept" = "application/json"
        }

        try {
            $auditLogs = Invoke-RestMethod -Uri "$API_BASE/audit-logs?action=access_denied" -Headers $headers -Method Get
            Print-Result $true "Audit logs accessible by CEO"
        }
        catch {
            Write-ColorOutput "Yellow" "  Note: Could not verify audit logs"
        }
    }
}

# ========================================
# MAIN
# ========================================
function Show-Help {
    Write-Host @"

Team Management Platform - Local Testing Script

Usage: .\test-local.ps1 [options]

Options:
  -Migrations    Test migrations and seeders only (T113)
  -Tests         Run backend tests only (T114)
  -Sdd           Test SDD flow only (T115)
  -DeptMgr       Test DeptManager flow only (T116)
  -Gm            Test GM flow only (T117)
  -Isolation     Test Source A/B isolation only (T118)
  -Help          Show this help

Examples:
  .\test-local.ps1                  # Run all tests
  .\test-local.ps1 -Migrations      # Test migrations only
  .\test-local.ps1 -Sdd -DeptMgr    # Test SDD and DeptManager flows

"@
}

# Main execution
Write-ColorOutput "Blue" "=========================================="
Write-ColorOutput "Blue" "  Team Management Platform - Local Tests  "
Write-ColorOutput "Blue" "=========================================="

if ($Help) {
    Show-Help
    exit 0
}

# Check if Docker is running
$dockerStatus = docker compose ps 2>&1
if (-not ($dockerStatus -match "Up")) {
    Write-ColorOutput "Red" "`nError: Docker containers are not running."
    Write-Host "Please run: docker compose up -d"
    exit 1
}

# Run selected tests or all
$runAll = -not ($Migrations -or $Tests -or $Sdd -or $DeptMgr -or $Gm -or $Isolation)

if ($Migrations -or $runAll) { Test-MigrationsAndSeeders }
if ($Tests -or $runAll) { Test-BackendTests }
if ($Sdd -or $runAll) { Test-SddFlow }
if ($DeptMgr -or $runAll) { Test-DeptManagerFlow }
if ($Gm -or $runAll) { Test-GmFlow }
if ($Isolation -or $runAll) { Test-SourceIsolation }

Write-ColorOutput "Blue" "`n=========================================="
Write-ColorOutput "Blue" "  Testing Complete"
Write-ColorOutput "Blue" "=========================================="
Write-Host @"

Review the results above. Manual browser testing may still be needed for:
  - Frontend UI/UX verification
  - Real-time features (WebSockets)
  - File upload/download

"@
