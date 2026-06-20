<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $items = InventoryItem::with('supplier')->orderBy('type')->orderBy('name')->paginate(25);

        $stats = [
            'total'     => InventoryItem::where('status', 'active')->count(),
            'low_stock' => InventoryItem::where('status', 'active')
                ->whereColumn('quantity_in_stock', '<=', 'minimum_stock')->count(),
            'out_stock' => InventoryItem::where('status', 'active')
                ->where('quantity_in_stock', '<=', 0)->count(),
            'total_value' => InventoryItem::where('status', 'active')
                ->selectRaw('SUM(quantity_in_stock * unit_cost) as v')->value('v') ?? 0,
        ];

        return view('inventory.index', compact('items', 'stats'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        return view('inventory.create', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:paper,ink,plate,spare_part,chemical,other',
            'unit'             => 'required|string|max:50',
            'quantity_in_stock'=> 'required|numeric|min:0',
            'minimum_stock'    => 'required|numeric|min:0',
            'unit_cost'        => 'required|numeric|min:0',
            'location'         => 'nullable|string|max:100',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'description'      => 'nullable|string|max:500',
        ]);

        $data['code']   = InventoryItem::generateCode();
        $data['status'] = 'active';

        $item = InventoryItem::create($data);

        if ($data['quantity_in_stock'] > 0) {
            $item->transactions()->create([
                'type'           => 'in',
                'quantity'       => $data['quantity_in_stock'],
                'quantity_before'=> 0,
                'quantity_after' => $data['quantity_in_stock'],
                'reference'      => 'Initial stock',
                'performed_by'   => 'Admin',
            ]);
        }

        return redirect()->route('inventory.index')
            ->with('success', "បន្ថែម {$item->name} ទៅក្នុង Inventory ដោយជោគជ័យ");
    }

    public function show(InventoryItem $inventoryItem): View
    {
        $inventoryItem->load('supplier');
        $transactions = $inventoryItem->transactions()
            ->orderByDesc('transacted_at')->paginate(15);
        return view('inventory.show', compact('inventoryItem', 'transactions'));
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        return view('inventory.edit', compact('inventoryItem', 'suppliers'));
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:paper,ink,plate,spare_part,chemical,other',
            'unit'          => 'required|string|max:50',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost'     => 'required|numeric|min:0',
            'location'      => 'nullable|string|max:100',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'description'   => 'nullable|string|max:500',
            'status'        => 'required|in:active,inactive',
        ]);

        $inventoryItem->update($data);
        return redirect()->route('inventory.show', $inventoryItem)
            ->with('success', 'ព័ត៌មាន Inventory ត្រូវបានធ្វើបច្ចុប្បន្នភាព');
    }

    public function adjust(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validate([
            'type'         => 'required|in:in,out,adjustment',
            'quantity'     => 'required|numeric|min:0.01',
            'reference'    => 'nullable|string|max:255',
            'performed_by' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:500',
        ]);

        $before = (float) $inventoryItem->quantity_in_stock;

        if ($data['type'] === 'out' && $data['quantity'] > $before) {
            return back()->with('error', 'ចំនួនក្នុង Stock មិនគ្រប់គ្រាន់');
        }

        $after = match ($data['type']) {
            'in'         => $before + $data['quantity'],
            'out'        => $before - $data['quantity'],
            'adjustment' => $data['quantity'],
        };

        $inventoryItem->update(['quantity_in_stock' => $after]);
        $inventoryItem->transactions()->create([
            'type'           => $data['type'],
            'quantity'       => abs($after - $before),
            'quantity_before'=> $before,
            'quantity_after' => $after,
            'reference'      => $data['reference'] ?? null,
            'performed_by'   => $data['performed_by'] ?? 'Admin',
            'notes'          => $data['notes'] ?? null,
        ]);

        // Low-stock alert
        if ($inventoryItem->isLowStock()) {
            SystemNotification::notify('warning', 'inventory',
                'Stock ទាប',
                "{$inventoryItem->name} — Stock នៅ {$after} {$inventoryItem->unit} (ខ្សោយ: {$inventoryItem->minimum_stock})",
                route('inventory.show', $inventoryItem)
            );
        }

        return back()->with('success', 'Stock ត្រូវបានកែសម្រួល');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->update(['status' => 'inactive']);
        return redirect()->route('inventory.index')
            ->with('success', 'Item ត្រូវបានបិទដំណើរការ');
    }
}

