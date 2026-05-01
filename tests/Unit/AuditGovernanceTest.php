<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Support\Audit;
use ReflectionMethod;
use Tests\TestCase;

class AuditGovernanceTest extends TestCase
{
    public function test_audit_log_accepts_governance_fields(): void
    {
        $log = new AuditLog();

        $this->assertContains('event', $log->getFillable());
        $this->assertContains('old_values', $log->getFillable());
        $this->assertContains('new_values', $log->getFillable());
        $this->assertContains('ip_address', $log->getFillable());
        $this->assertContains('user_agent', $log->getFillable());
    }

    public function test_audit_payloads_strip_sensitive_tokens(): void
    {
        $method = new ReflectionMethod(Audit::class, 'clean');
        $method->setAccessible(true);

        $this->assertSame([
            'email' => 'owner@example.com',
        ], $method->invoke(null, [
            'email' => 'owner@example.com',
            'password' => 'secret',
            'remember_token' => 'token',
        ]));
    }
}
