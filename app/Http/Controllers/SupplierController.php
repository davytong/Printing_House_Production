<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withCount('purchaseOrders')->latest()->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'supply_type'    => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:500',
        ]);

        // code is auto-set by booted() after insert
        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'អ្នកផ្គត់ផ្គង់ត្រូវបានបន្ថែម');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->take(10)]);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'supply_type'    => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:500',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'ព័ត៌មានអ្នកផ្គត់ផ្គង់ត្រូវបានធ្វើបច្ចុប្បន្នភាព');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'មិនអាចលុបអ្នកផ្គត់ផ្គង់នេះបានទេ — មានការបញ្ជាទិញដែលភ្ជាប់');
        }
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'អ្នកផ្គត់ផ្គង់ត្រូវបានលុប');
    }
}
