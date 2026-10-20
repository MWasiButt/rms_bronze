<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'currency',
        'timezone',
        'receipt_header',
        'receipt_footer',
        'default_tax_rate_bps',
        'qr_ordering',
        'delivery',
        'inventory_basic',
        'inventory_advanced',
        'kds_basic',
        'kds_pro',
        'coupons',
        'loyalty_basic',
        'api_read',
        'webhooks',
        'multi_outlet',
        'commissary',
        'analytics_advanced',
        'role_permissions_advanced',
        'shift_reports',
    ];

    protected function casts(): array
    {
        return [
            'qr_ordering' => 'boolean',
            'delivery' => 'boolean',
            'inventory_basic' => 'boolean',
            'inventory_advanced' => 'boolean',
            'kds_basic' => 'boolean',
            'kds_pro' => 'boolean',
            'coupons' => 'boolean',
            'loyalty_basic' => 'boolean',
            'api_read' => 'boolean',
            'webhooks' => 'boolean',
            'multi_outlet' => 'boolean',
            'commissary' => 'boolean',
            'analytics_advanced' => 'boolean',
            'role_permissions_advanced' => 'boolean',
            'shift_reports' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
