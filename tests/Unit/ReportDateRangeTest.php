<?php

namespace Tests\Unit;

use App\Support\ReportDateRange;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReportDateRangeTest extends TestCase
{
    public function test_it_uses_request_dates(): void
    {
        $range = ReportDateRange::fromRequest(new Request([
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
        ]));

        $this->assertSame('2026-04-01', $range->startDate());
        $this->assertSame('2026-04-30', $range->endDate());
    }

    public function test_it_normalizes_reversed_dates(): void
    {
        $range = ReportDateRange::fromRequest(new Request([
            'start_date' => '2026-04-30',
            'end_date' => '2026-04-01',
        ]));

        $this->assertSame('2026-04-01', $range->startDate());
        $this->assertSame('2026-04-30', $range->endDate());
    }
}
