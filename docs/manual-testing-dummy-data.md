# RMS Bronze Manual Testing Dummy Data

Use this as a manual-entry test dataset for the Bronze RMS modules. The app seeder should only create the minimum admin/tenant/outlet records needed to log in; enter the remaining records yourself while testing each screen.

Money fields ending in `_cents` are stored as cents, so `129900` means `1299.00`. Tax fields ending in `_bps` are basis points, so `1600` means `16%`.

## Demo Logins

Only the owner/admin account is seeded. Create the cashier, kitchen, and inactive staff users manually from the staff screen.

| Role | Email | Purpose |
| --- | --- | --- |
| OWNER | mwasi5276@gmail.com | Seeded owner/admin testing |
| CASHIER | cashier@example.com | Manually create for POS, orders, payments |
| KITCHEN | kitchen@example.com | Manually create for Kitchen/KDS testing |

## Tenants

| id | name | slug | plan_code | status | max_outlets | max_pos_devices | max_active_users |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | RMS Bronze Demo Store | rms-bronze-demo | bronze | active | 1 | 2 | 3 |

## Outlets

| id | tenant_id | name | code | phone | email | address | is_active |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | Main Outlet | MAIN | +92 300 0000000 | mwasi5276@gmail.com | Demo Market, Main Road, Lahore | true |

## Tenant Settings

| id | tenant_id | currency | timezone | receipt_header | receipt_footer | default_tax_rate_bps | qr_ordering | delivery | inventory_basic | kds_basic | api_read |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | PKR | Asia/Karachi | RMS Bronze Demo Store | Thank you for dining with us. | 1600 | false | false | true | true | false |

## Outlet Settings

| id | outlet_id | service_charge_enabled | service_charge_bps | notes |
| --- | --- | --- | --- | --- |
| 1 | 1 | false | 0 | No service charge for demo testing. |
| 2 | 1 | true | 500 | Use this variant to test 5% service charge behavior. |

## POS Devices

| id | tenant_id | outlet_id | name | device_uuid | is_active | last_seen_at |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Front Counter POS | 11111111-1111-4111-8111-111111111111 | true | 2026-05-01 18:00:00 |
| 2 | 1 | 1 | Tablet POS | 22222222-2222-4222-8222-222222222222 | true | 2026-05-01 17:50:00 |
| 3 | 1 | 1 | Blocked Extra POS | 33333333-3333-4333-8333-333333333333 | false | null |

## Users

| id | tenant_id | outlet_id | name | email | email_verified_at | password | role | is_active | last_active_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Test Owner | mwasi5276@gmail.com | 2026-05-01 10:00:00 | password | OWNER | true | 2026-05-01 18:05:00 |
| 2 | 1 | 1 | Demo Cashier | cashier@example.com | 2026-05-01 10:00:00 | password | CASHIER | true | 2026-05-01 18:04:00 |
| 3 | 1 | 1 | Demo Kitchen | kitchen@example.com | 2026-05-01 10:00:00 | password | KITCHEN | true | 2026-05-01 17:48:00 |
| 4 | 1 | 1 | Inactive Staff | inactive@example.com | 2026-05-01 10:00:00 | password | CASHIER | false | null |

## Dining Tables

| id | tenant_id | outlet_id | name | code | seats | is_active |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | T-01 | T1 | 2 | true |
| 2 | 1 | 1 | T-02 | T2 | 2 | true |
| 3 | 1 | 1 | T-03 | T3 | 4 | true |
| 4 | 1 | 1 | T-04 | T4 | 4 | true |
| 5 | 1 | 1 | T-05 | T5 | 6 | true |
| 6 | 1 | 1 | Patio-01 | P1 | 4 | false |

## Product Categories

