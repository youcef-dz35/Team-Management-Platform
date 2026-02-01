#!/bin/bash
# healthcheck.sh - Docker health check script for services
# Usage: ./healthcheck.sh [service] [options]

set -e

SERVICE="${1:-all}"
VERBOSE="${VERBOSE:-0}"

log() {
    if [ "$VERBOSE" = "1" ]; then
        echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1"
    fi
}

check_http() {
    local url=$1
    local timeout=${2:-5}

    if curl -sf --max-time "$timeout" "$url" > /dev/null 2>&1; then
        log "✓ HTTP check passed: $url"
        return 0
    else
        log "✗ HTTP check failed: $url"
        return 1
    fi
}

check_tcp() {
    local host=$1
    local port=$2
    local timeout=${3:-5}

    if nc -z -w "$timeout" "$host" "$port" > /dev/null 2>&1; then
        log "✓ TCP check passed: $host:$port"
        return 0
    else
        log "✗ TCP check failed: $host:$port"
        return 1
    fi
}

check_postgres() {
    local host="${POSTGRES_HOST:-postgres}"
    local port="${POSTGRES_PORT:-5432}"
    local user="${POSTGRES_USER:-app}"
    local db="${POSTGRES_DB:-team_mgmt}"

    if pg_isready -h "$host" -p "$port" -U "$user" -d "$db" > /dev/null 2>&1; then
        log "✓ PostgreSQL is ready"
        return 0
    else
        log "✗ PostgreSQL is not ready"
        return 1
    fi
}

check_redis() {
    local host="${REDIS_HOST:-redis}"
    local port="${REDIS_PORT:-6379}"

    if redis-cli -h "$host" -p "$port" ping > /dev/null 2>&1; then
        log "✓ Redis is ready"
        return 0
    else
        log "✗ Redis is not ready"
        return 1
    fi
}

check_backend() {
    check_http "http://localhost:9000/status" 5
}

check_frontend() {
    check_http "http://localhost:5173" 5
}

check_ai_service() {
    check_http "http://localhost:8000/health" 5
}

check_nginx() {
    check_http "http://localhost:80/health" 5
}

check_meilisearch() {
    check_http "http://localhost:7700/health" 5
}

check_minio() {
    check_http "http://localhost:9000/minio/health/live" 5
}

case "$SERVICE" in
    postgres)
        check_postgres
        ;;
    redis)
        check_redis
        ;;
    backend)
        check_backend
        ;;
    frontend)
        check_frontend
        ;;
    ai-service)
        check_ai_service
        ;;
    nginx)
        check_nginx
        ;;
    meilisearch)
        check_meilisearch
        ;;
    minio)
        check_minio
        ;;
    all)
        FAILED=0
        check_postgres || FAILED=1
        check_redis || FAILED=1
        check_backend || FAILED=1
        check_frontend || FAILED=1
        check_ai_service || FAILED=1
        check_nginx || FAILED=1

        if [ $FAILED -eq 0 ]; then
            echo "All services are healthy"
            exit 0
        else
            echo "Some services are unhealthy"
            exit 1
        fi
        ;;
    *)
        echo "Unknown service: $SERVICE"
        echo "Usage: $0 [postgres|redis|backend|frontend|ai-service|nginx|meilisearch|minio|all]"
        exit 1
        ;;
esac
