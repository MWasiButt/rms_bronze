# RMS Bronze Client Handover

## Demo Credentials
- Owner: `mwasi5276@gmail.com` / `password`
- Cashier: `cashier@example.com` / `password`
- Kitchen: `kitchen@example.com` / `password`

## QA Checklist
- Login works for Owner, Cashier, and Kitchen.
- Owner can manage staff, menu, categories, modifiers, tables, inventory, recipes, reports, and settings.
- Cashier can create dine-in/takeaway orders, add items, send orders to kitchen, take payment, and view receipts.
- Kitchen can see KDS tickets and update ticket status from Pending to Preparing to Ready to Served.
- Bronze limits are enforced: one outlet, two POS devices, three active users.
- Tenant records cannot be opened from another tenant account.
- Receipt thermal view and PDF view open for paid orders.
- Payment creates a receipt print job.
- KOT print job is created when an order is sent to kitchen.
- Recipe stock is deducted when an order is paid.
- Sales report shows daily sales, Z-read totals, category/item breakdown, and payment method summary.

## User Guide

### Add a Menu Item
1. Login as Owner.
2. Open Menu & Categories.
3. Create or select a category.
4. Open Menu Items, then create an item.
5. Enter name, SKU, price, cost, tax rate, tax mode, and optional modifier groups.
6. Save the item.

### Create an Order
1. Login as Owner or Cashier.
2. Open POS & Orders, then POS Screen.
3. Select takeaway or choose a dining table.
4. Add menu items and modifiers.
5. Adjust quantity or discount if needed.

### Send to Kitchen
1. Open the active order on the POS screen.
2. Click the action to send it to kitchen.
3. Confirm the KOT appears on the Kitchen/KDS screen.

### Complete a Kitchen Ticket
1. Login as Kitchen.
2. Open Kitchen / KDS.
3. Move the ticket through Preparing, Ready, and Served.
4. Confirm the order status updates in Orders Queue.

### Take Payment
1. Login as Owner or Cashier.
2. Open the served order.
3. Select Cash or Card.
4. Enter the exact remaining amount.
5. Submit payment.

### Print Receipt
1. After payment, open the receipt page.
2. Use the thermal receipt view for browser printing.
3. Use the PDF receipt link when an A4/downloadable receipt is needed.
4. Check Print Queue for receipt print jobs.

### View Reports
1. Login as Owner.
2. Open Reports.
3. Select the date range.
4. Review daily sales, Z-read totals, category/item breakdown, and payment method summary.

## Handover Notes
- Demo data is created by `php artisan db:seed --class=DatabaseSeeder`.
- Automated QA is covered by `php artisan test`.
- Real-time updates require the frontend assets and Reverb/WebSocket service to be configured on the deployment server.
- Horizon requires a Linux-compatible production environment with Redis and PHP process-control extensions.
