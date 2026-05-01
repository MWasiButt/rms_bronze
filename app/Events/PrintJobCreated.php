<?php

namespace App\Events;

use App\Models\PrintJob;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PrintJob $printJob)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->printJob->tenant_id.'.print');
    }

    public function broadcastAs(): string
    {
        return 'print.job.created';
    }

    public function broadcastWith(): array
    {
        return [
            'print_job_id' => $this->printJob->id,
            'type' => $this->printJob->type->value,
            'status' => $this->printJob->status->value,
            'order_id' => $this->printJob->order_id,
        ];
    }
}
