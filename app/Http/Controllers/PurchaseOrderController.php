<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $orders = PurchaseOrder::with('supplier')->latest()->paginate(20);
        $stats  = [
            'draft'    => PurchaseOrder::where('status', 'draft')->count(),
            'sent'     => PurchaseOrder::where('status', 'sent')->count(),
            'partial'  => PurchaseOrder::where('status', 'partially_received')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'overdue'  => PurchaseOrder::whereIn('status', ['sent', 'partially_received'])
                ->whereDate('expected_date', '<', today())->count(),
        ];
        return view('purchase-orders.index', compact('orders', 'stats'));
    }

    public function create(): View
    {
        $suppliers      = Supplier::where('status', 'active')->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('status', 'active')->orderBy('name')->get();
        return view('purchase-orders.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePoData($request);

        // po_number is auto-set by booted() after insert
        $po = PurchaseOrder::create([
            'supplier_id'   => $data['supplier_id'],
            'order_date'    => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'currency'      => $data['currency'],
            'notes'         => $data['notes'] ?? null,
            'status'        => 'draft',
            'created_by'    => 'Admin',
            'total_amount'  => 0,
        ]);

        $total = $this->syncItems($po, $data['items']);
        $po->update(['total_amount' => $total]);

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "ការបញ្ជាទិញ {$po->po_number} ត្រូវបានបង្កើត");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.inventoryItem']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        if (! in_array($purchaseOrder->status, ['draft'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'គ្រាន់តែ Draft PO ប៉ុណ្ណោះអាចកែបាន');
        }
        $purchaseOrder->load(['supplier', 'items.inventoryItem']);
        $suppliers      = Supplier::where('status', 'active')->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('status', 'active')->orderBy('name')->get();
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'inventoryItems'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! in_array($purchaseOrder->status, ['draft'])) {
            return back()->with('error', 'គ្រាន់តែ Draft PO ប៉ុណ្ណោះអាចកែបាន');
        }

        $data = $this->validatePoData($request);

        $purchaseOrder->update([
            'supplier_id'   => $data['supplier_id'],
            'order_date'    => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'currency'      => $data['currency'],
            'notes'         => $data['notes'] ?? null,
        ]);

        // Replace items
        $purchaseOrder->items()->delete();
        $total = $this->syncItems($purchaseOrder, $data['items']);
        $purchaseOrder->update(['total_amount' => $total]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "PO {$purchaseOrder->po_number} ត្រូវបានធ្វើបច្ចុប្បន្នភាព");
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate(['status' => 'required|in:sent,cancelled']);
        $purchaseOrder->update(['status' => $request->status]);
        return back()->with('success', 'ស្ថានភាពបានធ្វើបច្ចុប្បន្នភាព');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate([
            'items'                     => 'required|array',
            'items.*.id'                => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
        ]);

        foreach ($request->items as $itemData) {
            $item = PurchaseOrderItem::find($itemData['id']);
            if (! $item) continue;

            $received = min((float) $itemData['quantity_received'], (float) $item->quantity_ordered);
            $item->update(['quantity_received' => $received]);

            if ($item->inventory_item_id && $received > 0) {
                $inv = InventoryItem::find($item->inventory_item_id);
                if ($inv) {
                    $before = (float) $inv->quantity_in_stock;
                    $inv->increment('quantity_in_stock', $received);
                    $inv->transactions()->create([
                        'type'            => 'in',
                        'quantity'        => $received,
                        'quantity_before' => $before,
                        'quantity_after'  => $before + $received,
                        'reference'       => $purchaseOrder->po_number,
                        'performed_by'    => 'Admin',
                    ]);
                    // Low-stock cleared — push notification if now OK
                    if ($inv->fresh()->isLowStock() === false) {
                        SystemNotification::notify('success', 'inventory',
                            'Stock ត្រឡប់ស្ថានភាពធម្មតា',
                            "{$inv->name} — stock ឥឡូវ {$inv->fresh()->quantity_in_stock} {$inv->unit}",
                            route('inventory.show', $inv)
                        );
                    }
                }
            }
        }

        $allItems      = $purchaseOrder->items()->get();
        $totalOrdered  = $allItems->sum('quantity_ordered');
        $totalReceived = $allItems->sum('quantity_received');

        if ($totalReceived >= $totalOrdered) {
            $purchaseOrder->update(['status' => 'received', 'received_date' => today()]);
            $newStatus = 'received';
        } elseif ($totalReceived > 0) {
            $purchaseOrder->update(['status' => 'partially_received']);
            $newStatus = 'partially_received';
        } else {
            $newStatus = $purchaseOrder->status;
        }

        SystemNotification::notify('success', 'purchase_orders',
            'ទំនិញបានទទួល',
            "PO {$purchaseOrder->po_number} — {$newStatus}",
            route('purchase-orders.show', $purchaseOrder)
        );

        return back()->with('success', 'ការទទួលទំនិញបានកត់ត្រា');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'មិនអាចលុប PO ដែលបានទទួលរួចបានទេ');
        }
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')
            ->with('success', 'ការបញ្ជាទិញត្រូវបានលុប');
    }

    // ─── private helpers ──────────────────────────────────

    private function validatePoData(Request $request): array
    {
        return $request->validate([
            'supplier_id'               => 'required|exists:suppliers,id',
            'order_date'                => 'required|date',
            'expected_date'             => 'nullable|date',
            'currency'                  => 'required|string|max:10',
            'notes'                     => 'nullable|string|max:1000',
            'items'                     => 'required|array|min:1',
            'items.*.item_name'         => 'required|string|max:255',
            'items.*.unit'              => 'required|string|max:50',
            'items.*.quantity_ordered'  => 'required|numeric|min:0.01',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.notes'             => 'nullable|string|max:255',
        ]);
    }

    private function syncItems(PurchaseOrder $po, array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $lineTotal = (float) $item['quantity_ordered'] * (float) $item['unit_price'];
            $total    += $lineTotal;
            PurchaseOrderItem::create([
                'purchase_order_id'  => $po->id,
                'inventory_item_id'  => $item['inventory_item_id'] ?? null,
                'item_name'          => $item['item_name'],
                'unit'               => $item['unit'],
                'quantity_ordered'   => $item['quantity_ordered'],
                'quantity_received'  => 0,
                'unit_price'         => $item['unit_price'],
                'total_price'        => $lineTotal,
                'notes'              => $item['notes'] ?? null,
            ]);
        }
        return $total;
    }
}
