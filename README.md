# RMS Bronze

Restaurant Management System for the Bronze single-outlet plan.

## Included
- Single tenant/outlet scoped restaurant operations
- Roles: Owner, Cashier, Kitchen
- Menu categories, menu items, modifiers, per-item tax
- Tables, takeaway orders, POS order flow
- KOT and basic KDS
- Cash/card payments, thermal receipt view, PDF receipt
- Basic inventory, recipes/BOM, stock movements, sale deduction
- Daily sales, Z-read, category/item breakdown, payment summary
- Print job queue and print agent API
- Reverb/WebSocket event setup
- Settings, feature flags, audit logs, rate limiting

## Demo Credentials
- Owner: `mwasi5276@gmail.com` / `password`
- Cashier: `cashier@example.com` / `password`
- Kitchen: `kitchen@example.com` / `password`

## Local Setup
```bash
composer install
npm install
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
php artisan serve
```

## QA
```bash
php artisan test
php artisan route:list --except-vendor
php artisan view:cache
php artisan view:clear
```

## Handover Docs
- Bronze build sequence: `docs/bronze-plan.md`
- Client handover guide: `docs/client-handover.md`
- Deployment notes: `docs/deployment-notes.md`
