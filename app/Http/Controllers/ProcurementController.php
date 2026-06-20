<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProcurementAttachment;
use App\Models\ProcurementLog;
use App\Models\ProcurementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProcurementRequest::query();

        // Filters
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('supplier')) $query->where('supplier_name', 'like', "%{$request->supplier}%");
        if ($request->filled('search'))   $query->where('item_name', 'like', "%{$request->search}%");

        $requests = $query->withCount('attachments')
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Dashboard stats
        $stats = [
            'total'     => ProcurementRequest::count(),
            'pending'   => ProcurementRequest::where('status', 'pending')->count(),
            'completed' => ProcurementRequest::where('status', 'completed')->count(),
            'this_month'=> ProcurementRequest::whereMonth('request_date', now()->month)
                            ->whereYear('request_date', now()->year)->count(),
            'total_value'=> ProcurementRequest::whereNotNull('total_amount')->sum('total_amount'),
        ];

        return view('procurement.index', compact('requests', 'stats'));
    }

    public function create(): View
    {
        // Get unique supplier names for autocomplete
        $suppliers = ProcurementRequest::distinct()->pluck('supplier_name')->filter()->values();
        return view('procurement.create', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'request_date'     => 'required|date',
            'requester'        => 'required|string|max:255',
            'department'       => 'nullable|string|max:255',
            'supplier_name'    => 'required|string|max:255',
            'priority'         => 'required|in:low,medium,high,urgent',
            'due_date'         => 'nullable|date',
            'status'           => 'required|in:pending,approved,ordered,received,completed,cancelled',
            'remarks'          => 'nullable|string|max:2000',
            'attachments'      => 'nullable|array|max:10',
            'attachments.*'    => 'file|max:20480',
            // Multi-item rows
            'items'            => 'required|array|min:1',
            'items.*.item_name'=> 'required|string|max:255',
            'items.*.category' => 'required|in:consumable,spare_part,component,service,equipment,other',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit'     => 'required|string|max:30',
            'items.*.unit_price'=> 'nullable|numeric|min:0',
            'items.*.item_description' => 'nullable|string|max:500',
        ]);

        // Create the header
        $pr = ProcurementRequest::create([
            'request_date'  => $data['request_date'],
            'requester'     => $data['requester'],
            'department'    => $data['department'] ?? null,
            'supplier_name' => $data['supplier_name'],
            'priority'      => $data['priority'],
            'due_date'      => $data['due_date'] ?? null,
            'status'        => $data['status'],
            'remarks'       => $data['remarks'] ?? null,
            // First item info for backward compat
            'item_name'     => $data['items'][0]['item_name'] ?? 'Multiple items',
            'category'      => $data['items'][0]['category'] ?? 'other',
            'quantity'      => collect($data['items'])->sum('quantity'),
            'unit'          => $data['items'][0]['unit'] ?? 'pcs',
        ]);

        // Create items
        $totalValue = 0;
        foreach ($data['items'] as $item) {
            $itemTotal = (!empty($item['unit_price']) ? $item['quantity'] * $item['unit_price'] : null);
            $totalValue += $itemTotal ?? 0;

            \App\Models\ProcurementItem::create([
                'procurement_request_id' => $pr->id,
                'item_name'        => $item['item_name'],
                'item_description' => $item['item_description'] ?? null,
                'category'         => $item['category'],
                'quantity'         => $item['quantity'],
                'unit'             => $item['unit'],
                'unit_price'       => $item['unit_price'] ?? null,
                'total_amount'     => $itemTotal,
            ]);
        }

        // Update header total
        $pr->update(['total_amount' => $totalValue > 0 ? $totalValue : null]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($pr, $request->file('attachments'), $data['requester']);
        }

        // Log
        $itemCount = count($data['items']);
        ProcurementLog::create([
            'procurement_request_id' => $pr->id,
            'action'       => 'created',
            'performed_by' => $data['requester'],
            'details'      => "Request created with {$itemCount} item(s)",
        ]);

        ActivityLog::record('Created Procurement', "{$pr->request_number} — {$itemCount} items from {$pr->supplier_name}");

        return redirect()->route('procurement.show', $pr)
            ->with('success', "Request {$pr->request_number} created with {$itemCount} item(s)");
    }

    public function show(ProcurementRequest $procurement): View
    {
        $procurement->load(['items', 'attachments', 'logs' => fn($q) => $q->latest()]);
        return view('procurement.show', compact('procurement'));
    }

    public function edit(ProcurementRequest $procurement): View
    {
        $suppliers = ProcurementRequest::distinct()->pluck('supplier_name')->filter()->values();
        return view('procurement.edit', compact('procurement', 'suppliers'));
    }

    public function update(Request $request, ProcurementRequest $procurement): RedirectResponse
    {
        $data = $request->validate([
            'request_date'     => 'required|date',
            'requester'        => 'required|string|max:255',
            'department'       => 'nullable|string|max:255',
            'supplier_name'    => 'required|string|max:255',
            'category'         => 'required|in:consumable,spare_part,component,service,equipment,other',
            'item_name'        => 'required|string|max:255',
            'item_description' => 'nullable|string|max:2000',
            'quantity'         => 'required|numeric|min:0.01',
            'unit'             => 'required|string|max:30',
            'unit_price'       => 'nullable|numeric|min:0',
            'total_amount'     => 'nullable|numeric|min:0',
            'priority'         => 'required|in:low,medium,high,urgent',
            'due_date'         => 'nullable|date',
            'status'           => 'required|in:pending,approved,ordered,received,completed,cancelled',
            'remarks'          => 'nullable|string|max:2000',
            'attachments'      => 'nullable|array|max:10',
            'attachments.*'    => 'file|max:20480',
        ]);

        if (empty($data['total_amount']) && !empty($data['unit_price'])) {
            $data['total_amount'] = $data['quantity'] * $data['unit_price'];
        }

        $oldStatus = $procurement->status;
        unset($data['attachments']);
        $procurement->update($data);

        // Handle new uploads
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($procurement, $request->file('attachments'), $data['requester']);
        }

        // Log status change
        if ($oldStatus !== $procurement->status) {
            ProcurementLog::create([
                'procurement_request_id' => $procurement->id,
                'action'       => 'status_changed',
                'performed_by' => $data['requester'],
                'old_value'    => $oldStatus,
                'new_value'    => $procurement->status,
                'details'      => "Status: {$oldStatus} → {$procurement->status}",
            ]);
        }

        ProcurementLog::create([
            'procurement_request_id' => $procurement->id,
            'action'       => 'updated',
            'performed_by' => $data['requester'],
            'details'      => 'Request updated',
        ]);

        return redirect()->route('procurement.show', $procurement)
            ->with('success', 'សំណើរត្រូorg org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org');
    }

    public function updateStatus(Request $request, ProcurementRequest $procurement): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,ordered,received,completed,cancelled',
        ]);

        $old = $procurement->status;
        $procurement->update(['status' => $data['status']]);

        ProcurementLog::create([
            'procurement_request_id' => $procurement->id,
            'action'       => 'status_changed',
            'performed_by' => 'System',
            'old_value'    => $old,
            'new_value'    => $data['status'],
            'details'      => "Status: {$old} → {$data['status']}",
        ]);

        return back()->with('success', "Status updated: {$data['status']}");
    }

    public function destroy(ProcurementRequest $procurement): RedirectResponse
    {
        // Delete attachments from storage
        foreach ($procurement->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
        }
        $procurement->delete();
        return redirect()->route('procurement.index')
            ->with('success', 'សorg org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org');
    }

    public function deleteAttachment(ProcurementAttachment $attachment): RedirectResponse
    {
        $pr = $attachment->request;
        Storage::disk('public')->delete($attachment->file_path);

        ProcurementLog::create([
            'procurement_request_id' => $pr->id,
            'action'       => 'file_deleted',
            'performed_by' => 'User',
            'details'      => "Deleted: {$attachment->file_name}",
        ]);

        $attachment->delete();
        return back()->with('success', 'ឯកorg org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org');
    }

    /**
     * Analytics / statistics page
     */
    public function analytics(Request $request): View
    {
        $bySupplier = ProcurementRequest::selectRaw('supplier_name, COUNT(*) as cnt, SUM(quantity) as total_qty, SUM(total_amount) as total_val')
            ->groupBy('supplier_name')
            ->orderByDesc('cnt')
            ->limit(15)
            ->get();

        $byCategory = ProcurementRequest::selectRaw('category, COUNT(*) as cnt, SUM(total_amount) as total_val')
            ->groupBy('category')
            ->orderByDesc('cnt')
            ->get();

        $monthly = ProcurementRequest::selectRaw("DATE_FORMAT(request_date, '%Y-%m') as month, COUNT(*) as cnt, SUM(total_amount) as total_val")
            ->where('request_date', '>=', now()->subMonths(12))
            ->groupByRaw("DATE_FORMAT(request_date, '%Y-%m')")
            ->orderBy('month')
            ->get();

        $topItems = ProcurementRequest::selectRaw('item_name, COUNT(*) as cnt, SUM(quantity) as total_qty')
            ->groupBy('item_name')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        return view('procurement.analytics', compact('bySupplier', 'byCategory', 'monthly', 'topItems'));
    }

    // ── Quick Entry: batch add multiple requests ─────────
    public function quickEntry(): View
    {
        $suppliers = ProcurementRequest::distinct()->pluck('supplier_name')->filter()->values();
        return view('procurement.quick-entry', compact('suppliers'));
    }

    public function quickStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'requester'    => 'required|string|max:255',
            'department'   => 'nullable|string|max:255',
            'request_date' => 'required|date',
            'rows'         => 'required|array|min:1',
            'rows.*.supplier_name' => 'required|string|max:255',
            'rows.*.category'      => 'required|in:consumable,spare_part,component,service,equipment,other',
            'rows.*.item_name'     => 'required|string|max:255',
            'rows.*.quantity'      => 'required|numeric|min:0.01',
            'rows.*.unit'          => 'required|string|max:30',
            'rows.*.unit_price'    => 'nullable|numeric|min:0',
            'rows.*.priority'      => 'nullable|in:low,medium,high,urgent',
            'rows.*.remarks'       => 'nullable|string|max:500',
        ]);

        $count = 0;
        foreach ($data['rows'] as $row) {
            $total = (!empty($row['unit_price']) && $row['quantity'])
                ? $row['quantity'] * $row['unit_price'] : null;

            $pr = ProcurementRequest::create([
                'request_date'  => $data['request_date'],
                'requester'     => $data['requester'],
                'department'    => $data['department'] ?? null,
                'supplier_name' => $row['supplier_name'],
                'category'      => $row['category'],
                'item_name'     => $row['item_name'],
                'quantity'      => $row['quantity'],
                'unit'          => $row['unit'],
                'unit_price'    => $row['unit_price'] ?? null,
                'total_amount'  => $total,
                'priority'      => $row['priority'] ?? 'medium',
                'status'        => 'pending',
                'remarks'       => $row['remarks'] ?? null,
            ]);

            ProcurementLog::create([
                'procurement_request_id' => $pr->id,
                'action'       => 'created',
                'performed_by' => $data['requester'],
                'details'      => "Quick entry: {$pr->item_name} x{$pr->quantity}",
            ]);

            $count++;
        }

        return redirect()->route('procurement.index')
            ->with('success', "Created {$count} procurement requests successfully!");
    }

    // ── Private helpers ──────────────────────────────────
    private function storeAttachments(ProcurementRequest $pr, array $files, ?string $uploader = null): void
    {
        foreach ($files as $file) {
            $ext      = strtolower($file->getClientOriginalExtension());
            $fileType = match(true) {
                in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'image',
                in_array($ext, ['pdf'])                          => 'pdf',
                in_array($ext, ['doc','docx'])                   => 'doc',
                in_array($ext, ['xls','xlsx'])                   => 'excel',
                default                                          => 'other',
            };

            $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $ext;
            $path     = $file->storeAs('procurement/' . $pr->id, $filename, 'public');

            ProcurementAttachment::create([
                'procurement_request_id' => $pr->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_type'   => $fileType,
                'file_size'   => $file->getSize(),
                'uploaded_by' => $uploader,
            ]);

            ProcurementLog::create([
                'procurement_request_id' => $pr->id,
                'action'       => 'file_uploaded',
                'performed_by' => $uploader,
                'details'      => "Uploaded: {$file->getClientOriginalName()}",
            ]);
        }
    }
}
