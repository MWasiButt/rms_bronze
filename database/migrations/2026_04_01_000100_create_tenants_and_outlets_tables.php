<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan_code')->default('bronze');
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('max_outlets')->default(1);
            $table->unsignedSmallInteger('max_pos_devices')->default(2);
            $table->unsignedSmallInteger('max_active_users')->default(3);
            $table->timestamps();
        });

        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->unsignedInteger('default_tax_rate_bps')->default(0);
            $table->boolean('qr_ordering')->default(false);
            $table->boolean('delivery')->default(false);
            $table->boolean('inventory_basic')->default(true);
            $table->boolean('kds_basic')->default(true);
            $table->boolean('api_read')->default(false);
            $table->timestamps();
        });

        Schema::create('outlet_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('service_charge_enabled')->default(false);
            $table->unsignedInteger('service_charge_bps')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->uuid('device_uuid')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'outlet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_devices');
        Schema::dropIfExists('outlet_settings');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('outlets');
        Schema::dropIfExists('tenants');
    }
};
