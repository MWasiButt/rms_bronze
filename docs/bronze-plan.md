# Bronze Plan Build Sequence

## Phase 1: Foundation
- Tenants, outlets, tenant settings, outlet settings, POS devices
- User tenant/outlet ownership and role support
- Feature-flag defaults for Bronze plan

## Phase 2: Auth and Access
- Laravel Sanctum setup
- Tenant scoping middleware
- Role-based permissions for OWNER, CASHIER, KITCHEN
- Active user and device limit enforcement

## Phase 3: Catalog
- Product categories
- Menu items
- Modifier groups and options
- Item tax configuration and inclusive/exclusive tax handling

## Phase 4: Service and Orders
- Dining tables
- Order creation for dine-in and takeaway
- Order items, modifiers, discounts, totals
- Order lifecycle transitions

## Phase 5: Kitchen
- Kitchen tickets
- Basic KDS screen
- Kitchen real-time events and status updates

## Phase 6: Payments and Receipts
- Cash and card payments
- Thermal and PDF receipts
- Order payment settlement and print queue hooks

## Phase 7: Inventory
- Stock items
- Recipes / bill of materials
- Stock movement ledger and sale deduction flow

## Phase 8: Printing and Queues
- Print jobs
- Redis/Horizon integration
- Print agent integration
- Menu caching

## Phase 9: Reporting
- Daily sales
- Z-read
- Category and item breakdown
- Payment method summary

## Phase 10: Governance
- Audit logs
- Rate limiting
- Money-in-cents validation rules
- Soft delete management

## Frontend Delivery Order
- Auth shell and tenant-aware dashboard
- Catalog screens
- Table and POS order-taking screens
- Kitchen/KDS view
- Payments and receipt history
- Inventory screens
- Reporting screens
- Settings and governance screens
