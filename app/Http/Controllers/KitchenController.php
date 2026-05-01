<?php

namespace App\Http\Controllers;

use App\Events\KitchenTicketStatusChanged;
use App\Enums\KitchenTicketStatus;
use App\Enums\OrderStatus;
use App\Models\KitchenTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = KitchenTicket::query()
            ->with(['order.items.modifiers', 'order.table'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->whereIn('status', [
                KitchenTicketStatus::PENDING,
                KitchenTicketStatus::PREPARING,
                KitchenTicketStatus::READY,
                KitchenTicketStatus::SERVED,
            ])
            ->latest('fired_at')
            ->latest()
            ->get()
            ->groupBy(fn (KitchenTicket $ticket) => $ticket->status->value);

        return view('kitchen.index', [
            'columns' => collect([
                KitchenTicketStatus::PENDING,
                KitchenTicketStatus::PREPARING,
                KitchenTicketStatus::READY,
                KitchenTicketStatus::SERVED,
            ])->mapWithKeys(fn (KitchenTicketStatus $status) => [$status->value => $tickets->get($status->value, collect())]),
        ]);
    }

    public function update(Request $request, KitchenTicket $ticket): RedirectResponse
    {
        $this->authorizeTenantTicket($request, $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(KitchenTicketStatus::class)],
        ]);

        $target = KitchenTicketStatus::from($validated['status']);

        DB::transaction(function () use ($ticket, $target): void {
            $ticketUpdates = ['status' => $target];
            $orderUpdates = [];

            if ($target === KitchenTicketStatus::PENDING) {
                $orderUpdates['status'] = OrderStatus::SENT_TO_KITCHEN;
            }

            if ($target === KitchenTicketStatus::PREPARING) {
                $ticketUpdates['fired_at'] = $ticket->fired_at ?: now();
                $orderUpdates['status'] = OrderStatus::SENT_TO_KITCHEN;
            }

            if ($target === KitchenTicketStatus::READY) {
                $ticketUpdates['ready_at'] = now();
                $orderUpdates['status'] = OrderStatus::READY;
            }

            if ($target === KitchenTicketStatus::SERVED) {
                $ticketUpdates['served_at'] = now();
                $orderUpdates['status'] = OrderStatus::SERVED;
            }

            $ticket->update($ticketUpdates);
            $ticket->order?->update($orderUpdates);
        });

        event(new KitchenTicketStatusChanged($ticket->refresh()));

        return back()->with('status', 'Kitchen ticket updated.');
    }

    private function authorizeTenantTicket(Request $request, KitchenTicket $ticket): void
    {
        abort_if(
            $ticket->tenant_id !== $request->user()->tenant_id || $ticket->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
