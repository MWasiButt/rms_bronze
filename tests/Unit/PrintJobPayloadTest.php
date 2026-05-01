<?php

namespace Tests\Unit;

use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Models\PrintJob;
use App\Support\PrintJobPayload;
use Tests\TestCase;

class PrintJobPayloadTest extends TestCase
{
    public function test_it_builds_basic_print_job_payload(): void
    {
        $job = new PrintJob([
            'type' => PrintJobType::RECEIPT,
            'status' => PrintJobStatus::PENDING,
            'copies' => 1,
            'channel' => 'agent',
            'payload' => ['order_number' => 'ORD-1'],
        ]);
        $job->id = 10;

        $payload = app(PrintJobPayload::class)->build($job);

        $this->assertSame(10, $payload['id']);
        $this->assertSame('RECEIPT', $payload['type']);
        $this->assertSame('PENDING', $payload['status']);
        $this->assertSame('ORD-1', $payload['payload']['order_number']);
    }
}
