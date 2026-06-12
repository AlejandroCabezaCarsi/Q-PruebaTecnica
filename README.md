# Qynvo Technical Test - Itinerary API

Laravel API for a multi-tenant travel domain. Agencies authenticate with a bearer token and can only access their own travellers and itineraries.

## Requirements

- PHP 8.3+
- Composer
- SQLite extension enabled

This project was built with Laravel 13.

## Quick Start

From a fresh clone:

```bash
git clone https://github.com/AlejandroCabezaCarsi/Q-PruebaTecnica
cd Q-PruebaTecnica
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Windows PowerShell equivalent:

```powershell
git clone https://github.com/AlejandroCabezaCarsi/Q-PruebaTecnica
Set-Location Q-PruebaTecnica
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

The API will be available at:

```txt
http://127.0.0.1:8000
```

## Setup

Install dependencies:

```bash
composer install
```

Create the environment file and application key if needed:

```bash
cp .env.example .env
php artisan key:generate
```

The default local database is SQLite. The repository expects:

```env
DB_CONNECTION=sqlite
```

When `DB_DATABASE` is not set, Laravel uses:

```txt
database/database.sqlite
```

Create the SQLite file before running migrations if it does not already exist:

```bash
touch database/database.sqlite
```

On Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File -Force
```

Prepare the database with demo data:

```bash
php artisan migrate:fresh --seed
```

Run the API:

```bash
php artisan serve
```

The local API will be available at:

```txt
http://127.0.0.1:8000
```

## Demo Data

The seeder creates two agencies to demonstrate tenant isolation.

Use this token for the main demo agency:

```txt
qynvo-demo-token
```

Use this token for the second demo agency:

```txt
other-agency-token
```

Requests made with one agency token cannot see or update itineraries owned by the other agency.

Example request:

```bash
curl -H "Authorization: Bearer qynvo-demo-token" \
  -H "Accept: application/json" \
  http://127.0.0.1:8000/api/v1/itineraries
```

## Authentication

API routes use a small agency-scoped authentication middleware:

```txt
Authorization: Bearer <agency-token>
```

Tokens are not stored in plain text. The `agencies` table stores `api_token_hash`, and the middleware compares the SHA-256 hash of the bearer token.

Unauthenticated requests return:

```http
401 Unauthorized
```

## Endpoints

For Postman or another API client, these variables are useful:

```txt
base_url = http://127.0.0.1:8000/api/v1
agency_token = qynvo-demo-token
other_agency_token = other-agency-token
```

Use these headers for authenticated requests:

```txt
Accept: application/json
Authorization: Bearer {{agency_token}}
```

### List Itineraries

```http
GET /api/v1/itineraries
```

Optional filters:

```txt
status
traveller_id
from
to
per_page
```

Example:

```bash
curl -H "Authorization: Bearer qynvo-demo-token" \
  -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/v1/itineraries?status=confirmed&per_page=10"
```

### Get Itinerary Detail

```http
GET /api/v1/itineraries/{itinerary}
```

Example:

```bash
curl -H "Authorization: Bearer qynvo-demo-token" \
  -H "Accept: application/json" \
  http://127.0.0.1:8000/api/v1/itineraries/1
```

### Update Itinerary Status

```http
PATCH /api/v1/itineraries/{itinerary}/status
```

Body:

```json
{
  "status": "confirmed"
}
```

Valid statuses:

```txt
draft
confirmed
cancelled
completed
```

PowerShell example:

```powershell
Invoke-RestMethod `
  -Method Patch `
  -Uri "http://127.0.0.1:8000/api/v1/itineraries/1/status" `
  -Headers @{
    Authorization = "Bearer qynvo-demo-token"
    Accept = "application/json"
  } `
  -ContentType "application/json" `
  -Body '{"status":"completed"}'
```

## Architecture

The application is intentionally split into small layers:

- `Models`: Eloquent models, relationships, table/column constants, casts and fillable fields.
- `Requests`: HTTP validation.
- `DTOs`: typed input objects created from validated requests.
- `Actions`: use cases such as listing itineraries or updating status.
- `Queries`: reusable query objects, especially for tenant scoping.
- `Resources`: response serialization.
- `Middleware`: agency authentication.

The controller stays thin: it receives the request, builds the DTO, calls an action, and returns a resource.

## Multi-Tenancy

`Agency` is the tenant. `Traveller` belongs to an agency, and `Itinerary` stores both `agency_id` and `traveller_id`.

Keeping `agency_id` directly on `itineraries` is deliberate:

- every itinerary query can be scoped with a simple `where agency_id = ?`;
- indexes can support common list filters;
- the API does not need joins just to enforce tenant ownership;
- attempts to access another agency's itinerary return `404`, avoiding data leakage.

The database also enforces this tenant boundary with a composite foreign key from
`itineraries(traveller_id, agency_id)` to `travellers(id, agency_id)`. This prevents
an itinerary from being persisted under one agency while pointing at a traveller
owned by another agency, even if the data is inserted outside the API layer.

The main scoping logic lives in:

```txt
app/Queries/Itineraries/ItineraryQuery.php
```

## Validation And Errors

The API uses Laravel form requests and standard JSON error responses:

- `401` when the agency token is missing or invalid.
- `404` when an itinerary does not exist within the authenticated agency.
- `422` when request validation fails.

Examples of validation:

- `status` must be one of the `ItineraryStatus` enum values.
- `traveller_id` filters must belong to the authenticated agency.
- `to` must be greater than or equal to `from`.
- `per_page` is limited between 1 and 100.

## Tests

Run:

```bash
php artisan test
```

The feature tests cover:

- listing only itineraries for the authenticated agency;
- detail access scoped by agency;
- `404` for another agency's itinerary;
- database rejection of itineraries that point to a traveller from another agency;
- filtering by status, traveller and date range;
- rejecting traveller filters from another agency;
- updating itinerary status;
- validating invalid statuses;
- requiring an agency token.

## Troubleshooting

- If SQLite reports that it cannot open the database file, make sure `database/database.sqlite` exists.
- If port `8000` is already in use, start the server on another port:

```bash
php artisan serve --port=8001
```

- If migrations are already marked as run but you want a clean demo database, run:

```bash
php artisan migrate:fresh --seed
```

## What I Would Add With More Time

- Status transition rules, for example `draft -> confirmed -> completed`, and explicit allowed cancellation paths.
- Audit logs for status changes.
- User-level authentication under an agency, instead of agency-token-only authentication.
- OpenAPI documentation and a Postman collection.
- More advanced filters and sorting for itinerary lists.
- Rate limiting per agency.
- Cursor pagination for high-volume mobile feeds.
- Domain events for notifications when an itinerary status changes.

## Mobile Layer Considerations

For the mobile layer, I would keep the API contract explicit and stable. The list endpoint should remain paginated and return compact itinerary cards, while the detail endpoint can return richer nested data. The mobile app should not need to understand tenancy directly; once authenticated, it only receives agency-scoped data. I would also consider offline-friendly caching for upcoming itineraries, optimistic UI for status changes, and API versioning so older app builds continue to work safely as the backend evolves.
