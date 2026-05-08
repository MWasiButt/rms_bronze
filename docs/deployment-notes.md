# RMS Bronze Deployment Notes

## Server Requirements
- PHP 8.2 or newer
- PHP extensions required by Laravel plus `pdo_sqlite` for the automated SQLite test suite
- Composer
- Node.js and npm
- MySQL or MariaDB
- Redis for queues, cache, Horizon, and real-time workloads
- Web server pointing to Laravel `public`

## Environment
Set production values in `.env`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- database credentials
- mail credentials for password reset
- Redis credentials
- `QUEUE_CONNECTION=redis`
- `REDIS_QUEUE_CONNECTION=default`
- `REDIS_QUEUE=default`
- broadcast/Reverb credentials

## Install Steps
1. `composer install --no-dev --optimize-autoloader`
2. `npm install`
3. `npm run production`
4. `php artisan key:generate`
5. `php artisan migrate --force`
6. `php artisan db:seed --class=DatabaseSeeder` only if demo data is required
7. `php artisan config:cache`
8. `php artisan route:cache`
9. `php artisan view:cache`
10. Start Horizon with a process manager: `php artisan horizon`.
11. Start Reverb/WebSocket service if live POS/KDS/print updates are enabled.

## Redis and Horizon Runtime Verification
- Run `php artisan horizon:status` and confirm Horizon is active.
- Run `php artisan queue:monitor redis:printing,redis:default --max=60` to verify both Bronze runtime queues are visible.
- Open `/horizon` as an authenticated Owner and confirm the `supervisor-printing` supervisor is running.
- Confirm `supervisor-printing` is consuming the `printing` and `default` queues.
- Confirm the production supervisor/process manager restarts Horizon after deploys and server reboots.
- After each deploy, run `php artisan horizon:terminate` so Horizon restarts with the latest cached code.

## Post-Deploy Checks
- Login as Owner, Cashier, and Kitchen.
- Create a test takeaway order.
- Send it to kitchen and update the KDS ticket.
- Take payment and open receipt/PDF.
- Confirm stock deduction on a recipe-linked item.
- Confirm reports show the paid test order.
- Confirm print jobs appear in Print Queue.
- Create a Sanctum token for the print agent user.
- Call `GET /api/print-agent/jobs/next?type=RECEIPT` and confirm the response includes the order number, line items, payments, and `PROCESSING` status.
- Call `PATCH /api/print-agent/jobs/{id}` with `{"status":"COMPLETED"}` and confirm the job stores `printed_at`.
- Repeat `GET /api/print-agent/jobs/next?type=KOT` and confirm the response includes order items and kitchen ticket status.

## Known Production Note
Laravel Horizon could not be installed locally on Windows because the required `pcntl` and `posix` PHP extensions are not available on Windows. Install and run Horizon on the Linux production server.
