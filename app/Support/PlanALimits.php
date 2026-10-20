<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

class PlanALimits
{
    public const PLAN_BRONZE = 'bronze';
    public const PLAN_GOLD = 'gold';

    public const MAX_OUTLETS = 1;
    public const MAX_POS_DEVICES = 2;
    public const MAX_ACTIVE_USERS = 3;

    public const GOLD_MAX_OUTLETS = 1;
    public const GOLD_MAX_POS_DEVICES = 5;
    public const GOLD_MAX_ACTIVE_USERS = 10;

    /**
     * @return array{max_outlets: int, max_pos_devices: int, max_active_users: int}
     */
    public static function defaults(string $planCode = self::PLAN_BRONZE): array
    {
        return match (self::normalizePlan($planCode)) {
            self::PLAN_GOLD => [
                'max_outlets' => self::GOLD_MAX_OUTLETS,
                'max_pos_devices' => self::GOLD_MAX_POS_DEVICES,
                'max_active_users' => self::GOLD_MAX_ACTIVE_USERS,
            ],
            default => [
                'max_outlets' => self::MAX_OUTLETS,
                'max_pos_devices' => self::MAX_POS_DEVICES,
                'max_active_users' => self::MAX_ACTIVE_USERS,
            ],
        };
    }

    /**
     * @return array<string, bool>
     */
    public static function featureFlags(string $planCode = self::PLAN_BRONZE): array
    {
        return match (self::normalizePlan($planCode)) {
            self::PLAN_GOLD => [
                'qr_ordering' => true,
                'delivery' => false,
                'inventory_basic' => false,
                'inventory_advanced' => true,
                'kds_basic' => true,
                'kds_pro' => false,
                'coupons' => true,
                'loyalty_basic' => true,
                'api_read' => false,
                'webhooks' => false,
                'multi_outlet' => false,
                'commissary' => false,
                'analytics_advanced' => true,
                'role_permissions_advanced' => true,
                'shift_reports' => true,
            ],
            default => [
                'qr_ordering' => false,
                'delivery' => false,
                'inventory_basic' => true,
                'inventory_advanced' => false,
                'kds_basic' => true,
                'kds_pro' => false,
                'coupons' => false,
                'loyalty_basic' => false,
                'api_read' => false,
                'webhooks' => false,
                'multi_outlet' => false,
                'commissary' => false,
                'analytics_advanced' => false,
                'role_permissions_advanced' => false,
                'shift_reports' => false,
            ],
        };
    }

    public function assertTenantWithinLimits(Tenant $tenant): void
    {
        $this->applyMissingDefaults($tenant);

        $tenant->loadCount([
            'outlets as active_outlets_count' => fn ($query) => $query->where('is_active', true),
            'posDevices as active_pos_devices_count' => fn ($query) => $query->where('is_active', true),
            'users as active_users_count' => fn ($query) => $query->where('is_active', true),
        ]);

        abort_if(
            $tenant->active_outlets_count > $tenant->max_outlets,
            403,
            $this->limitMessage($tenant, 'outlet', (int) $tenant->max_outlets)
        );

        abort_if(
            $tenant->active_pos_devices_count > $tenant->max_pos_devices,
            403,
            $this->limitMessage($tenant, 'POS device', (int) $tenant->max_pos_devices)
        );

        abort_if(
            $tenant->active_users_count > $tenant->max_active_users,
            403,
            $this->limitMessage($tenant, 'active staff account', (int) $tenant->max_active_users)
        );
    }

    public function assertCanAddOutlet(Tenant $tenant): void
    {
        $this->applyMissingDefaults($tenant);

        $this->assertCanAdd($tenant, 'outlets', 'max_outlets', $this->limitMessage($tenant, 'outlet', (int) $tenant->max_outlets));
    }

    public function assertCanAddPosDevice(Tenant $tenant): void
    {
        $this->applyMissingDefaults($tenant);

        $this->assertCanAdd($tenant, 'posDevices', 'max_pos_devices', $this->limitMessage($tenant, 'POS device', (int) $tenant->max_pos_devices));
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        $this->applyMissingDefaults($tenant);

        $this->assertCanAdd($tenant, 'users', 'max_active_users', $this->limitMessage($tenant, 'active staff account', (int) $tenant->max_active_users));
    }

    private function assertCanAdd(Tenant $tenant, string $relation, string $limitColumn, string $message): void
    {
        $this->applyMissingDefaults($tenant);

        $activeCount = $tenant->{$relation}()
            ->where('is_active', true)
            ->count();

        if ($activeCount >= $tenant->{$limitColumn}) {
            throw ValidationException::withMessages([
                'plan' => $message,
            ]);
        }
    }

    private static function normalizePlan(?string $planCode): string
    {
        return strtolower((string) ($planCode ?: self::PLAN_BRONZE));
    }

    private function applyMissingDefaults(Tenant $tenant): void
    {
        $defaults = self::defaults($tenant->plan_code);

        foreach ($defaults as $column => $value) {
            if ($tenant->{$column} === null) {
                $tenant->forceFill([$column => $value]);
            }
        }
    }

    private function limitMessage(Tenant $tenant, string $label, int $limit): string
    {
        $plan = str(self::normalizePlan($tenant->plan_code))->headline()->toString();
        $plural = $limit === 1 ? $label : str($label)->plural()->toString();

        return "{$plan} allows up to {$limit} active {$plural}.";
    }
}
