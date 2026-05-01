<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('outlet_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->string('role')->default('OWNER')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('last_active_at')->nullable()->after('is_active');
            $table->index(['tenant_id', 'outlet_id']);
            $table->index(['tenant_id', 'role']);
        });

        DB::table('users')->whereNull('role')->update(['role' => 'OWNER']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'outlet_id']);
            $table->dropIndex(['tenant_id', 'role']);
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn(['role', 'is_active', 'last_active_at']);
        });
    }
};
