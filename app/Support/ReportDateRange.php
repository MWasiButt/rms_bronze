<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ReportDateRange
{
    public function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $start = $request->filled('start_date')
            ? CarbonImmutable::parse($request->input('start_date'))->startOfDay()
            : CarbonImmutable::today()->startOfDay();

        $end = $request->filled('end_date')
            ? CarbonImmutable::parse($request->input('end_date'))->endOfDay()
            : CarbonImmutable::today()->endOfDay();

        if ($end->lessThan($start)) {
            return new self($end->startOfDay(), $start->endOfDay());
        }

        return new self($start, $end);
    }

    public function startDate(): string
    {
        return $this->start->toDateString();
    }

    public function endDate(): string
    {
        return $this->end->toDateString();
    }
}
