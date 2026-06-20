<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenanceSchedule;
use App\Models\SystemNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function index(): View
    {
        $machines = Machine::withCount([
            'maintenanceSchedules as completed_maintenance_count' => fn($q) =>
                $q->where('status', 'completed'),
        ])->orderBy('status')->orderBy('name')->get();

        $stats = [
            'operational' => Machine::where('status', 'operational')->count(),
            'maintenance' => Machine::where('status', 'maintenance')->count(),
            'breakdown'   => Machine::where('status', 'breakdown')->count(),
            'idle'        => Machine::where('status', 'idle')->count(),
            'due_soon'    => Machine::whereDate('next_maintenance', '<=', now()->addDays(7))
                ->where('status', '!=', 'retired')->count(),
        ];

        return view('machines.index', compact('machines', 'stats'));
    }

    public function create(): View
    {
        return view('machines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:255',
            'model'                     => 'nullable|string|max:100',
            'manufacturer'              => 'nullable|string|max:100',
            'serial_number'             => 'nullable|string|max:100',
            'type'                      => 'required|in:offset,digital,binding,cutting,folding,other',
            'status'                    => 'required|in:operational,maintenance,breakdown,idle',
            'purchased_date'            => 'nullable|date',
            'last_maintenance'          => 'nullable|date',
            'maintenance_interval_days' => 'required|integer|min:1',
            'notes'                     => 'nullable|string|max:500',
        ]);

        // Auto-calculate next maintenance from last_maintenance
        if (! empty($data['last_maintenance'])) {
            $data['next_maintenance'] = Carbon::parse($data['last_maintenance'])
                ->addDays($data['maintenance_interval_days'])->toDateString();
        }

        // code is auto-set by booted() after insert
        Machine::create($data);

        return redirect()->route('machines.index')
            ->with('success', 'ម៉ាស៊ីនត្រូវបានបន្ថែមដោយជោគជ័យ');
    }

    public function show(Machine $machine): View
    {
        $maintenances = $machine->maintenanceSchedules()
            ->orderByDesc('scheduled_date')
            ->paginate(10);

        return view('machines.show', compact('machine', 'maintenances'));
    }

    public function edit(Machine $machine): View
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine): RedirectResponse
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:255',
            'model'                     => 'nullable|string|max:100',
            'manufacturer'              => 'nullable|string|max:100',
            'serial_number'             => 'nullable|string|max:100',
            'type'                      => 'required|in:offset,digital,binding,cutting,folding,other',
            'status'                    => 'required|in:operational,maintenance,breakdown,idle,retired',
            'purchased_date'            => 'nullable|date',
            'last_maintenance'          => 'nullable|date',
            'maintenance_interval_days' => 'required|integer|min:1',
            'notes'                     => 'nullable|string|max:500',
        ]);

        if (! empty($data['last_maintenance'])) {
            $data['next_maintenance'] = Carbon::parse($data['last_maintenance'])
                ->addDays($data['maintenance_interval_days'])->toDateString();
        }

        if ($data['status'] === 'breakdown' && $machine->status !== 'breakdown') {
            SystemNotification::notify('danger', 'machines',
                'ម៉ាស៊ីន Breakdown!',
                "ម៉ាស៊ីន {$machine->name} ({$machine->code}) — Breakdown",
                route('machines.show', $machine)
            );
        }

        $machine->update($data);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'ព័ត៌មានម៉ាស៊ីនត្រូវបានធ្វើបច្ចុប្បន្នភាព');
    }

    public function destroy(Machine $machine): RedirectResponse
    {
        if ($machine->maintenanceSchedules()->exists()) {
            // Soft-retire instead of hard delete when history exists
            $machine->update(['status' => 'retired']);
            return redirect()->route('machines.index')
                ->with('success', "ម៉ាស៊ីន {$machine->name} ត្រូវបាន Retire");
        }

        $machine->delete();
        return redirect()->route('machines.index')
            ->with('success', "ម៉ាស៊ីន {$machine->name} ត្រូវបានលុប");
    }

    public function scheduleMaintenance(Request $request, Machine $machine): RedirectResponse
    {
        $data = $request->validate([
            'type'           => 'required|in:preventive,corrective,inspection,breakdown',
            'scheduled_date' => 'required|date',
            'technician'     => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:500',
        ]);

        MaintenanceSchedule::create([
            'machine_id'     => $machine->id,
            'type'           => $data['type'],
            'scheduled_date' => $data['scheduled_date'],
            'technician'     => $data['technician'] ?? null,
            'description'    => $data['description'] ?? null,
            'status'         => 'scheduled',
        ]);

        return back()->with('success', 'គ្រោងការថែទាំត្រូវបានកំណត់');
    }

    public function completeMaintenance(Request $request, MaintenanceSchedule $schedule): RedirectResponse
    {
        $data = $request->validate([
            'completed_date' => 'required|date',
            'downtime_hours' => 'nullable|integer|min:0',
            'findings'       => 'nullable|string|max:1000',
            'parts_used'     => 'nullable|string|max:500',
            'cost'           => 'nullable|numeric|min:0',
        ]);

        $schedule->update([
            'status'         => 'completed',
            'completed_date' => $data['completed_date'],
            'downtime_hours' => $data['downtime_hours'] ?? 0,
            'findings'       => $data['findings'] ?? null,
            'parts_used'     => $data['parts_used'] ?? null,
            'cost'           => $data['cost'] ?? 0,
        ]);

        $machine = $schedule->machine;
        $machine->update([
            'last_maintenance' => $data['completed_date'],
            'next_maintenance' => Carbon::parse($data['completed_date'])
                ->addDays($machine->maintenance_interval_days)->toDateString(),
            'status'           => 'operational',
        ]);

        return back()->with('success', 'ការថែទាំត្រូវបានបញ្ចប់ — ម៉ាស៊ីន Operational');
    }
}
