<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(Request $request): View
    {
        return view('tables.index', [
            'tables' => DiningTable::query()
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('tables.form', [
            'table' => new DiningTable(['is_active' => true, 'seats' => 2]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedTable($request);

        DiningTable::create([
            'tenant_id' => $request->user()->tenant_id,
            'outlet_id' => $request->user()->outlet_id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?: null,
            'seats' => $validated['seats'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('tables.index')->with('status', 'Table created.');
    }

    public function edit(Request $request, DiningTable $table): View
    {
        $this->authorizeTenantTable($request, $table);

        return view('tables.form', ['table' => $table]);
    }

    public function update(Request $request, DiningTable $table): RedirectResponse
    {
        $this->authorizeTenantTable($request, $table);
        $validated = $this->validatedTable($request, $table);

        $table->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?: null,
            'seats' => $validated['seats'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('tables.index')->with('status', 'Table updated.');
    }

    public function destroy(Request $request, DiningTable $table): RedirectResponse
    {
        $this->authorizeTenantTable($request, $table);

        $hasOpenOrder = $table->orders()
            ->whereNotIn('status', ['VOIDED', 'PAID'])
            ->exists();

        if ($hasOpenOrder) {
            return back()->with('error', 'This table has an active order and cannot be deleted.');
        }

        $table->delete();

        return back()->with('status', 'Table deleted.');
    }

    /**
     * @return array{name: string, code?: string|null, seats?: int|null}
     */
    private function validatedTable(Request $request, ?DiningTable $table = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dining_tables', 'name')
                    ->where('outlet_id', $request->user()->outlet_id)
                    ->ignore($table?->id),
            ],
            'code' => ['nullable', 'string', 'max:255'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function authorizeTenantTable(Request $request, DiningTable $table): void
    {
        abort_if(
            $table->tenant_id !== $request->user()->tenant_id || $table->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
