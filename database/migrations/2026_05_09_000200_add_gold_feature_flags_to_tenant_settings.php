<?php

use App\Support\PlanALimits;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->boolean('inventory_advanced')->default(false)->after('inventory_basic');
            $table->boolean('kds_pro')->default(false)->after('kds_basic');
            $table->boolean('coupons')->default(false)->after('kds_pro');
            $table->boolean('loyalty_basic')->default(false)->after('coupons');
            $table->boolean('webhooks')->default(false)->after('api_read');
            $table->boolean('multi_outlet')->default(false)->after('webhooks');
            $table->boolean('commissary')->default(false)->after('multi_outlet');
            $table->boolean('analytics_advanced')->default(false)->after('commissary');
            $table->boolean('role_permissions_advanced')->default(false)->after('analytics_advanced');
            $table->boolean('shift_reports')->default(false)->after('role_permissions_advanced');
        });

        $goldTenantIds = DB::table('tenants')
            ->where('plan_code', PlanALimits::PLAN_GOLD)
            ->pluck('id');

        if ($goldTenantIds->isNotEmpty()) {
            DB::table('tenant_settings')
                ->whereIn('tenant_id', $goldTenantIds)
                ->update(PlanALimits::featureFlags(PlanALimits::PLAN_GOLD));
        }
    }

    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'inventory_advanced',
                'kds_pro',
                'coupons',
                'loyalty_basic',
                'webhooks',
                'multi_outlet',
                'commissary',
                'analytics_advanced',
                'role_permissions_advanced',
                'shift_reports',
            ]);
        });
    }
};
