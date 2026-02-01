# Team Management Platform - Makefile
# Development automation commands

.PHONY: help setup up down build rebuild install shell logs migrate seed test clean fresh

# Default target
.DEFAULT_GOAL := help

# Colors
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m

#======================================
# HELP
#======================================
help: ## Show this help message
	@echo ""
	@echo "$(BLUE)Team Management Platform$(NC) - Development Commands"
	@echo ""
	@echo "$(GREEN)Usage:$(NC) make [target]"
	@echo ""
	@echo "$(YELLOW)Setup & Lifecycle:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; /^(setup|up|down|build|rebuild|install|fresh|clean)/ {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Development:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; /^(shell|logs|migrate|seed|test)/ {printf "  $(BLUE)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""

#======================================
# SETUP & LIFECYCLE
#======================================
setup: ## Initial setup: start containers and install dependencies
	@echo "$(BLUE)[Setup]$(NC) Starting Docker containers..."
	docker compose up -d
	@echo "$(BLUE)[Setup]$(NC) Waiting for services to be healthy..."
	@sleep 10
	@echo "$(BLUE)[Setup]$(NC) Installing backend dependencies..."
	docker compose exec backend composer install --no-interaction
	@echo "$(BLUE)[Setup]$(NC) Installing frontend dependencies..."
	docker compose exec frontend npm install
	@echo "$(BLUE)[Setup]$(NC) Installing AI service dependencies..."
	docker compose exec ai-service pip install -r requirements.txt
	@echo "$(GREEN)[Done]$(NC) Setup complete! Run 'make migrate' to set up the database."

up: ## Start all Docker containers
	@echo "$(BLUE)[Docker]$(NC) Starting containers..."
	docker compose up -d
	@echo "$(GREEN)[Done]$(NC) Containers started. Access the app at http://localhost"

down: ## Stop all Docker containers
	@echo "$(BLUE)[Docker]$(NC) Stopping containers..."
	docker compose down
	@echo "$(GREEN)[Done]$(NC) Containers stopped."

build: ## Build all Docker images
	@echo "$(BLUE)[Docker]$(NC) Building images..."
	docker compose build
	@echo "$(GREEN)[Done]$(NC) Build complete."

rebuild: ## Rebuild and restart all containers
	@echo "$(BLUE)[Docker]$(NC) Rebuilding and restarting..."
	docker compose down
	docker compose build --no-cache
	docker compose up -d
	@echo "$(GREEN)[Done]$(NC) Rebuild complete."

install: ## Install dependencies in all services
	@echo "$(BLUE)[Install]$(NC) Installing backend dependencies..."
	docker compose exec backend composer install --no-interaction
	@echo "$(BLUE)[Install]$(NC) Installing frontend dependencies..."
	docker compose exec frontend npm install
	@echo "$(BLUE)[Install]$(NC) Installing AI service dependencies..."
	docker compose exec ai-service pip install -r requirements.txt
	@echo "$(GREEN)[Done]$(NC) All dependencies installed."

fresh: ## Fresh install: destroy volumes and rebuild everything
	@echo "$(RED)[Warning]$(NC) This will destroy all data!"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d
	@sleep 10
	$(MAKE) install
	$(MAKE) migrate
	$(MAKE) seed
	@echo "$(GREEN)[Done]$(NC) Fresh install complete."

clean: ## Remove all containers, volumes, and build cache
	@echo "$(RED)[Warning]$(NC) This will destroy all data!"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	docker compose down -v --rmi local
	docker system prune -f
	@echo "$(GREEN)[Done]$(NC) Cleanup complete."

#======================================
# DEVELOPMENT
#======================================
shell: ## Enter a container shell (usage: make shell SERVICE=backend)
ifndef SERVICE
	@echo "$(YELLOW)[Usage]$(NC) make shell SERVICE=<service_name>"
	@echo "$(BLUE)Available services:$(NC) backend, frontend, ai-service, postgres, redis"
else ifeq ($(SERVICE),backend)
	docker compose exec backend bash
else ifeq ($(SERVICE),frontend)
	docker compose exec frontend sh
else ifeq ($(SERVICE),ai-service)
	docker compose exec ai-service bash
else ifeq ($(SERVICE),postgres)
	docker compose exec postgres psql -U app -d team_mgmt
else ifeq ($(SERVICE),redis)
	docker compose exec redis redis-cli
else
	docker compose exec $(SERVICE) sh
endif

