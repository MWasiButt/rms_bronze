<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

class PlanALimits
{
    public const MAX_OUTLETS = 1;
    public const MAX_POS_DEVICES = 2;
    public const MAX_ACTIVE_USERS = 3;

    /**
     * @return array{max_outlets: int, max_pos_devices: int, max_active_users: int}
     */
    public static function defaults(): array
    {
        return [
            'max_outlets' => self::MAX_OUTLETS,
            'max_pos_devices' => self::MAX_POS_DEVICES,
            'max_active_users' => self::MAX_ACTIVE_USERS,
        ];
    }

    public function assertTenantWithinLimits(Tenant $tenant): void
    {
        $tenant->loadCount([
            'outlets as active_outlets_count' => fn ($query) => $query->where('is_active', true),
            'posDevices as active_pos_devices_count' => fn ($query) => $query->where('is_active', true),
            'users as active_users_count' => fn ($query) => $query->where('is_active', true),
        ]);

        abort_if(
            $tenant->active_outlets_count > $tenant->max_outlets,
            403,
            'Bronze allows one active outlet only.'
        );

        abort_if(
            $tenant->active_pos_devices_count > $tenant->max_pos_devices,
            403,
            'Bronze allows up to two active POS devices.'
        );

        abort_if(
            $tenant->active_users_count > $tenant->max_active_users,
            403,
            'Bronze allows up to three active staff accounts.'
        );
    }

    public function assertCanAddOutlet(Tenant $tenant): void
    {
        $this->assertCanAdd($tenant, 'outlets', 'max_outlets', 'Bronze allows one active outlet only.');
    }

    public function assertCanAddPosDevice(Tenant $tenant): void
    {
        $this->assertCanAdd($tenant, 'posDevices', 'max_pos_devices', 'Bronze allows up to two active POS devices.');
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        $this->assertCanAdd($tenant, 'users', 'max_active_users', 'Bronze allows up to three active staff accounts.');
    }

    private function assertCanAdd(Tenant $tenant, string $relation, string $limitColumn, string $message): void
    {
        $activeCount = $tenant->{$relation}()
            ->where('is_active', true)
            ->count();

        if ($activeCount >= $tenant->{$limitColumn}) {
            throw ValidationException::withMessages([
                'plan' => $message,
            ]);
        }
    }
}
