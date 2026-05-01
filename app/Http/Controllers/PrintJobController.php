<?php

namespace App\Http\Controllers;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintJobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = PrintJob::query()
            ->with(['order', 'kitchenTicket'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->latest()
            ->limit(100)
            ->get();

        return view('print-jobs.index', [
            'jobs' => $jobs,
            'summary' => $jobs->groupBy(fn (PrintJob $job) => $job->status->value)->map->count(),
        ]);
    }

    public function retry(Request $request, PrintJob $printJob): RedirectResponse
    {
        $this->authorizeTenantPrintJob($request, $printJob);

        abort_if($printJob->status !== PrintJobStatus::FAILED, 422, 'Only failed print jobs can be retried.');

        $printJob->update([
            'status' => PrintJobStatus::PENDING,
            'failed_at' => null,
            'printed_at' => null,
        ]);

        return back()->with('status', 'Print job queued for retry.');
    }

    private function authorizeTenantPrintJob(Request $request, PrintJob $printJob): void
    {
        abort_if(
            $printJob->tenant_id !== $request->user()->tenant_id || $printJob->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
