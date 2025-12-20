# NuxGame

Simple PHP 8.2 registration and lucky-link application. No heavy framework, only PDO and a handful of small helpers.

## Prerequisites
- Docker + Docker Compose
- Make sure ports **8080** (app) and **33060** (MySQL forwarded port) are free on your host.

## Setup & Run
1. Copy environment template and adjust if needed:
   ```bash
   cp .env.example .env
   ```
2. Build and start the stack:
   ```bash
   docker compose up --build -d
   ```
3. Initialize the database schema (run once):
   ```bash
   docker compose exec php php scripts/migrate.php
   ```
4. Open the app at [http://localhost:8080](http://localhost:8080).

### Stopping the stack
```bash
docker compose down
```

## Database schema
Schema lives in `src/Database/schema.sql` and is applied by `scripts/migrate.php`.
Tables:
- `users`: username + phone unique pair, created_at
- `links`: token, is_active, expires_at, replaced_by_link_id (old link points to new), created_at
- `attempts`: lucky attempts history with number/result/amount

## Application behavior
- Registration reuses the user record when username + phone already exist, but **deactivates previous active links** for that user and issues a new one with a 7‑day validity window.
- Tokens are generated with `random_bytes` (64 hex chars). Links become invalid immediately when regenerated or deactivated.
- Expired/deactivated/invalid tokens return HTTP 404 with a clear message.
- Pages:
  - **Home (`GET /`)**: registration form. On success shows the generated link.
  - **Page A (`GET /link/{token}`)**: link controls + AJAX buttons (regenerate, deactivate, Imfeelinglucky, history).
- Lucky rules: random int 1..1000. Even = win else lose. Win amount tiers: >900 → 70%, >600 → 50%, >300 → 30%, otherwise 10%. Each attempt is stored with user/link relation.

## Endpoints
- `GET /` – registration form.
- `POST /register` – validate + create user/link.
- `GET /link/{token}` – Page A, available only for active + non-expired tokens.
- `POST /link/{token}/regen` – regenerate token (old deactivated, new 7-day window).
- `POST /link/{token}/deactivate` – mark link inactive.
- `POST /link/{token}/lucky` – Imfeelinglucky; returns JSON `{number, result, amount, created_at}`.
- `GET /link/{token}/history` – last 3 attempts for the user.

## Decisions & notes
- Pure PHP with manual dependency wiring; no Composer dependencies are required.
- Error handling returns HTML for pages and JSON for AJAX endpoints with meaningful HTTP status codes.
- Output is escaped with `htmlspecialchars` helpers; all queries use prepared PDO statements.
- Only one active link per user at any time (older ones deactivated on new registration or regeneration).

## Docker details
- Nginx serves `public/` (fastcgi to php-fpm).
- MySQL credentials come from `.env` (`DB_NAME`, `DB_USER`, `DB_PASS`, `DB_HOST`, `DB_PORT`). Default MySQL port is exposed as `33060` on the host.

Enjoy!
