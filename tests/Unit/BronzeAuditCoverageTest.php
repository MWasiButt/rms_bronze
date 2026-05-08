<?php

namespace Tests\Unit;

use Tests\TestCase;

class BronzeAuditCoverageTest extends TestCase
{
    public function test_required_bronze_write_controllers_record_audit_events(): void
    {
        $expectedEvents = [
            app_path('Http/Controllers/SettingsController.php') => ['settings.updated'],
            app_path('Http/Controllers/MenuCategoryController.php') => [
                'menu.category.created',
                'menu.category.updated',
                'menu.category.archived',
                'menu.category.restored',
            ],
            app_path('Http/Controllers/MenuItemController.php') => [
                'menu.item.created',
                'menu.item.updated',
                'menu.item.archived',
                'menu.item.restored',
            ],
            app_path('Http/Controllers/StockItemController.php') => [
                'stock.item.created',
                'stock.item.updated',
                'stock.item.archived',
                'stock.item.restored',
                'stock.movement.created',
            ],
            app_path('Http/Controllers/RecipeController.php') => ['stock.recipe.updated'],
            app_path('Http/Controllers/StaffController.php') => [
                'staff.created',
                'staff.updated',
                'staff.status.updated',
                'staff.password_reset.requested',
            ],
            app_path('Http/Controllers/DiningTableController.php') => [
                'table.created',
                'table.updated',
                'table.deleted',
            ],
        ];

        foreach ($expectedEvents as $path => $events) {
            $contents = file_get_contents($path);

            foreach ($events as $event) {
                $this->assertStringContainsString($event, $contents, $event.' is missing from '.$path);
            }
        }
    }
}
