<?php

return [
    'roles' => [
        'OWNER' => [
            '*',
        ],

        'CASHIER' => [
            'orders.manage',
            'orders.view',
            'tables.manage',
            'print.manage',
            'reports.sales.view',
        ],

        'KITCHEN' => [
            'kitchen.manage',
            'orders.view',
        ],
    ],

    'known' => [
        'orders.manage',
        'orders.view',
        'tables.manage',
        'kitchen.manage',
        'catalog.manage',
        'inventory.manage',
        'inventory.adjust',
        'recipes.manage',
        'reports.sales.view',
        'reports.view_cost',
        'print.manage',
        'staff.manage',
        'settings.manage',
        'coupons.manage',
        'loyalty.manage',
    ],
];
