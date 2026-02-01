#!/bin/bash
# Team Management Platform - Directory Structure Creator
# Creates the monorepo folder structure for the Dual Independent Reporting System

set -e

echo "=============================================="
echo "  Team Management Platform - Project Setup"
echo "=============================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_done() {
    echo -e "${GREEN}[DONE]${NC} $1"
}

# Backend (Laravel 11)
print_step "Creating backend directory structure..."
mkdir -p backend/{app,bootstrap,config,database,public,resources,routes,storage,tests}
mkdir -p backend/app/{Console,Exceptions,Http,Models,Policies,Providers,Services,Observers,Jobs}
mkdir -p backend/app/Http/{Controllers,Middleware,Requests}
mkdir -p backend/database/{factories,migrations,seeders}
mkdir -p backend/storage/{app,framework,logs}
mkdir -p backend/storage/framework/{cache,sessions,views}
mkdir -p backend/tests/{Feature,Unit}
print_done "Backend structure created"

# Frontend (React + Vite + TypeScript)
print_step "Creating frontend directory structure..."
mkdir -p frontend/{src,public,tests}
mkdir -p frontend/src/{components,pages,services,store,hooks,guards,types,assets}
mkdir -p frontend/src/components/{common,dashboards,reports}
mkdir -p frontend/src/components/dashboards/{CeoDashboard,CfoDashboard,GmDashboard,DirectorDashboard,SddDashboard,DeptManagerDashboard}
mkdir -p frontend/src/components/reports/{ProjectReportForm,DepartmentReportForm}
mkdir -p frontend/src/store/{slices,api}
mkdir -p frontend/src/services/api
print_done "Frontend structure created"

# AI Service (Python FastAPI)
print_step "Creating ai-service directory structure..."
mkdir -p ai-service/{app,tests,models}
mkdir -p ai-service/app/{routers,services,schemas,core}
print_done "AI service structure created"

# Docker configs
print_step "Creating Docker configuration directories..."
mkdir -p docker/{nginx,php,python}
print_done "Docker directories created"

# Scripts
print_step "Creating scripts directory..."
mkdir -p scripts
print_done "Scripts directory created"

# Specs (if not exists)
print_step "Ensuring specs directory exists..."
mkdir -p specs
print_done "Specs directory verified"

echo ""
echo "=============================================="
echo "  Project structure created successfully!"
echo "=============================================="
echo ""
echo "Next steps:"
echo "  1. Run 'make setup' to start Docker containers"
echo "  2. Run 'make install' to install dependencies"
echo "  3. Run 'make migrate' to run database migrations"
echo ""
