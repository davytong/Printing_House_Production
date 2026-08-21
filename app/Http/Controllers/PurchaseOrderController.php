<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SystemNotification;
use App\Services\ExcelExportService;
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

        // Handle file uploads
        $storedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('po-attachments', 'public');
                $storedFiles[] = [
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                    'mime'          => $file->getMimeType(),
                ];
            }
        }

        // Determine status based on action
        $status = $data['status'] ?? 'draft';
        if ($request->input('action') === 'submit_for_approval') {
            $status = 'pending_approval';
        }

        // Get current user name
        $userName = session('user_name', 'Admin');

        // po_number is auto-set by booted() after insert
        $po = PurchaseOrder::create([
            'supplier_id'    => $data['supplier_id'],
            'order_date'     => $data['order_date'],
            'expected_date'  => $data['expected_date'] ?? null,
            'currency'       => $data['currency'],
            'notes'          => $data['notes'] ?? null,
            'status'         => $status,
            'created_by'     => $userName,
            'total_amount'   => 0,
            'attachments'    => $storedFiles ?: null,
            // New enhanced fields
            'priority'       => $data['priority'] ?? 'medium',
            'reason'         => $data['reason'] ?? null,
            'requested_by'   => $userName,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_status' => $data['payment_status'] ?? 'pending',
        ]);

        $total = $this->syncItems($po, $data['items']);
        $po->update(['total_amount' => $total]);

        $message = $status === 'pending_approval' 
            ? "PO {$po->po_number} ត្រូវបានបង្កើត និងដាក់ស្នើសុំអនុម័ត" 
            : "PO {$po->po_number} ត្រូវបានបង្កើត";

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', $message);
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

        // Handle new file uploads - merge with existing
        $existing = $purchaseOrder->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('po-attachments', 'public');
                $existing[] = [
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                    'mime'          => $file->getMimeType(),
                ];
            }
        }

        $purchaseOrder->update([
            'supplier_id'   => $data['supplier_id'],
            'order_date'    => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'currency'      => $data['currency'],
            'notes'         => $data['notes'] ?? null,
            'attachments'   => $existing ?: null,
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
        $request->validate([
            'status' => 'required|in:draft,pending_approval,approved,sent,in_transit,partially_received,cancelled',
        ]);

        $updates = ['status' => $request->status];

        // Track who approved and when
        if ($request->status === 'approved') {
            $updates['approved_by'] = session('user_name', 'Admin');
            $updates['approved_at'] = now();
        }

        $purchaseOrder->update($updates);
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
                        'performed_by'    => session('user_name', 'Admin'),
                    ]);
                    // Reload once to check low-stock status
                    $freshInv = $inv->fresh();
                    if ($freshInv && $freshInv->isLowStock() === false) {
                        SystemNotification::notify('success', 'inventory',
                            'Stock ត្រឡប់ស្ថានភាពធម្មតា',
                            "{$freshInv->name} — stock ឥឡូវ {$freshInv->quantity_in_stock} {$freshInv->unit}",
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

    /**
     * Add images/attachments to a PO (works for any status, including received)
     */
    public function addAttachments(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate([
            'attachments'   => 'required|array|min:1|max:10',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $existing = $purchaseOrder->attachments ?? [];
        
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('po-attachments', 'public');
            $existing[] = [
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
                'mime'          => $file->getMimeType(),
                'uploaded_at'   => now()->toDateTimeString(),
            ];
        }

        $purchaseOrder->update(['attachments' => $existing]);

        $count = count($request->file('attachments'));
        return back()->with('success', "បានបន្ថែមឯកសារ {$count} ទៅកាន់ PO {$purchaseOrder->po_number}");
    }

    /**
     * Remove a specific attachment from a PO
     */
    public function removeAttachment(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate(['index' => 'required|integer|min:0']);
        
        $attachments = $purchaseOrder->attachments ?? [];
        $index = $request->integer('index');
        
        if (!isset($attachments[$index])) {
            return back()->with('error', 'រកមិនឃើញឯកសារ');
        }

        // Delete file from storage
        $filePath = $attachments[$index]['path'] ?? null;
        if ($filePath && \Storage::disk('public')->exists($filePath)) {
            \Storage::disk('public')->delete($filePath);
        }

        // Remove from array
        array_splice($attachments, $index, 1);
        
        $purchaseOrder->update(['attachments' => $attachments ?: null]);

        return back()->with('success', 'បានលុបឯកសារ');
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
            'status'                    => 'nullable|string|in:draft,pending_approval,approved',
            'items'                     => 'required|array|min:1',
            'items.*.item_name'         => 'required|string|max:255',
            'items.*.unit'              => 'required|string|max:50',
            'items.*.quantity_ordered'  => 'required|numeric|min:0.01',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.notes'             => 'nullable|string|max:255',
            
            // Enhanced fields
            'priority'                  => 'nullable|string|in:low,medium,high,urgent',
            'reason'                    => 'required|string|max:1000',
            'payment_method'            => 'nullable|string|in:cash,bank,credit',
            'payment_status'            => 'nullable|string|in:pending,partial,paid',
            
            // Attachments (images of request forms, quotations, etc.)
            'attachments'               => 'nullable|array|max:10',
            'attachments.*'             => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx|max:10240',
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

    /**
     * Export purchase orders to Excel
     */
    public function exportExcel(Request $request, ExcelExportService $exportService)
    {
        try {
            $query = PurchaseOrder::with('supplier');
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }
            if ($request->filled('start_date')) {
                $query->where('order_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('order_date', '<=', $request->end_date);
            }
            
            $purchaseOrders = $query->latest()->get();
            
            $filename = 'purchase_orders_' . now()->format('Y-m-d') . '.xlsx';
            
            return $exportService->exportPurchaseOrders($purchaseOrders, $filename);
        } catch (\Throwable $e) {
            \Log::error('PO export failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'មិនអាចទាញយក Excel បានទេ។ សូមព្យាយាមម្តងទៀត។ / Unable to export Excel. Please try again.');
        }
    }
}
