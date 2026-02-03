# GitLab CI/CD Configuration Guide
## Team Management Platform

## Overview

This GitLab CI/CD pipeline provides automated testing, linting, security scanning, and deployment for the Team Management Platform.

### Pipeline Stages

1. **Test** - Run backend (PHPUnit) and frontend (Vitest) tests
2. **Lint** - Code quality checks (PHP-CS-Fixer, ESLint)
3. **Security** - Dependency audits and secret detection
4. **Build** - Build Docker images for backend and frontend
5. **Deploy** - Deploy to staging/production environments

---

## Required GitLab CI/CD Variables

Configure these in **Settings > CI/CD > Variables**:

### Registry Authentication
| Variable | Description | Protected | Masked |
|----------|-------------|-----------|--------|
| `CI_REGISTRY` | GitLab Container Registry URL | No | No |
| `CI_REGISTRY_USER` | Registry username (auto-provided) | No | No |
| `CI_REGISTRY_PASSWORD` | Registry password (auto-provided) | No | Yes |

### Deployment - Staging
| Variable | Description | Protected | Masked |
|----------|-------------|-----------|--------|
| `SSH_PRIVATE_KEY` | SSH key for staging server | Yes | Yes |
| `STAGING_SERVER` | Staging server hostname/IP | No | No |
| `STAGING_USER` | SSH username for staging | No | No |

### Deployment - Production
| Variable | Description | Protected | Masked |
|----------|-------------|-----------|--------|
| `SSH_PRIVATE_KEY` | SSH key for production server | Yes | Yes |
| `PRODUCTION_SERVER` | Production server hostname/IP | Yes | No |
| `PRODUCTION_USER` | SSH username for production | Yes | No |

---

## Pipeline Jobs Explained

### Backend Tests (`backend-tests`)
**Purpose**: Run Laravel PHPUnit tests with code coverage

**Services**:
- PostgreSQL 15 (test database)
- Redis 7 (cache/session for tests)

**Actions**:
1. Install PHP 8.2 with required extensions
2. Install Composer dependencies
3. Run database migrations
4. Execute PHPUnit tests
5. Generate code coverage report

**Success Criteria**: 
- All tests pass
- Code coverage ≥70%

**Artifacts**:
- JUnit test report
- Cobertura coverage report
- HTML coverage files

---

### Frontend Tests (`frontend-tests`)
**Purpose**: Run React/TypeScript tests with Vitest

**Actions**:
1. Install Node.js 20
2. Install npm dependencies
3. Run Vitest tests
4. Generate coverage report

**Success Criteria**:
- All tests pass
- Coverage reports generated

**Artifacts**:
- JUnit test report
- Coverage report

---

### PHP Code Style (`php-cs-fixer`)
**Purpose**: Enforce PSR-12 coding standards

**Actions**:
- Run PHP-CS-Fixer in dry-run mode
- Report style violations

