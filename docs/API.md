# API Documentation Summary

This document provides a high-level overview of the API architecture for the Team Management Platform. Detailed endpoint specifications, request/response schemas, and authentication examples can be found in the auto-generated documentation.

## 1. API Architecture Overview

The platform is built on a microservices architecture, with several backend services providing distinct functionalities. These services communicate internally via HTTP and expose APIs consumed by the React frontend.

*   **Core API**: Handles authentication, user management, roles, permissions, and audit logging.
*   **Reporting API**: Manages Project Reports (Source A) and Department Reports (Source B), including report submission, amendments, and entries.
*   **AI/ML API**: A Python FastAPI service responsible for machine learning predictions (e.g., attrition, churn) and analytical insights.

## 2. Accessing Detailed API Documentation

The primary source for comprehensive API documentation is generated automatically by **Scribe** from the backend Laravel codebase. This documentation includes:

*   Detailed descriptions of all available endpoints.
*   Request parameters and their types.
*   Example request and response payloads.
*   Authentication methods required for each endpoint.

You can access the live API documentation by starting the Docker services and navigating to:

**Live API Docs**: `http://localhost:8000/docs`

## 3. OpenAPI Specifications (Contracts)

For a more programmatic understanding and integration purposes, the API contracts are also defined using OpenAPI (Swagger) specifications. These YAML files provide a machine-readable definition of the API endpoints and data models.

You can find the OpenAPI specification files in the `specs/001-dual-reporting/contracts/` directory:

*   [`core-api.yaml`](../../specs/001-dual-reporting/contracts/core-api.yaml)
*   [`reporting-api.yaml`](../../specs/001-dual-reporting/contracts/reporting-api.yaml)
*   [`analytics-api.yaml`](../../specs/001-dual-reporting/contracts/analytics-api.yaml)
*   [`ai-ml-api.yaml`](../../specs/001-dual-reporting/contracts/ai-ml-api.yaml)

These files can be used with tools like Swagger UI, Postman, or various code generators.

## 4. Authentication

The API uses **Laravel Sanctum** for SPA authentication (cookie-based) for the React frontend and **Bearer Tokens (JWT)** for API clients and service-to-service communication.

*   **SPA Authentication**: The frontend handles authentication via the `/api/v1/auth/login` endpoint, which establishes a secure session using HTTP-only cookies.
*   **Bearer Token Authentication**: For external API calls or internal service-to-service communication, an `access_token` obtained from the login endpoint should be included in the `Authorization` header as a `Bearer` token.

**Example Authentication Header**:

```
Authorization: Bearer <your_access_token>
```

## 5. API Endpoints Overview

Here's a brief overview of the main API endpoint groups:

*   `/api/v1/auth`: User authentication (login, logout, user details).
*   `/api/v1/project-reports`: CRUD for Project Reports and their entries.
*   `/api/v1/department-reports`: CRUD for Department Reports and their entries.
*   `/api/v1/conflict-alerts`: Management of detected reporting conflicts.
*   `/api/v1/dashboards`: Data endpoints for executive dashboards (CEO, CFO, GM).
*   `/api/v1/audit-logs`: Access to immutable audit trails (restricted).

Please refer to the live Scribe documentation for exact paths and methods.
