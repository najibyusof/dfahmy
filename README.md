# DFahMy Eco Resort - Homestay Management System

Laravel 13 application for homestay operations: rooms, bookings, guests, payments, housekeeping, notifications, reporting, and audit logs.

## Repository

- Primary remote: `https://github.com/najibyusof/dfahmy.git`
- Default branch: `main`

Quick Git workflow:

```bash
git init
git branch -M main
git remote add origin https://github.com/najibyusof/dfahmy.git
git add -A
git commit -m "chore: initial project import"
git push -u origin main
```

## Requirements

- PHP 8.3+
- Composer 2+
- Node.js 20+ and npm
- MySQL 8+ (recommended for production)

## Quick Start

1. Install dependencies:

```bash
composer install
npm install
```

2. Environment setup:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure `.env`:

- `APP_NAME`, `APP_ENV`, `APP_URL`
- `APP_DEBUG=false` in production
- Database (`DB_*`)
- Queue (`QUEUE_CONNECTION`, `DB_QUEUE_*`, `QUEUE_FAILED_*`)
- Mail (`MAIL_*`)
- Telegram (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`)
- Session cookie security in production:
    - `SESSION_SECURE_COOKIE=true`
    - `SESSION_SAME_SITE=lax`

4. Database migrate + seed:

```bash
php artisan migrate --seed
```

5. Build frontend assets:

```bash
npm run build
```

6. Run locally:

```bash
php artisan serve
```

## Environment Variables

Use `.env.example` as the canonical reference. Key groups:

- App: `APP_ENV`, `APP_DEBUG`, `APP_URL`
- DB: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Session/Security: `SESSION_DRIVER`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`
- Queue: `QUEUE_CONNECTION`, `DB_QUEUE_CONNECTION`, `DB_QUEUE_TABLE`, `DB_QUEUE_RETRY_AFTER`
- Failed jobs: `QUEUE_FAILED_DRIVER`, `QUEUE_FAILED_DATABASE`
- Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
- Telegram: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`
- Health probes: `HEALTH_CHECK_TOKEN`
- Bootstrap admin user: `SUPER_ADMIN_NAME`, `SUPER_ADMIN_EMAIL`, `SUPER_ADMIN_PASSWORD`

## Telegram Alert Setup

Use this when configuring Telegram alerts for admins on `/admin/telegram-alert-settings`.

1. Open Telegram and search for `@BotFather`.
2. Run `/newbot`, complete the bot name and username prompts, then copy the bot token returned by BotFather.
3. Save the token in `.env`:

```bash
TELEGRAM_BOT_TOKEN=your_bot_token
```

4. Open a direct chat with the bot, or add the bot to the target Telegram group.
5. Send at least one message to that chat, for example `/start`, so Telegram creates an update record.
6. Get the chat ID by opening the following URL in a browser, replacing `<YOUR_BOT_TOKEN>` with the real token:

```text
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
```

7. Find the latest message in the JSON response and copy `chat.id` from the target private chat or group.
8. Save the chat ID in `.env`:

```bash
TELEGRAM_CHAT_ID=your_chat_id
```

Do not use the bot's own Telegram ID. Telegram will reject sends to the bot itself with `403 Forbidden: the bot can't send messages to the bot`.

9. Generate a long random secret for operational health endpoints and save it in `.env`:

```bash
HEALTH_CHECK_TOKEN=your_random_secret
```

10. After editing `.env`, reload Laravel configuration:

```bash
php artisan optimize:clear
```

11. Open `/admin/telegram-alert-settings` and use `Send Test Telegram` to confirm the bot can deliver messages.

## Database Commands

- Fresh setup:

```bash
php artisan migrate:fresh --seed
```

- Normal incremental migration:

```bash
php artisan migrate
```

- Seed only:

```bash
php artisan db:seed
```

## Queue and Failed Jobs

This app uses queued notifications and Telegram jobs.

1. Start worker:

```bash
php artisan queue:work --tries=3 --backoff=30
```

2. Monitor failed jobs:

```bash
php artisan queue:failed
```

3. Retry failed jobs:

```bash
php artisan queue:retry all
```

4. Clear failed jobs (careful):

```bash
php artisan queue:flush
```

Production note: queue connections are configured with `after_commit=true` so jobs only dispatch after successful DB commits.

## Scheduler

The app schedules operational reminders:

- `system:heartbeat` every minute
- `housekeeping:notify-overdue` at 07:00
- `bookings:send-upcoming-checkin-reminders` at 09:00
- `bookings:send-outstanding-balance-telegram-alerts` at 10:00

Admin users with `users.manage` permission can view runtime health indicators at:

- `/admin/operations-health`

Public/system health endpoints:

- `/healthz`: unauthenticated basic liveness probe
- `/readyz`: unauthenticated readiness probe (DB + queue tables)
- `/healthz/ops`: token-protected operational probe
    - pass header `X-Health-Token: <HEALTH_CHECK_TOKEN>`
    - returns queue and scheduler metrics
- `/readyz/ops`: token-protected readiness metrics probe
    - pass header `X-Health-Token: <HEALTH_CHECK_TOKEN>`
    - returns per-dependency readiness (`database`, `jobs_table`, `failed_jobs_table`) and `latency_ms`

Monitoring curl examples:

```bash
# Basic liveness (expect HTTP 200)
curl -sS -o /tmp/healthz.json -w "%{http_code}\n" "https://your-domain/healthz"

# Basic readiness (expect HTTP 200 when dependencies are ready, else 503)
curl -sS -o /tmp/readyz.json -w "%{http_code}\n" "https://your-domain/readyz"

# Ops liveness metrics (expect HTTP 200 with valid token, 401 invalid token, 404 when token not configured)
curl -sS -o /tmp/healthz-ops.json -w "%{http_code}\n" \
  -H "X-Health-Token: ${HEALTH_CHECK_TOKEN}" \
  "https://your-domain/healthz/ops"

# Ops readiness metrics with latency (expect HTTP 200 when ready, else 503)
curl -sS -o /tmp/readyz-ops.json -w "%{http_code}\n" \
  -H "X-Health-Token: ${HEALTH_CHECK_TOKEN}" \
  "https://your-domain/readyz/ops"
```

Probe policy recommendation:

- Use `/healthz` for container liveness checks.
- Use `/readyz` for load balancer readiness checks.
- Use `/healthz/ops` and `/readyz/ops` for internal monitoring only.
- Never expose `HEALTH_CHECK_TOKEN` in frontend code or public logs.

Configure cron (Linux/macOS):

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Optional long-running scheduler:

```bash
php artisan schedule:work
```

## Test Commands

- Full suite:

```bash
php artisan test
```

- Focused suites:

```bash
php artisan test --filter=BookingModuleTest
php artisan test --filter=PaymentModuleTest
php artisan test --filter=HousekeepingModuleTest
php artisan test --filter=OperationalInAppNotificationsTest
php artisan test --filter=GuestEmailNotificationTest
php artisan test --filter=TelegramAlertInfrastructureTest
```

## Deployment Checklist

1. Set production environment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` set

2. Set secure secrets:

- DB credentials
- Mail credentials
- Telegram token/chat id
- Never commit `.env`

3. Optimize framework:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Run migrations safely:

```bash
php artisan migrate --force
```

5. Ensure queue worker is supervised (systemd/supervisor) and restarted on deploy.

6. Ensure scheduler cron is installed on one server in the cluster.

7. Verify filesystem permissions for `storage` and `bootstrap/cache`.

8. Validate health endpoints and core flows:

- Login/logout
- Booking create/check-in/check-out
- Payment create/refund/void
- Housekeeping task updates
- Report CSV export
- Audit log access by Manager/Super Admin only

9. Observe logs after deployment for queue failures and notification delivery issues.
10. Verify operations dashboard updates heartbeat and queue stats:
    - run `php artisan system:heartbeat`
    - open `/admin/operations-health`

## Security Notes

- Route protection uses role/permission middleware and policy checks.
- Mutation and export endpoints are throttled.
- Audit logs redact sensitive values (`password`, `token`, `secret`, etc.).
- CSRF protection is provided by Laravel `web` middleware for all form actions.