**Note**: Set to `allow_failure: true` (won't block pipeline)

---

### ESLint (`eslint`)
**Purpose**: Check TypeScript/React code quality

**Actions**:
- Run ESLint on frontend codebase
- Report linting errors

**Configuration**: Uses `.eslintrc.cjs` in frontend/

---

### Security Audits
**Composer Audit**: Check PHP dependencies for known vulnerabilities  
**NPM Audit**: Check JavaScript dependencies for vulnerabilities  
**Secret Detection**: Scan git history for exposed secrets

---

### Docker Image Building
**Purpose**: Build and push Docker images to GitLab Container Registry

**Jobs**:
- `build-backend`: Build Laravel backend image
- `build-frontend`: Build React frontend image

**Tags**:
- `{CI_COMMIT_SHORT_SHA}` - Specific commit SHA
- `latest` - Latest build

**Registry**: Images stored at `registry.gitlab.com/deepminds_info/team-management-platform`

---

### Deployment

#### Staging Deployment (`deploy-staging`)
**Trigger**: Manual (button click) on `develop` branch

**Actions**:
1. SSH to staging server
2. Pull latest `develop` code
3. Update Docker containers
4. Run migrations
5. Clear/cache Laravel config

**Environment**: `staging` - https://staging.yourcompany.com

---

#### Production Deployment (`deploy-production`)
**Trigger**: Manual (button click) on `main` branch

**Requirements**:
- All tests must pass
- Manual approval required

**Actions**:
1. **Database backup** (automatic before deploy)
2. SSH to production server
3. Pull latest `main` code
4. Update Docker containers
5. Run migrations (with backup)
6. Optimize Laravel caches
7. **Health check** (fails deployment if unhealthy)

**Environment**: `production` - https://yourcompany.com

**Rollback**: Use database backup created before deployment

---

## Setting Up GitLab Runner

If using self-hosted runners, configure as follows:

### 1. Install GitLab Runner
```bash
# Ubuntu/Debian
curl -L "https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.deb.sh" | sudo bash
sudo apt-get install gitlab-runner
```

### 2. Register Runner
```bash
sudo gitlab-runner register
```

**Configuration**:
- GitLab URL: `https://gitlab.com/`
- Registration token: Get from **Settings > CI/CD > Runners**
- Executor: `docker`
- Default image: `php:8.2-fpm`

### 3. Configure Docker Executor
Edit `/etc/gitlab-runner/config.toml`:

```toml
[[runners]]
  name = "team-mgmt-runner"
  executor = "docker"
  [runners.docker]
    image = "php:8.2-fpm"
    privileged = true
    volumes = ["/cache", "/var/run/docker.sock:/var/run/docker.sock"]
```

---

## Triggering Pipeline

### Automatic Triggers
- **Merge Request**: Runs tests, lint, security checks
- **Commit to `main`**: Full pipeline + Docker builds
- **Commit to `develop`**: Full pipeline + staging-ready builds

### Manual Triggers
- **Staging Deployment**: Click "Play" button in pipeline
- **Production Deployment**: Click "Play" button (requires approval)

### Scheduled Pipelines
Create in **CI/CD > Schedules** for:
- Daily security scans
- Weekly dependency updates

---

## Monitoring Pipeline

### View Pipeline Status
1. Go to **CI/CD > Pipelines**
2. Click on pipeline ID
3. View job statuses and logs

### Download Artifacts
1. Open completed pipeline
2. Click job name
3. Click "Browse" or "Download" artifacts

### Coverage Reports
- Backend coverage: `backend/coverage/index.html`
- Frontend coverage: `frontend/coverage/index.html`

---

## Troubleshooting

### Tests Failing in CIButPassing Locally

**Problem**: Database connection issues  
**Solution**: Check `DB_*` variables in `.gitlab-ci.yml`

**Problem**: Missing environment variables  
**Solution**: Ensure `.env.example` is complete

---

### Deployment Fails

**Problem**: SSH connection refused  
**Solution**: Verify `SSH_PRIVATE_KEY` is correct and server allows GitLab IP

**Problem**: Permission denied on server  
**Solution**: Ensure deployment user has Docker permissions:
```bash
sudo usermod -aG docker $DEPLOYMENT_USER
```

---

### Docker Build Fails

**Problem**: Registry authentication failed  
**Solution**: Regenerate deploy token in **Settings > Repository > Deploy Tokens**

**Problem**: Out of disk space  
**Solution**: Clean old images:
```bash
docker system prune -a
```

---

## Best Practices

### 1. Protect Main Branch
**Settings > Repository > Protected Branches**:
- ✅ Protect `main` branch
- ✅ Require successful pipeline
- ✅ Require approvals for merge

### 2. Use Merge Requests
- Always create MR for changes
- Review pipeline results before merging
- Require passing tests

### 3. Monitor Coverage
- Aim for ≥70% backend coverage
- Aim for ≥60% frontend coverage
- Reject MRs that lower coverage significantly

### 4. Security
- Review security audit results weekly
- Update dependencies regularly
- Never commit secrets (use CI/CD variables)

---

## Next Steps

1. ✅ Push `.gitlab-ci.yml` to repository
2. ⚠️ Configure CI/CD variables in GitLab
3. ⚠️ Update package.json with `test:ci` script
4. ⚠️ Set up deployment servers
5. ⚠️ Configure SSH keys for deployment
6. ⚠️ Test pipeline on merge request
7. ⚠️ Configure branch protection rules
8. ⚠️ Set up scheduled security scans

---

## Support

For issues with the pipeline:
1. Check job logs in GitLab CI/CD
2. Review this documentation
3. Contact DevOps team

---

**Last Updated**: February 3, 2026  
**Pipeline Version**: 1.0
