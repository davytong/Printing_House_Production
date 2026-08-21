<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ProcurementItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutocompleteController extends Controller
{
    /**
     * Get supplier names for autocomplete
     */
    public function suppliers(Request $request)
    {
        $query = $request->input('q', '');
        
        $suppliers = Supplier::where('status', 'active')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->pluck('name');
        
        return response()->json($suppliers);
    }
    
    /**
     * Get material names for autocomplete
     */
    public function materials(Request $request)
    {
        $query = $request->input('q', '');
        $category = $request->input('category', '');
        
        $materials = Material::where('status', 'active')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('name', 'LIKE', "%{$query}%")
                       ->orWhere('name_km', 'LIKE', "%{$query}%");
                });
            })
            ->when($category && $category !== 'all', function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['name', 'name_km', 'unit', 'category']);
        
        return response()->json($materials->map(function ($m) {
            return [
                'name' => $m->name,
                'name_km' => $m->name_km,
                'unit' => $m->unit,
                'category' => $m->category,
            ];
        }));
    }
    
    /**
     * Get procurement item suggestions with prices
     */
    public function procurementItems(Request $request)
    {
        $query = $request->input('q', '');
        $category = $request->input('category', '');
        
        // Get recent procurement items with avg price
        $items = DB::table('procurement_items')
            ->select(
                'item_name as name',
                'unit',
                DB::raw('AVG(unit_price) as price'),
                DB::raw('MAX(created_at) as last_used')
            )
            ->when($query, function ($q) use ($query) {
                $q->where('item_name', 'LIKE', "%{$query}%");
            })
            ->when($category && $category !== 'all', function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->groupBy('item_name', 'unit')
            ->orderByDesc('last_used')
            ->limit(20)
            ->get();
        
        return response()->json($items->map(function ($item) {
            return [
                'name' => $item->name,
                'unit' => $item->unit,
                'price' => $item->price ? number_format($item->price, 2, '.', '') : null,
            ];
        }));
    }
    
    /**
     * Get department names
     */
    public function departments(Request $request)
    {
        $query = $request->input('q', '');
        
        // Get unique departments from procurement requests
        $departments = DB::table('procurement_requests')
            ->select('department')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->when($query, function ($q) use ($query) {
                $q->where('department', 'LIKE', "%{$query}%");
            })
            ->groupBy('department')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->pluck('department');
        
        return response()->json($departments);
    }
    
    /**
     * Get requester names
     */
    public function requesters(Request $request)
    {
        $query = $request->input('q', '');
        
        // Get unique requesters from procurement requests
        $requesters = DB::table('procurement_requests')
            ->select('requester')
            ->whereNotNull('requester')
            ->where('requester', '!=', '')
            ->when($query, function ($q) use ($query) {
                $q->where('requester', 'LIKE', "%{$query}%");
            })
            ->groupBy('requester')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->pluck('requester');
        
        return response()->json($requesters);
    }
}
