<?php

namespace Tests\Unit;

use Tests\TestCase;

class HorizonRedisRuntimeConfigTest extends TestCase
{
    public function test_bronze_runtime_uses_redis_horizon_printing_supervisor_config(): void
    {
        $this->assertSame('redis', config('queue.connections.redis.driver'));
        $this->assertSame('default', config('queue.connections.redis.connection'));
        $this->assertSame('default', config('queue.connections.redis.queue'));

        $this->assertSame('redis', config('horizon.defaults.supervisor-printing.connection'));
        $this->assertSame(['printing', 'default'], config('horizon.defaults.supervisor-printing.queue'));
        $this->assertSame(90, config('horizon.defaults.supervisor-printing.timeout'));
        $this->assertSame(3, config('horizon.defaults.supervisor-printing.tries'));
        $this->assertSame(4, config('horizon.environments.production.supervisor-printing.maxProcesses'));

        $this->assertArrayHasKey('redis:printing', config('horizon.waits'));
        $this->assertArrayHasKey('redis:default', config('horizon.waits'));
    }
}