| id | tenant_id | outlet_id | name | slug | sort_order | is_active |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Burgers | burgers | 1 | true |
| 2 | 1 | 1 | Sides | sides | 2 | true |
| 3 | 1 | 1 | Drinks | drinks | 3 | true |
| 4 | 1 | 1 | Desserts | desserts | 4 | true |
| 5 | 1 | 1 | Seasonal Hidden | seasonal-hidden | 99 | false |

## Menu Items

| id | tenant_id | outlet_id | product_category_id | name | sku | description | price_cents | cost_cents | tax_rate_bps | tax_mode | is_active |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 1 | Zinger Burger | MI-ZINGER | Crispy chicken burger with lettuce and sauce. | 129900 | 65000 | 1600 | EXCLUSIVE | true |
| 2 | 1 | 1 | 1 | Beef Smash Burger | MI-SMASH | Double smashed beef patty with cheese. | 159900 | 82000 | 1600 | EXCLUSIVE | true |
| 3 | 1 | 1 | 2 | Loaded Fries | MI-FRIES | Fries with cheese sauce and spicy mayo. | 69900 | 28000 | 1600 | EXCLUSIVE | true |
| 4 | 1 | 1 | 3 | Mint Margarita | MI-MINT | Fresh mint lemon cooler. | 49900 | 15000 | 1600 | INCLUSIVE | true |
| 5 | 1 | 1 | 3 | Soft Drink | MI-SOFT | Chilled canned drink. | 22000 | 9500 | 0 | EXCLUSIVE | true |
| 6 | 1 | 1 | 4 | Molten Lava Cake | MI-LAVA | Warm chocolate lava cake. | 74900 | 31000 | 1600 | EXCLUSIVE | true |
| 7 | 1 | 1 | 5 | Old Seasonal Shake | MI-OLD-SHAKE | Inactive item for visibility tests. | 45000 | 18000 | 1600 | EXCLUSIVE | false |

## Modifier Groups

| id | tenant_id | name | min_select | max_select | is_required | sort_order |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | Burger Add-ons | 0 | 3 | false | 1 |
| 2 | 1 | Sauce Choice | 0 | 2 | false | 2 |
| 3 | 1 | Drink Size | 1 | 1 | true | 3 |
| 4 | 1 | Dessert Extras | 0 | 2 | false | 4 |

## Modifier Options

| id | tenant_id | modifier_group_id | name | price_delta_cents | sort_order | is_active |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Extra Cheese | 15000 | 1 | true |
| 2 | 1 | 1 | Jalapeno | 7500 | 2 | true |
| 3 | 1 | 1 | Extra Patty | 35000 | 3 | true |
| 4 | 1 | 2 | Mayo | 0 | 1 | true |
| 5 | 1 | 2 | Spicy Mayo | 5000 | 2 | true |
| 6 | 1 | 2 | BBQ | 5000 | 3 | true |
| 7 | 1 | 3 | Regular | 0 | 1 | true |
| 8 | 1 | 3 | Large | 10000 | 2 | true |
| 9 | 1 | 4 | Ice Cream Scoop | 12000 | 1 | true |
| 10 | 1 | 4 | Chocolate Drizzle | 8000 | 2 | true |

## Menu Item Modifier Attachments

| id | menu_item_id | modifier_group_id |
| --- | --- | --- |
| 1 | 1 | 1 |
| 2 | 1 | 2 |
| 3 | 2 | 1 |
| 4 | 2 | 2 |
| 5 | 3 | 2 |
| 6 | 4 | 3 |
| 7 | 6 | 4 |

## Stock Items

| id | tenant_id | outlet_id | name | sku | unit | current_stock | reorder_level | is_active |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Burger Buns | STK-BUN | pcs | 80.000 | 20.000 | true |
| 2 | 1 | 1 | Chicken Fillet | STK-CHK | pcs | 42.000 | 15.000 | true |
| 3 | 1 | 1 | Beef Patty | STK-BEEF | pcs | 30.000 | 12.000 | true |
| 4 | 1 | 1 | Potatoes | STK-POT | kg | 18.500 | 8.000 | true |
| 5 | 1 | 1 | Mint Syrup | STK-MNT | ltr | 6.000 | 8.000 | true |
| 6 | 1 | 1 | Cake Mix | STK-CAK | kg | 14.000 | 5.000 | true |
| 7 | 1 | 1 | Old Packaging | STK-OLD-PACK | pcs | 0.000 | 10.000 | false |

