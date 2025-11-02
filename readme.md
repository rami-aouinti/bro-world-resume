# Bro World Resume Service

## Table of Contents
1. [Project Overview](#project-overview)
2. [Quick Start](#quick-start)
3. [Domain & Architecture](#domain--architecture)
4. [Tech Stack](#tech-stack)
5. [Environment Configuration](#environment-configuration)
6. [Key Services & Tooling](#key-services--tooling)
7. [Running Tests & Quality Gates](#running-tests--quality-gates)
8. [API Usage](#api-usage)
9. [Configuration Reference](#configuration-reference)
10. [Deployment Considerations](#deployment-considerations)
11. [Contribution Guidelines](#contribution-guidelines)
12. [Troubleshooting & Support](#troubleshooting--support)

## Project Overview
The Bro World Resume Service provides the curriculum vitae backend that powers [`bro-world-portfolio-main`](https://github.com/bro-world/bro-world-portfolio-main).
It exposes a JSON REST API (Symfony 7, PHP 8.4) that stores and serves canonical resume data for every Bro World member.

The platform is built around a **user-centric data model**: every resume record is uniquely tied to a `userId` UUID (stored as an ordered binary UUID for fast lookups).
Each public query requires the `userId` and every private mutation verifies that incoming payloads reference the same identifier, ensuring perfect alignment between
profile management tools and the public-facing portfolio.

Key capabilities include:
- CRUD endpoints for resumes plus the nested collections (experiences, education, skills) with DTO validation and optimistic field guarding.
- Projection endpoints optimised for the public portfolio application, aggregating resume, experiences, education, and skills in a single network hop.
- Strict `userId` ownership checks for write operations to prevent cross-account data leaks.
- Scheduler hooks to refresh portfolio caches and keep public data in sync with private edits.

## Quick Start
1. **Install prerequisites**: Docker, Docker Compose, and GNU Make must be available on your workstation.
2. **Clone the repository** and bootstrap the environment variables:
   ```bash
   git clone git@github.com:bro-world/bro-world-resume.git
   cd bro-world-resume
   cp .env .env.local
   ```
3. **Adjust secrets and overrides** (`DATABASE_URL`, `JWT_PASSPHRASE`, etc.) in `.env.local` (or create `.env.staging` / `.env.prod`).
4. **Build and start the development stack**:
   ```bash
   make build
   make start
   ```
5. **Provision the application**:
   ```bash
   make composer-install
   make migrate
   make messenger-setup-transports
   make generate-jwt-keys
   ```
6. **Verify the installation**
   - Visit `http://localhost/api/doc` for the generated OpenAPI documentation.
   - Run the regression suite: `make phpunit`.
7. **Stop services** with `make stop` and remove containers/volumes using `make down` when required.

`make help` provides a full catalogue of orchestration commands.

## Domain & Architecture
- **Domain layer** – Entities such as `Resume`, `Experience`, `Education`, and `Skill` represent the source of truth. A `Resume` owns the other aggregates, and all records are keyed by the same `userId` UUID.
- **Application layer** – `*Resource` services expose transactional CRUD functionality with DTO validation, `userId` ownership checks, and lifecycle hooks that maintain the resume relationships.
- **Transport layer** – Attribute-based controllers combine reusable REST traits for the authenticated back-office API (`/api/v1/...`) and thin public projection endpoints (`/api/public/resume/...`).
- **Infrastructure layer** – Doctrine repositories and data fixtures provide persistence, while the scheduler bundle drives recurring cache refresh commands for portfolio synchronisation.

See `docs/api-endpoints.md` for a detailed map of use cases, request/response payloads, and constraint rules (including the ubiquitous `userId`).

## Tech Stack
The application ships as a containerised environment orchestrated via Docker Compose. Core components include:
- **Symfony 7 + PHP 8.4 FPM** for the HTTP API and background workers.
- **Nginx** as the gateway.
- **MySQL 8** for relational persistence (with ordered UUID columns).
- **Redis** for caching, lock management, and queueing.
- **RabbitMQ 4** for async messaging.
- **Elasticsearch 7 + Kibana** for search/observability dashboards.
- **Mailpit** to capture outbound email during development.

Supporting developer tooling: PHPUnit, Easy Coding Standard, PHPStan, PHP Insights, Rector, PhpMetrics, PhpMD, PhpCPD, Composer QA commands, and JetBrains Qodana integration.

## Environment Configuration
The Compose files (`compose.yaml`, `compose-staging.yaml`, `compose-prod.yaml`, `compose-test-ci.yaml`) deliver isolated stacks for local, staging, production-like, and CI contexts.
GNU Make targets wrap the orchestration:

| Stage | Build | Start | Stop | Tear Down |
| --- | --- | --- | --- | --- |
| Development | `make build` | `make start` | `make stop` | `make down` |
| Testing/CI | `make build-test` | `make start-test` | `make stop-test` | `make down-test` |
| Staging | `make build-staging` | `make start-staging` | `make stop-staging` | `make down-staging` |
| Production | `make build-prod` | `make start-prod` | `make stop-prod` | `make down-prod` |

Additional helpers:
- `make generate-jwt-keys` to provision signing keys.
- `make messenger-setup-transports`, `make migrate`, `make migrate-cron-jobs` for persistence and background workers.
- `make ssh-*` targets to open shell sessions inside running containers.
- `make logs-*` to tail service logs.

## Key Services & Tooling
### Local Services
Once started, local dashboards are available at:
- OpenAPI (Swagger UI): `http://localhost/api/doc`
- RabbitMQ management: `http://localhost:15672`
- Kibana: `http://localhost:5601`
- Mailpit inbox: `http://localhost:8025`

### Monitoring & Diagnostics
- Doctrine profiling and the Symfony debug toolbar are enabled in `dev` for rapid feedback.
- Docker logs are accessible through `make logs-<service>`.
- Kibana dashboards aggregate search indices and application logs.

## Running Tests & Quality Gates
Execute the PHPUnit suite from the host with:
```bash
make phpunit
```

Additional quality tooling:
- Static analysis: `make phpstan`
- Coding standards: `make ecs` (auto-fix via `make ecs-fix`) and `make phpcs`
- Architecture metrics: `make phpmetrics`
- Code smells: `make phpmd`
- Duplicate detection: `make phpcpd` or `make phpcpd-html-report`
- Dependency hygiene: `make composer-normalize`, `make composer-validate`, `make composer-unused`, `make composer-require-checker`
- Holistic insights: `make phpinsights`

Use composite targets such as `make qa` before opening a pull request.

## API Usage
All routes live beneath `/api`. Back-office endpoints reside under `/api/v1/...` and accept authenticated clients (JWT bearer tokens or API keys). Public projection routes are under `/api/public/resume/...` and are anonymous but still gated by a valid `userId` UUID in the URL.

Highlights:
- `POST /api/v1/resume` – Create a resume for a user (`userId` is mandatory and must remain unique).
- `PATCH /api/v1/resume/{id}` – Update the resume headline, summary, contact fields, etc. Ownership is validated via `userId`.
- `POST /api/v1/experience|education|skill` – Append nested entries to an existing resume. Each payload must carry the same `userId` as the resume it references.
- `GET /api/public/resume/{userId}` – Fetch the full projection consumed by the portfolio frontend (resume + collections in one response).

Refer to `docs/api-endpoints.md` for complete payload definitions and constraint matrices.

## Configuration Reference
### Environment Variables
| Variable | Purpose |
| --- | --- |
| `APP_ENV` | Chooses the runtime environment (`dev`, `test`, `prod`). |
| `APP_DEBUG` | Enables debug mode in non-production contexts. |
| `DATABASE_URL` | Doctrine DSN for MySQL. |
| `MESSENGER_TRANSPORT_DSN` | RabbitMQ transport for async messages. |
| `REDIS_URL` | Redis connection string for cache pools and locks. |
| `ELASTICSEARCH_HOST` | Hostname for Elasticsearch. |
| `MAILER_DSN` | Outbound mail transport (Mailpit in development). |
| `JWT_PASSPHRASE` | Protects generated JWT private keys. |

### Database & Migrations
- Run `make migrate` (or `make migrate-no-test` for production) after adjusting Doctrine metadata.
- Fixture data is loaded through `App\Resume\Infrastructure\DataFixtures\ResumeFixtures` and ensures a canonical resume is available for demos/tests.
- Scheduler migrations register resume cache refresh commands to keep the portfolio layer hot.

### Assets & Frontend Integrations
- Asset management relies on Symfony AssetMapper (`assets/`). Use `make asset-install` and `make asset-dev-server` for iterative frontend work.
- Frontend clients should rely on the documented REST APIs. Every resume-related call must supply the correct `userId` UUID; cache keys and projections are derived from it.

## Deployment Considerations
- Prepare environment-specific overrides (`.env.prod`, `.env.staging`) for secrets and backing services.
- Use `make env-prod` / `make env-staging` to compile cached Symfony configuration (`.env.local.php`).
- Build immutable images with `make build-prod` (or stage equivalent) before publishing to registries.
- Initialise databases with the migration targets plus the scheduler commands that warm resume caches.
- Provision messenger consumers (e.g., via Supervisord) to process background work.
- Review Elasticsearch licensing if enabling premium features; adjust `docker/elasticsearch/config/elasticsearch.yml` accordingly.

## Contribution Guidelines
- Follow PSR-12 and the existing coding standard (enforced via ECS/PHPCS).
- Write tests for every behavioural change. Resume workflows must always validate the `userId` contract.
- Keep documentation (`readme.md`, `docs/api-endpoints.md`) up to date with new endpoints or payload adjustments.

## Troubleshooting & Support
- Run `make logs-php` to inspect API container logs.
- Use `bin/console debug:router` to verify routes, especially when introducing new resume endpoints.
- If migrations fail, confirm the database connection string and ensure the user has DDL privileges.
- For help, contact the Bro World platform team via the internal Slack channel `#bro-world-backend`.
