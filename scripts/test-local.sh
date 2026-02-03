#!/bin/bash
# Local Testing Script for Team Management Platform
# This script automates the validation tasks T113-T118

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
API_BASE="http://localhost/api/v1"
FRONTEND_URL="http://localhost:5173"

echo -e "${BLUE}==========================================${NC}"
echo -e "${BLUE}  Team Management Platform - Local Tests ${NC}"
echo -e "${BLUE}==========================================${NC}"
echo ""

# Function to print test result
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ PASS${NC}: $2"
    else
        echo -e "${RED}✗ FAIL${NC}: $2"
        if [ -n "$3" ]; then
            echo -e "  ${YELLOW}Error:${NC} $3"
        fi
    fi
}

# Function to login and get token
login() {
    local email=$1
    local password=${2:-password}

    response=$(curl -s -X POST "${API_BASE}/auth/login" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"email\":\"${email}\",\"password\":\"${password}\"}")

    echo "$response" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4
}

# ========================================
# T113: Verify migrations and seeders work
# ========================================
test_migrations_and_seeders() {
    echo ""
    echo -e "${BLUE}[T113] Testing migrations and seeders...${NC}"

    # Run fresh migration with seed
    docker compose exec -T backend php artisan migrate:fresh --seed --force 2>&1
    result=$?
    print_result $result "migrate:fresh --seed completed"

    # Verify users were seeded
    user_count=$(docker compose exec -T backend php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
    if [ "$user_count" -gt 0 ]; then
        print_result 0 "Users seeded: $user_count users found"
    else
        print_result 1 "Users seeded" "No users found in database"
    fi

    # Verify roles were seeded
    role_count=$(docker compose exec -T backend php artisan tinker --execute="echo Spatie\Permission\Models\Role::count();" 2>/dev/null | tail -1)
    if [ "$role_count" -ge 8 ]; then
        print_result 0 "Roles seeded: $role_count roles found"
    else
        print_result 1 "Roles seeded" "Expected at least 8 roles, found $role_count"
    fi

    # Verify departments were seeded
    dept_count=$(docker compose exec -T backend php artisan tinker --execute="echo App\Models\Department::count();" 2>/dev/null | tail -1)
    if [ "$dept_count" -ge 5 ]; then
        print_result 0 "Departments seeded: $dept_count departments found"
    else
        print_result 1 "Departments seeded" "Expected at least 5 departments, found $dept_count"
    fi

    # Verify projects were seeded
    project_count=$(docker compose exec -T backend php artisan tinker --execute="echo App\Models\Project::count();" 2>/dev/null | tail -1)
    if [ "$project_count" -gt 0 ]; then
        print_result 0 "Projects seeded: $project_count projects found"
    else
        print_result 1 "Projects seeded" "No projects found in database"
    fi
}

# ========================================
# T114: Run all backend tests
# ========================================
test_backend_tests() {
    echo ""
    echo -e "${BLUE}[T114] Running backend tests...${NC}"

    docker compose exec -T backend php artisan test --parallel 2>&1
    result=$?
    print_result $result "All backend tests pass"
}

# ========================================
# T115: Test SDD flow
# ========================================
test_sdd_flow() {
    echo ""
    echo -e "${BLUE}[T115] Testing SDD flow...${NC}"

    # Login as SDD
    SDD_TOKEN=$(login "sdd@example.com")
    if [ -z "$SDD_TOKEN" ]; then
        print_result 1 "SDD login" "Failed to get auth token"
        return 1
    fi
    print_result 0 "SDD login"

    # Get projects for this SDD
    projects=$(curl -s -X GET "${API_BASE}/projects" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Accept: application/json")

    project_id=$(echo "$projects" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    if [ -z "$project_id" ]; then
        print_result 1 "Get SDD projects" "No projects found for SDD"
        return 1
    fi
    print_result 0 "Get SDD projects: Found project $project_id"

    # Create a project report
    today=$(date +%Y-%m-%d)
    week_start=$(date -d "last monday" +%Y-%m-%d 2>/dev/null || date -v-monday +%Y-%m-%d 2>/dev/null || echo "2026-01-27")
    week_end=$(date -d "next sunday" +%Y-%m-%d 2>/dev/null || date -v+sunday +%Y-%m-%d 2>/dev/null || echo "2026-02-02")

    create_response=$(curl -s -X POST "${API_BASE}/project-reports" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"project_id\":${project_id},\"reporting_period_start\":\"${week_start}\",\"reporting_period_end\":\"${week_end}\"}")

    report_id=$(echo "$create_response" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    if [ -z "$report_id" ]; then
        print_result 1 "Create project report" "Failed to create report: $create_response"
        return 1
    fi
    print_result 0 "Create project report: ID $report_id"

    # Add an entry to the report
    entry_response=$(curl -s -X POST "${API_BASE}/project-reports/${report_id}/entries" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"employee_id":1,"hours_worked":40,"tasks_completed":5,"status":"on_track","accomplishments":"Test accomplishment"}')

    entry_id=$(echo "$entry_response" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    if [ -n "$entry_id" ]; then
        print_result 0 "Add worker entry: ID $entry_id"
    else
        print_result 1 "Add worker entry" "Failed: $entry_response"
    fi

    # Submit the report
    submit_response=$(curl -s -X POST "${API_BASE}/project-reports/${report_id}/submit" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Accept: application/json")

    if echo "$submit_response" | grep -q '"status":"submitted"'; then
        print_result 0 "Submit report"
    else
        print_result 1 "Submit report" "$submit_response"
    fi

    # View submitted report
    view_response=$(curl -s -X GET "${API_BASE}/project-reports/${report_id}" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Accept: application/json")

    if echo "$view_response" | grep -q "\"id\":${report_id}"; then
        print_result 0 "View submitted report"
    else
        print_result 1 "View submitted report" "$view_response"
    fi

    # Amend the report
    amend_response=$(curl -s -X POST "${API_BASE}/project-reports/${report_id}/amend" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"amendment_reason":"Test amendment for verification"}')

    if echo "$amend_response" | grep -q '"status":"amended"'; then
        print_result 0 "Amend report"
    else
        print_result 1 "Amend report" "$amend_response"
    fi

    # Try to delete (should fail)
    delete_response=$(curl -s -w "\n%{http_code}" -X DELETE "${API_BASE}/project-reports/${report_id}" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Accept: application/json")

    http_code=$(echo "$delete_response" | tail -1)
    if [ "$http_code" = "405" ] || [ "$http_code" = "403" ]; then
        print_result 0 "Delete blocked (HTTP $http_code)"
    else
        print_result 1 "Delete should be blocked" "Got HTTP $http_code"
    fi
}

# ========================================
# T116: Test DeptManager flow
# ========================================
test_deptmanager_flow() {
    echo ""
    echo -e "${BLUE}[T116] Testing Department Manager flow...${NC}"

    # Login as Dept Manager
    DM_TOKEN=$(login "deptmgr@example.com")
    if [ -z "$DM_TOKEN" ]; then
        print_result 1 "Dept Manager login" "Failed to get auth token"
        return 1
    fi
    print_result 0 "Dept Manager login"

    # Get departments for this manager
    departments=$(curl -s -X GET "${API_BASE}/departments" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Accept: application/json")

    dept_id=$(echo "$departments" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    if [ -z "$dept_id" ]; then
        print_result 1 "Get departments" "No departments found"
        return 1
    fi
    print_result 0 "Get departments: Found department $dept_id"

    # Create a department report
    week_start=$(date -d "last monday" +%Y-%m-%d 2>/dev/null || date -v-monday +%Y-%m-%d 2>/dev/null || echo "2026-01-27")
    week_end=$(date -d "next sunday" +%Y-%m-%d 2>/dev/null || date -v+sunday +%Y-%m-%d 2>/dev/null || echo "2026-02-02")

    create_response=$(curl -s -X POST "${API_BASE}/department-reports" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"department_id\":${dept_id},\"reporting_period_start\":\"${week_start}\",\"reporting_period_end\":\"${week_end}\"}")

    report_id=$(echo "$create_response" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    if [ -z "$report_id" ]; then
        print_result 1 "Create department report" "Failed: $create_response"
        return 1
    fi
    print_result 0 "Create department report: ID $report_id"

    # Add an entry
    entry_response=$(curl -s -X POST "${API_BASE}/department-reports/${report_id}/entries" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"employee_id":1,"hours_worked":40,"tasks_completed":3,"status":"productive","work_description":"Test work description"}')

    if echo "$entry_response" | grep -q '"id":'; then
        print_result 0 "Add employee entry"
    else
        print_result 1 "Add employee entry" "$entry_response"
    fi

    # Submit
    submit_response=$(curl -s -X POST "${API_BASE}/department-reports/${report_id}/submit" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Accept: application/json")

    if echo "$submit_response" | grep -q '"status":"submitted"'; then
        print_result 0 "Submit report"
    else
        print_result 1 "Submit report" "$submit_response"
    fi

    # Verify NO access to Source A (project reports)
    source_a_response=$(curl -s -w "\n%{http_code}" -X GET "${API_BASE}/project-reports" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Accept: application/json")

    http_code=$(echo "$source_a_response" | tail -1)
    if [ "$http_code" = "403" ]; then
        print_result 0 "Source A blocked (HTTP 403)"
    else
        print_result 1 "Source A should be blocked" "Got HTTP $http_code - DeptManager should NOT access project reports"
    fi
}

# ========================================
# T117: Test GM conflict review flow
# ========================================
test_gm_flow() {
    echo ""
    echo -e "${BLUE}[T117] Testing GM conflict review flow...${NC}"

    # Login as GM
    GM_TOKEN=$(login "gm@example.com")
    if [ -z "$GM_TOKEN" ]; then
        print_result 1 "GM login" "Failed to get auth token"
        return 1
    fi
    print_result 0 "GM login"

    # View conflict alerts
    conflicts=$(curl -s -X GET "${API_BASE}/conflict-alerts" \
        -H "Authorization: Bearer ${GM_TOKEN}" \
        -H "Accept: application/json")

    if echo "$conflicts" | grep -q '"data":'; then
        print_result 0 "View conflict alerts"
    else
        print_result 1 "View conflict alerts" "$conflicts"
    fi

    # Get first open conflict (if any)
    conflict_id=$(echo "$conflicts" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

    if [ -n "$conflict_id" ]; then
        # View conflict detail
        detail=$(curl -s -X GET "${API_BASE}/conflict-alerts/${conflict_id}" \
            -H "Authorization: Bearer ${GM_TOKEN}" \
            -H "Accept: application/json")

        if echo "$detail" | grep -q "\"id\":${conflict_id}"; then
            print_result 0 "View conflict detail: ID $conflict_id"
        else
            print_result 1 "View conflict detail" "$detail"
        fi

        # Resolve the conflict
        resolve_response=$(curl -s -X POST "${API_BASE}/conflict-alerts/${conflict_id}/resolve" \
            -H "Authorization: Bearer ${GM_TOKEN}" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d '{"resolution_notes":"Resolved during local testing - hours were correctly reported by both sources after verification."}')

        if echo "$resolve_response" | grep -q '"status":"resolved"'; then
            print_result 0 "Resolve conflict with notes"
        else
            print_result 1 "Resolve conflict" "$resolve_response"
        fi
    else
        echo -e "  ${YELLOW}Note:${NC} No conflicts found to test resolution. Run conflict detection job first."
    fi
}

# ========================================
# T118: Verify Source A/B isolation
# ========================================
test_source_isolation() {
    echo ""
    echo -e "${BLUE}[T118] Testing Source A/B isolation...${NC}"

    # Login as SDD
    SDD_TOKEN=$(login "sdd@example.com")
    if [ -z "$SDD_TOKEN" ]; then
        print_result 1 "SDD login for isolation test" "Failed to get auth token"
        return 1
    fi

    # SDD tries to access Source B (department reports) - should be blocked
    sdd_source_b=$(curl -s -w "\n%{http_code}" -X GET "${API_BASE}/department-reports" \
        -H "Authorization: Bearer ${SDD_TOKEN}" \
        -H "Accept: application/json")

    http_code=$(echo "$sdd_source_b" | tail -1)
    if [ "$http_code" = "403" ]; then
        print_result 0 "SDD blocked from Source B (HTTP 403)"
    else
        print_result 1 "SDD should be blocked from Source B" "Got HTTP $http_code"
    fi

    # Login as DeptManager
    DM_TOKEN=$(login "deptmgr@example.com")
    if [ -z "$DM_TOKEN" ]; then
        print_result 1 "DeptManager login for isolation test" "Failed to get auth token"
        return 1
    fi

    # DeptManager tries to access Source A (project reports) - should be blocked
    dm_source_a=$(curl -s -w "\n%{http_code}" -X GET "${API_BASE}/project-reports" \
        -H "Authorization: Bearer ${DM_TOKEN}" \
        -H "Accept: application/json")

    http_code=$(echo "$dm_source_a" | tail -1)
    if [ "$http_code" = "403" ]; then
        print_result 0 "DeptManager blocked from Source A (HTTP 403)"
    else
        print_result 1 "DeptManager should be blocked from Source A" "Got HTTP $http_code"
    fi

    # Verify audit logs capture access attempts
    CEO_TOKEN=$(login "ceo@example.com")
    if [ -n "$CEO_TOKEN" ]; then
        audit_logs=$(curl -s -X GET "${API_BASE}/audit-logs?action=access_denied" \
            -H "Authorization: Bearer ${CEO_TOKEN}" \
            -H "Accept: application/json")

        if echo "$audit_logs" | grep -q '"data":'; then
            print_result 0 "Audit logs accessible by CEO"
        else
            echo -e "  ${YELLOW}Note:${NC} Could not verify audit logs"
        fi
    fi
}

# ========================================
# MAIN EXECUTION
# ========================================
main() {
    echo ""
    echo -e "${YELLOW}Starting local validation tests...${NC}"
    echo ""

    # Check if Docker is running
    if ! docker compose ps | grep -q "Up"; then
        echo -e "${RED}Error:${NC} Docker containers are not running."
        echo "Please run: docker compose up -d"
        exit 1
    fi

    # Run all tests
    test_migrations_and_seeders
    test_backend_tests
    test_sdd_flow
    test_deptmanager_flow
    test_gm_flow
    test_source_isolation

    echo ""
    echo -e "${BLUE}==========================================${NC}"
    echo -e "${BLUE}  Testing Complete ${NC}"
    echo -e "${BLUE}==========================================${NC}"
    echo ""
    echo -e "Review the results above. Manual browser testing may still be needed for:"
    echo -e "  - Frontend UI/UX verification"
    echo -e "  - Real-time features (WebSockets)"
    echo -e "  - File upload/download"
    echo ""
}

# Parse arguments
case "$1" in
    --migrations|-m)
        test_migrations_and_seeders
        ;;
    --tests|-t)
        test_backend_tests
        ;;
    --sdd)
        test_sdd_flow
        ;;
    --deptmgr|--dm)
        test_deptmanager_flow
        ;;
    --gm)
        test_gm_flow
        ;;
    --isolation|-i)
        test_source_isolation
        ;;
    --help|-h)
        echo "Usage: $0 [option]"
        echo ""
        echo "Options:"
        echo "  (no args)      Run all tests"
        echo "  --migrations   Test migrations and seeders only (T113)"
        echo "  --tests        Run backend tests only (T114)"
        echo "  --sdd          Test SDD flow only (T115)"
        echo "  --deptmgr      Test DeptManager flow only (T116)"
        echo "  --gm           Test GM flow only (T117)"
        echo "  --isolation    Test Source A/B isolation only (T118)"
        echo "  --help         Show this help"
        ;;
    *)
        main
        ;;
esac
