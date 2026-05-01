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
        'kds_basic',
        'api_read',
    ];

    protected function casts(): array
    {
        return [
            'qr_ordering' => 'boolean',
            'delivery' => 'boolean',
            'inventory_basic' => 'boolean',
            'kds_basic' => 'boolean',
            'api_read' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