## Recipes

| id | tenant_id | menu_item_id | yield_quantity |
| --- | --- | --- | --- |
| 1 | 1 | 1 | 1.000 |
| 2 | 1 | 2 | 1.000 |
| 3 | 1 | 3 | 1.000 |
| 4 | 1 | 4 | 1.000 |
| 5 | 1 | 6 | 1.000 |

## Recipe Items

| id | recipe_id | stock_item_id | quantity |
| --- | --- | --- | --- |
| 1 | 1 | 1 | 1.000 |
| 2 | 1 | 2 | 1.000 |
| 3 | 2 | 1 | 1.000 |
| 4 | 2 | 3 | 2.000 |
| 5 | 3 | 4 | 0.250 |
| 6 | 4 | 5 | 0.150 |
| 7 | 5 | 6 | 0.250 |

## Stock Movements

| id | tenant_id | outlet_id | stock_item_id | user_id | type | quantity | unit_cost_cents | reference_type | reference_id | notes | occurred_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 1 | 1 | IN | 100.000 | 5500 | null | null | Opening stock | 2026-05-01 09:00:00 |
| 2 | 1 | 1 | 2 | 1 | IN | 50.000 | 28000 | null | null | Opening stock | 2026-05-01 09:00:00 |
| 3 | 1 | 1 | 5 | 1 | IN | 10.000 | 12000 | null | null | Supplier purchase | 2026-05-01 09:30:00 |
| 4 | 1 | 1 | 5 | 2 | OUT | 1.000 | null | null | null | Spillage test | 2026-05-01 13:00:00 |
| 5 | 1 | 1 | 1 | 2 | SALE | 2.000 | null | App\\Models\\Order | 6 | Auto-deduct paid sale | 2026-05-01 16:15:00 |
| 6 | 1 | 1 | 4 | 1 | ADJUST | -0.500 | null | null | null | Manual count correction | 2026-05-01 17:00:00 |

## Orders

| id | tenant_id | outlet_id | dining_table_id | user_id | order_number | order_type | status | guest_count | subtotal_cents | discount_cents | tax_cents | total_cents | notes | sent_to_kitchen_at | paid_at | voided_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 1 | 2 | ORD-DEMO-1001 | DINE_IN | OPEN | 2 | 129900 | 0 | 20784 | 150684 | Open table order for edit tests. | null | null | null |
| 2 | 1 | 1 | 2 | 2 | ORD-DEMO-1002 | DINE_IN | SENT_TO_KITCHEN | 3 | 199800 | 0 | 31968 | 231768 | Sent to kitchen, not ready yet. | 2026-05-01 17:10:00 | null | null |
| 3 | 1 | 1 | 3 | 2 | ORD-DEMO-1003 | DINE_IN | READY | 2 | 69900 | 0 | 11184 | 81084 | Ready for serving. | 2026-05-01 17:05:00 | null | null |
| 4 | 1 | 1 | 4 | 2 | ORD-DEMO-1004 | DINE_IN | SERVED | 4 | 234800 | 10000 | 37568 | 262368 | Served but unpaid. | 2026-05-01 16:45:00 | null | null |
| 5 | 1 | 1 | null | 2 | ORD-DEMO-1005 | TAKEAWAY | VOIDED | 1 | 49900 | 0 | 7984 | 57884 | Customer cancelled takeaway. | null | null | 2026-05-01 16:00:00 |
| 6 | 1 | 1 | null | 2 | ORD-DEMO-1006 | TAKEAWAY | PAID | 1 | 204800 | 20000 | 32768 | 217568 | Paid cash order with discount. | 2026-05-01 15:50:00 | 2026-05-01 16:15:00 | null |
| 7 | 1 | 1 | 5 | 2 | ORD-DEMO-1007 | DINE_IN | PAID | 5 | 309700 | 0 | 49552 | 359252 | Paid card dine-in order. | 2026-05-01 14:20:00 | 2026-05-01 15:05:00 | null |

