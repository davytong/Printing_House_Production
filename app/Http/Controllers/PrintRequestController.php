<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\PrintRequest;
use App\Models\PrintRequestItem;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrintRequestController extends Controller
{
    // ─────────────────────────────────────────────
    // Index
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $requests = PrintRequest::withCount('items')
            ->latest()->paginate(20);

        $stats = [
            'pending'       => PrintRequest::where('status', 'pending')->count(),
            'approved'      => PrintRequest::where('status', 'approved')->count(),
            'in_production' => PrintRequest::where('status', 'in_production')->count(),
            'completed'     => PrintRequest::where('status', 'completed')->count(),
            'rejected'      => PrintRequest::where('status', 'rejected')->count(),
        ];

        return view('requests.index', compact('requests', 'stats'));
    }

    // ─────────────────────────────────────────────
    // Create form
    // ─────────────────────────────────────────────
    public function create(): View
    {
        $books = Book::orderBy('grade')->orderBy('title')->get();
        return view('requests.create', compact('books'));
    }

    // ─────────────────────────────────────────────
    // Store — multi-book with attachments
    // ─────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'requester_name' => 'required|string|max:255',
            'department'     => 'nullable|string|max:255',
            'priority'       => 'required|in:low,normal,high,urgent',
            'required_by'    => 'nullable|date',
            'notes'          => 'nullable|string|max:2000',

            // Book rows
            'books'                        => 'required|array|min:1',
            'books.*.book_title'           => 'required|string|max:255',
            'books.*.grade'                => 'nullable|string|max:50',
            'books.*.category'             => 'nullable|in:perfect_binding,staple,',
            'books.*.quantity_requested'   => 'required|integer|min:1',
            'books.*.book_id'              => 'nullable|exists:books,id',
            'books.*.notes'                => 'nullable|string|max:500',

            // Attachments
            'attachments'      => 'nullable|array|max:10',
            'attachments.*'    => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt|max:10240',
        ]);

        // Handle file uploads
        $storedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('request-attachments', 'public');
                $storedFiles[] = [
                    'path'         => $path,
                    'original_name'=> $file->getClientOriginalName(),
                    'size'         => $file->getSize(),
                    'mime'         => $file->getMimeType(),
                ];
            }
        }

        // Compute totals
        $totalQty   = array_sum(array_column($data['books'], 'quantity_requested'));
        $totalBooks = count($data['books']);

        $req = PrintRequest::create([
            'title'                => $data['title'],
            'requester_name'       => $data['requester_name'],
            'department'           => $data['department'] ?? null,
            'priority'             => $data['priority'],
            'status'               => 'pending',
            'quantity_requested'   => $totalQty,
            'total_books_requested'=> $totalBooks,
            'required_by'          => $data['required_by'] ?? null,
            'notes'                => $data['notes'] ?? null,
            'attachments'          => $storedFiles ?: null,
        ]);

        // Store each book line
        foreach ($data['books'] as $row) {
            PrintRequestItem::create([
                'print_request_id'   => $req->id,
                'book_id'            => $row['book_id'] ?: null,
                'book_title'         => $row['book_title'],
                'grade'              => $row['grade'] ?: null,
                'category'           => $row['category'] ?: null,
                'quantity_requested' => (int) $row['quantity_requested'],
                'notes'              => $row['notes'] ?? null,
            ]);
        }

        SystemNotification::notify(
            $data['priority'] === 'urgent' ? 'danger' : 'info',
            'requests',
            'ស្នើរសុំបោះពុម្ពថ្មី',
            "#{$req->request_code} — {$req->title} ({$req->requester_name}) · {$totalBooks} ចំណងជើង",
            route('requests.show', $req)
        );

        return redirect()->route('requests.show', $req)
            ->with('success', "ស្នើរសុំ {$req->request_code} — {$totalBooks} ចំណងជើង បានបញ្ជូនដោយជោគជ័យ");
    }

    // ─────────────────────────────────────────────
    // Show
    // ─────────────────────────────────────────────
    public function show(PrintRequest $request): View
    {
        $request->load(['items.book']);
        return view('requests.show', compact('request'));
    }

    // ─────────────────────────────────────────────
    // Edit
    // ─────────────────────────────────────────────
    public function edit(PrintRequest $request): View
    {
        $books = Book::orderBy('grade')->orderBy('title')->get();
        $request->load('items');
        return view('requests.edit', compact('request', 'books'));
    }

    // ─────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────
    public function update(Request $request, PrintRequest $printRequest): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'requester_name' => 'required|string|max:255',
            'department'     => 'nullable|string|max:255',
            'priority'       => 'required|in:low,normal,high,urgent',
            'required_by'    => 'nullable|date',
            'notes'          => 'nullable|string|max:2000',
            'books'                       => 'required|array|min:1',
            'books.*.book_title'          => 'required|string|max:255',
            'books.*.grade'               => 'nullable|string|max:50',
            'books.*.category'            => 'nullable|in:perfect_binding,staple,',
            'books.*.quantity_requested'  => 'required|integer|min:1',
            'books.*.book_id'             => 'nullable|exists:books,id',
            'books.*.notes'               => 'nullable|string|max:500',
            'attachments'     => 'nullable|array|max:10',
            'attachments.*'   => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt|max:10240',
        ]);

        // Handle new uploads merged with existing
        $existing = $printRequest->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('request-attachments', 'public');
                $existing[] = [
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                    'mime'          => $file->getMimeType(),
                ];
            }
        }

        $totalQty   = array_sum(array_column($data['books'], 'quantity_requested'));
        $totalBooks = count($data['books']);

        $printRequest->update([
            'title'                => $data['title'],
            'requester_name'       => $data['requester_name'],
            'department'           => $data['department'] ?? null,
            'priority'             => $data['priority'],
            'quantity_requested'   => $totalQty,
            'total_books_requested'=> $totalBooks,
            'required_by'          => $data['required_by'] ?? null,
            'notes'                => $data['notes'] ?? null,
            'attachments'          => $existing ?: null,
        ]);

        // Replace items
        $printRequest->items()->delete();
        foreach ($data['books'] as $row) {
            PrintRequestItem::create([
                'print_request_id'   => $printRequest->id,
                'book_id'            => $row['book_id'] ?: null,
                'book_title'         => $row['book_title'],
                'grade'              => $row['grade'] ?: null,
                'category'           => $row['category'] ?: null,
                'quantity_requested' => (int) $row['quantity_requested'],
                'notes'              => $row['notes'] ?? null,
            ]);
        }

        return redirect()->route('requests.show', $printRequest)
            ->with('success', 'ស្នើរសុំត្រូវបានធ្វើបច្ចុប្បន្នភាព');
    }

    // ─────────────────────────────────────────────
    // Remove single attachment
    // ─────────────────────────────────────────────
    public function removeAttachment(Request $request, PrintRequest $printRequest): RedirectResponse
    {
        $idx  = (int) $request->input('index', -1);
        $atts = $printRequest->attachments ?? [];
        if (isset($atts[$idx])) {
            Storage::disk('public')->delete($atts[$idx]['path']);
            array_splice($atts, $idx, 1);
            $printRequest->update(['attachments' => array_values($atts) ?: null]);
        }
        return back()->with('success', 'ឯកសារត្រូវបានលុប');
    }

    // ─────────────────────────────────────────────
    // Approve
    // ─────────────────────────────────────────────
    public function approve(Request $request, PrintRequest $printRequest): RedirectResponse
    {
        if ($printRequest->status !== 'pending') {
            return back()->with('error', 'ស្នើរសុំនេះមិនអាច approve បានទេ');
        }

        $printRequest->update([
            'status'      => 'approved',
            'approved_by' => $request->input('approved_by', 'Manager'),
            'approved_at' => now(),
        ]);

        SystemNotification::notify('success', 'requests',
            'ស្នើរសុំបានអនុម័ត',
            "#{$printRequest->request_code} · {$printRequest->total_books_requested} ចំណងជើង",
            route('requests.show', $printRequest)
        );

        return back()->with('success', 'ស្នើរសុំត្រូវបានអនុម័ត');
    }

    // ─────────────────────────────────────────────
    // Reject
    // ─────────────────────────────────────────────
    public function reject(Request $request, PrintRequest $printRequest): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $printRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        return back()->with('success', 'ស្នើរសុំត្រូវបានបដិសេធ');
    }

    // ─────────────────────────────────────────────
    // Update status
    // ─────────────────────────────────────────────
    public function updateStatus(Request $request, PrintRequest $printRequest): RedirectResponse
    {
        $request->validate(['status' => 'required|in:in_production,completed,cancelled']);
        $printRequest->update(['status' => $request->status]);
        return back()->with('success', 'ស្ថានភាពត្រូវបានធ្វើបច្ចុប្បន្នភាព');
    }

    // ─────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────
    public function destroy(PrintRequest $request): RedirectResponse
    {
        // Clean up attachments
        foreach ($request->attachments ?? [] as $att) {
            Storage::disk('public')->delete($att['path']);
        }
        $request->delete();
        return redirect()->route('requests.index')
            ->with('success', 'ស្នើរសុំត្រូវបានលុប');
    }
}
