<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function (Material $m) {
                $m->calculated_stock = $m->currentStock();
                $m->is_low = $m->isLowStock();
                return $m;
            });

        $summary = $this->stockService->getSummaryByCategory();

        return view('stock.materials.index', compact('materials', 'summary'));
    }

    public function create(): View
    {
        return view('stock.materials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Handle "other" category/unit from the picker
        if ($request->input('category') === 'other' && $request->filled('category_other')) {
            $request->merge(['category' => strtolower(preg_replace('/\s+/', '-', trim($request->input('category_other'))))]);
        }
        if ($request->input('unit') === 'other' && $request->filled('unit_other')) {
            $request->merge(['unit' => strtolower(trim($request->input('unit_other')))]);
        }

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'name_km'       => 'nullable|string|max:255',
            'category'      => 'required|string|max:50',
            'icon'          => 'nullable|string|max:100',
            'sub_type'      => 'nullable|string|max:100',
            'size'          => 'nullable|string|max:100',
            'unit'          => 'required|string|max:30',
            'min_stock'     => 'required|numeric|min:0',
            'location'      => 'nullable|string|max:100',
            'unit_cost'     => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:500',
            'initial_stock' => 'nullable|numeric|min:0',
        ]);

        $initialStock = (float) ($data['initial_stock'] ?? 0);
        unset($data['initial_stock']);
        $data['status'] = 'active';

        $material = Material::create($data);

        if ($initialStock > 0) {
            $this->stockService->recordMovement(
                $material, 'in', $initialStock, 'Initial stock', 'System'
            );
        }

        return redirect()->route('stock.materials.index')
            ->with('success', "បន្ថែម {$material->name} ដោយជោគជ័យ");
    }

    public function show(Material $material): View
    {
        $material->calculated_stock = $material->currentStock();
        $movements = $material->movements()
            ->orderByDesc('movement_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('stock.materials.show', compact('material', 'movements'));
    }

    public function edit(Material $material): View
    {
        return view('stock.materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        if ($request->input('category') === 'other' && $request->filled('category_other')) {
            $request->merge(['category' => strtolower(preg_replace('/\s+/', '-', trim($request->input('category_other'))))]);
        }
        if ($request->input('unit') === 'other' && $request->filled('unit_other')) {
            $request->merge(['unit' => strtolower(trim($request->input('unit_other')))]);
        }

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'name_km'   => 'nullable|string|max:255',
            'category'  => 'required|string|max:50',
            'icon'      => 'nullable|string|max:100',
            'sub_type'  => 'nullable|string|max:100',
            'size'      => 'nullable|string|max:100',
            'unit'      => 'required|string|max:30',
            'min_stock' => 'required|numeric|min:0',
            'location'  => 'nullable|string|max:100',
            'unit_cost' => 'nullable|numeric|min:0',
            'status'    => 'required|in:active,inactive',
            'notes'     => 'nullable|string|max:500',
        ]);

        $material->update($data);

        return redirect()->route('stock.materials.show', $material)
            ->with('success', 'ធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $material->update(['status' => 'inactive']);
        return redirect()->route('stock.materials.index')
            ->with('success', "{$material->name} ត្រូវបានបិទ");
    }
}