## Order Items

| id | order_id | menu_item_id | item_name | sku | quantity | unit_price_cents | tax_rate_bps | tax_mode | line_subtotal_cents | line_tax_cents | line_total_cents | notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | Zinger Burger | MI-ZINGER | 1.00 | 129900 | 1600 | EXCLUSIVE | 129900 | 20784 | 150684 | No onions |
| 2 | 2 | 2 | Beef Smash Burger | MI-SMASH | 1.00 | 159900 | 1600 | EXCLUSIVE | 159900 | 25584 | 185484 | Well done |
| 3 | 2 | 4 | Mint Margarita | MI-MINT | 1.00 | 49900 | 1600 | INCLUSIVE | 49900 | 6883 | 49900 | Large |
| 4 | 3 | 3 | Loaded Fries | MI-FRIES | 1.00 | 69900 | 1600 | EXCLUSIVE | 69900 | 11184 | 81084 | Extra spicy |
| 5 | 4 | 1 | Zinger Burger | MI-ZINGER | 1.00 | 129900 | 1600 | EXCLUSIVE | 129900 | 20784 | 150684 | null |
| 6 | 4 | 6 | Molten Lava Cake | MI-LAVA | 1.00 | 74900 | 1600 | EXCLUSIVE | 74900 | 11984 | 86884 | Serve warm |
| 7 | 4 | 3 | Loaded Fries | MI-FRIES | 1.00 | 69900 | 1600 | EXCLUSIVE | 69900 | 11184 | 81084 | null |
| 8 | 5 | 4 | Mint Margarita | MI-MINT | 1.00 | 49900 | 1600 | INCLUSIVE | 49900 | 6883 | 49900 | Voided item |
| 9 | 6 | 1 | Zinger Burger | MI-ZINGER | 1.00 | 129900 | 1600 | EXCLUSIVE | 129900 | 20784 | 150684 | Paid cash |
| 10 | 6 | 3 | Loaded Fries | MI-FRIES | 1.00 | 69900 | 1600 | EXCLUSIVE | 69900 | 11184 | 81084 | Paid cash |
| 11 | 7 | 2 | Beef Smash Burger | MI-SMASH | 1.00 | 159900 | 1600 | EXCLUSIVE | 159900 | 25584 | 185484 | null |
| 12 | 7 | 6 | Molten Lava Cake | MI-LAVA | 2.00 | 74900 | 1600 | EXCLUSIVE | 149800 | 23968 | 173768 | Two desserts |

## Order Item Modifiers

| id | order_item_id | modifier_group_name | modifier_option_name | price_delta_cents | quantity |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | Burger Add-ons | Extra Cheese | 15000 | 1.00 |
| 2 | 1 | Sauce Choice | Spicy Mayo | 5000 | 1.00 |
| 3 | 2 | Burger Add-ons | Extra Patty | 35000 | 1.00 |
| 4 | 3 | Drink Size | Large | 10000 | 1.00 |
| 5 | 4 | Sauce Choice | BBQ | 5000 | 1.00 |
| 6 | 12 | Dessert Extras | Ice Cream Scoop | 12000 | 2.00 |

## Kitchen Tickets

