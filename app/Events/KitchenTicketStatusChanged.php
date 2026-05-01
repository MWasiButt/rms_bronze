<?php

namespace App\Events;

use App\Models\KitchenTicket;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenTicketStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly KitchenTicket $ticket)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.'.$this->ticket->tenant_id.'.kitchen'),
            new PrivateChannel('tenant.'.$this->ticket->tenant_id.'.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kitchen.ticket.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'order_id' => $this->ticket->order_id,
            'status' => $this->ticket->status->value,
        ];
    }
}