logs: ## Tail logs for all services (or specific: make logs SERVICE=backend)
ifndef SERVICE
	docker compose logs -f
else
	docker compose logs -f $(SERVICE)
endif

migrate: ## Run database migrations
	@echo "$(BLUE)[Migrate]$(NC) Running migrations..."
	docker compose exec backend php artisan migrate
	@echo "$(GREEN)[Done]$(NC) Migrations complete."

seed: ## Seed the database
	@echo "$(BLUE)[Seed]$(NC) Seeding database..."
	docker compose exec backend php artisan db:seed
	@echo "$(GREEN)[Done]$(NC) Database seeded."

test: ## Run tests in all services
	@echo "$(BLUE)[Test]$(NC) Running backend tests..."
	docker compose exec backend php artisan test
	@echo "$(BLUE)[Test]$(NC) Running frontend tests..."
	docker compose exec frontend npm test -- --run
	@echo "$(BLUE)[Test]$(NC) Running AI service tests..."
	docker compose exec ai-service pytest
	@echo "$(GREEN)[Done]$(NC) All tests complete."

#======================================
# BACKEND SPECIFIC
#======================================
artisan: ## Run artisan command (usage: make artisan CMD="migrate:status")
ifndef CMD
	@echo "$(YELLOW)[Usage]$(NC) make artisan CMD=\"<command>\""
else
	docker compose exec backend php artisan $(CMD)
endif

tinker: ## Open Laravel Tinker REPL
	docker compose exec backend php artisan tinker

cache-clear: ## Clear all Laravel caches
	@echo "$(BLUE)[Cache]$(NC) Clearing all caches..."
	docker compose exec backend php artisan cache:clear
	docker compose exec backend php artisan config:clear
	docker compose exec backend php artisan route:clear
	docker compose exec backend php artisan view:clear
	@echo "$(GREEN)[Done]$(NC) Caches cleared."

#======================================
# FRONTEND SPECIFIC
#======================================
npm: ## Run npm command (usage: make npm CMD="run lint")
ifndef CMD
	@echo "$(YELLOW)[Usage]$(NC) make npm CMD=\"<command>\""
else
	docker compose exec frontend npm $(CMD)
endif

#======================================
# AI SERVICE SPECIFIC
#======================================
pip: ## Run pip command (usage: make pip CMD="install pandas")
ifndef CMD
	@echo "$(YELLOW)[Usage]$(NC) make pip CMD=\"<command>\""
else
	docker compose exec ai-service pip $(CMD)
endif

python: ## Run Python command (usage: make python CMD="-c 'print(1)'")
ifndef CMD
	docker compose exec ai-service python
else
	docker compose exec ai-service python $(CMD)
endif

#======================================
# DATABASE
#======================================
db-reset: ## Reset database (drop all tables and re-migrate)
	@echo "$(RED)[Warning]$(NC) This will destroy all database data!"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	docker compose exec backend php artisan migrate:fresh --seed
	@echo "$(GREEN)[Done]$(NC) Database reset complete."

db-backup: ## Backup the database to ./backups/
	@mkdir -p backups
	@echo "$(BLUE)[Backup]$(NC) Creating database backup..."
	docker compose exec postgres pg_dump -U app team_mgmt > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)[Done]$(NC) Backup saved to ./backups/"

#======================================
# STATUS & INFO
#======================================
status: ## Show status of all containers
	docker compose ps

health: ## Check health of all services
	@echo "$(BLUE)[Health Check]$(NC)"
	@echo ""
	@echo "PostgreSQL:"
	@docker compose exec postgres pg_isready -U app -d team_mgmt && echo "  $(GREEN)✓ Healthy$(NC)" || echo "  $(RED)✗ Unhealthy$(NC)"
	@echo ""
	@echo "Redis:"
	@docker compose exec redis redis-cli ping && echo "  $(GREEN)✓ Healthy$(NC)" || echo "  $(RED)✗ Unhealthy$(NC)"
	@echo ""
	@echo "Backend:"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health 2>/dev/null | grep -q 200 && echo "  $(GREEN)✓ Healthy$(NC)" || echo "  $(YELLOW)? Check manually$(NC)"
	@echo ""
	@echo "Frontend:"
	@curl -s -o /dev/null -w "%{http_code}" http://localhost:5173 2>/dev/null | grep -q 200 && echo "  $(GREEN)✓ Healthy$(NC)" || echo "  $(YELLOW)? Check manually$(NC)"