| id | tenant_id | outlet_id | order_id | status | fired_at | ready_at | served_at | notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 2 | PREPARING | 2026-05-01 17:10:00 | null | null | Preparing active KOT. |
| 2 | 1 | 1 | 3 | READY | 2026-05-01 17:05:00 | 2026-05-01 17:20:00 | null | Ready ticket. |
| 3 | 1 | 1 | 4 | SERVED | 2026-05-01 16:45:00 | 2026-05-01 17:00:00 | 2026-05-01 17:08:00 | Served ticket. |
| 4 | 1 | 1 | 7 | SERVED | 2026-05-01 14:20:00 | 2026-05-01 14:45:00 | 2026-05-01 14:55:00 | Paid served ticket. |

## Payments

| id | tenant_id | outlet_id | order_id | user_id | method | amount_cents | reference | paid_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 6 | 2 | CASH | 217568 | CASH-ORD-DEMO-1006 | 2026-05-01 16:15:00 |
| 2 | 1 | 1 | 7 | 2 | CARD | 359252 | CARD-APPROVAL-778899 | 2026-05-01 15:05:00 |

## Print Jobs

| id | tenant_id | outlet_id | order_id | kitchen_ticket_id | requested_by_user_id | type | channel | status | copies | payload | printed_at | failed_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | 2 | 1 | 2 | KOT | agent | PENDING | 1 | {"order_number":"ORD-DEMO-1002"} | null | null |
| 2 | 1 | 1 | 3 | 2 | 2 | KOT | agent | PROCESSING | 1 | {"order_number":"ORD-DEMO-1003"} | null | null |
| 3 | 1 | 1 | 6 | null | 2 | RECEIPT | agent | COMPLETED | 1 | {"order_number":"ORD-DEMO-1006","paid":true} | 2026-05-01 16:16:00 | null |
| 4 | 1 | 1 | 7 | null | 2 | RECEIPT | agent | FAILED | 2 | {"order_number":"ORD-DEMO-1007","reason":"printer_offline"} | null | 2026-05-01 15:06:00 |

## Audit Logs

| id | tenant_id | user_id | auditable_type | auditable_id | event | old_values | new_values | ip_address | user_agent | created_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 1 | App\\Models\\MenuItem | 1 | menu_item.updated | {"price_cents":119900} | {"price_cents":129900} | 127.0.0.1 | Manual QA Browser | 2026-05-01 11:00:00 |
| 2 | 1 | 2 | App\\Models\\Order | 5 | order.voided | {"status":"OPEN"} | {"status":"VOIDED"} | 127.0.0.1 | Manual QA Browser | 2026-05-01 16:00:00 |
| 3 | 1 | 2 | App\\Models\\Payment | 1 | payment.created | null | {"method":"CASH","amount_cents":217568} | 127.0.0.1 | Manual QA Browser | 2026-05-01 16:15:00 |
| 4 | 1 | 1 | App\\Models\\StockMovement | 6 | stock.adjusted | {"current_stock":"19.000"} | {"current_stock":"18.500"} | 127.0.0.1 | Manual QA Browser | 2026-05-01 17:00:00 |

## Manual Test Scenarios Covered

| Feature | Records to use |
| --- | --- |
| Login and role access | Owner, cashier, kitchen, inactive staff |
| Bronze limits | 1 outlet, 2 active POS devices, 3 active users |
| Catalog CRUD | Active and inactive categories/items |
| Modifiers | Burger, sauce, drink size, dessert extras |
| Tax calculation | EXCLUSIVE, INCLUSIVE, and 0% tax items |
| Tables | Available, occupied, inactive table |
| Orders | OPEN, SENT_TO_KITCHEN, READY, SERVED, VOIDED, PAID |
| Kitchen/KDS | PREPARING, READY, SERVED tickets |
| Payments | CASH and CARD |
| Receipts/printing | PENDING, PROCESSING, COMPLETED, FAILED print jobs |
| Inventory | IN, OUT, SALE, ADJUST movements plus low-stock item |
| Reports | Paid cash/card orders and category/item spread |
| Audit | Menu change, void, payment, stock adjustment |
