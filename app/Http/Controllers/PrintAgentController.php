<?php

namespace App\Http\Controllers;

use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Models\PrintJob;
use App\Support\PrintJobPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PrintAgentController extends Controller
{
    public function next(Request $request, PrintJobPayload $payload): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::enum(PrintJobType::class)],
        ]);

        $printJob = DB::transaction(function () use ($request, $validated) {
            $query = PrintJob::query()
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->where('status', PrintJobStatus::PENDING)
                ->oldest()
                ->lockForUpdate();

            if (! empty($validated['type'])) {
                $query->where('type', $validated['type']);
            }

            $job = $query->first();

            if ($job) {
                $job->update(['status' => PrintJobStatus::PROCESSING]);
            }

            return $job;
        });

        if (! $printJob) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $payload->build($printJob)]);
    }

    public function update(Request $request, PrintJob $printJob): JsonResponse
    {
        abort_if(
            $printJob->tenant_id !== $request->user()->tenant_id || $printJob->outlet_id !== $request->user()->outlet_id,
            404
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in([PrintJobStatus::COMPLETED->value, PrintJobStatus::FAILED->value])],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = PrintJobStatus::from($validated['status']);

        $printJob->update([
            'status' => $status,
            'printed_at' => $status === PrintJobStatus::COMPLETED ? now() : $printJob->printed_at,
            'failed_at' => $status === PrintJobStatus::FAILED ? now() : null,
            'payload' => [
                ...($printJob->payload ?? []),
                'agent_message' => $validated['message'] ?? null,
            ],
        ]);

        return response()->json(['data' => [
            'id' => $printJob->id,
            'status' => $printJob->status->value,
        ]]);
    }
}
