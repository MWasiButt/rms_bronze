<?php

namespace App\Support;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;

class TenantContext
{
    public function __construct(
        private readonly ?Tenant $tenant = null,
        private readonly ?Outlet $outlet = null,
        private readonly ?User $user = null,
    ) {
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function outlet(): ?Outlet
    {
        return $this->outlet;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function tenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function outletId(): ?int
    {
        return $this->outlet?->id;
    }

    public function belongsToTenant(?int $tenantId): bool
    {
        return $tenantId !== null && $this->tenantId() === $tenantId;
    }

    public function belongsToOutlet(?int $outletId): bool
    {
        return $outletId !== null && $this->outletId() === $outletId;
    }
}
