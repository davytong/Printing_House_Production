<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PrintRequest;
use App\Models\Machine;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->input('q', '');
        
        $books = collect();
        $materials = collect();
        $suppliers = collect();
        $pos = collect();
        $requests = collect();
        $machines = collect();
        $inventory = collect();
        $results = collect();

        if (strlen($query) >= 2) {
            // Search Books
            $books = Book::where('title', 'like', "%{$query}%")
                ->orWhere('grade', 'like', "%{$query}%")
                ->with('batch')
                ->limit(15)
                ->get()
                ->map(fn($b) => [
                    'type' => 'book',
                    'icon' => 'bi-book',
                    'title' => $b->title,
                    'subtitle' => ($b->grade ? "ថ្នាក់ {$b->grade} · " : '') . "{$b->total_printed}/{$b->target_qty}",
                    'url' => route('printing.index') . '#book-' . $b->id,
                    'badge' => $b->batch->name ?? '',
                ]);

            // Search Materials (Stock)
            $materials = Material::where('name', 'like', "%{$query}%")
                ->orWhere('name_km', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->where('status', 'active')
                ->limit(15)
                ->get()
                ->map(fn($m) => [
                    'type' => 'material',
                    'icon' => 'bi-box-seam',
                    'title' => $m->displayName(),
                    'subtitle' => "{$m->code} · {$m->currentStock()} {$m->unit}",
                    'url' => route('stock.materials.show', $m),
                    'badge' => $m->categoryLabelShort(),
                ]);

            // Search Suppliers
            $suppliers = Supplier::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('contact_person', 'like', "%{$query}%")
                ->where('status', 'active')
                ->limit(15)
                ->get()
                ->map(fn($s) => [
                    'type' => 'supplier',
                    'icon' => 'bi-shop',
                    'title' => $s->name,
                    'subtitle' => $s->code . ($s->contact_person ? " · {$s->contact_person}" : ''),
                    'url' => route('suppliers.show', $s),
                    'badge' => $s->supply_type ?? '',
                ]);

            // Search Purchase Orders
            $pos = PurchaseOrder::where('po_number', 'like', "%{$query}%")
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$query}%"))
                ->with('supplier')
                ->limit(15)
                ->get()
                ->map(fn($po) => [
                    'type' => 'purchase_order',
                    'icon' => 'bi-cart3',
                    'title' => $po->po_number,
                    'subtitle' => $po->supplier->name . " · \${$po->total_amount}",
                    'url' => route('purchase-orders.show', $po),
                    'badge' => $po->status,
                ]);

            // Search Print Requests
            $requests = PrintRequest::where('request_code', 'like', "%{$query}%")
                ->orWhere('title', 'like', "%{$query}%")
                ->orWhere('requester_name', 'like', "%{$query}%")
                ->limit(15)
                ->get()
                ->map(fn($r) => [
                    'type' => 'print_request',
                    'icon' => 'bi-printer',
                    'title' => $r->request_code,
                    'subtitle' => $r->title . " · {$r->requester_name}",
                    'url' => route('requests.show', $r),
                    'badge' => $r->status,
                ]);

            // Search Machines
            $machines = Machine::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('model', 'like', "%{$query}%")
                ->where('status', '!=', 'retired')
                ->limit(15)
                ->get()
                ->map(fn($m) => [
                    'type' => 'machine',
                    'icon' => 'bi-gear-wide-connected',
                    'title' => $m->name,
                    'subtitle' => $m->code . ($m->model ? " · {$m->model}" : ''),
                    'url' => route('machines.show', $m),
                    'badge' => $m->status,
                ]);

            // Search Inventory Items (legacy)
            $inventory = InventoryItem::where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->where('status', 'active')
                ->limit(15)
                ->get()
                ->map(fn($i) => [
                    'type' => 'inventory',
                    'icon' => 'bi-box',
                    'title' => $i->name,
                    'subtitle' => "{$i->code} · {$i->quantity_in_stock} {$i->unit}",
                    'url' => route('inventory.show', $i),
                    'badge' => $i->type,
                ]);

            $results = collect()
                ->merge($books)
                ->merge($materials)
                ->merge($suppliers)
                ->merge($pos)
                ->merge($requests)
                ->merge($machines)
                ->merge($inventory);
        }

        return view('search.results', compact(
            'query', 'results', 'books', 'materials', 'suppliers', 'pos', 'requests', 'machines', 'inventory'
        ));
    }
}
