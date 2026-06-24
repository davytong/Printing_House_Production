<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\ProductionBatch;
use App\Models\BatchSnapshot;
use App\Models\TelegramGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrintingController extends Controller
{
    // ─────────────────────────────────────────────
    // Shared sort: scoped to the ACTIVE batch's books
    // ─────────────────────────────────────────────
    private function booksOrdered()
    {
        $activeId = ProductionBatch::current()->id;
        return Book::where('batch_id', $activeId)
            ->orderBy('category')
            ->orderByRaw("CAST(SUBSTRING_INDEX(COALESCE(grade,'0'), ' ', -1) AS UNSIGNED)")
            ->orderBy('title');
    }

    // ─────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $currentBatch = ProductionBatch::current();
        $books        = $this->booksOrdered()->get();
        $allBatches   = ProductionBatch::orderBy('id', 'desc')->get();

        return view('printing.index', compact('books', 'currentBatch', 'allBatches'));
    }

    // ─────────────────────────────────────────────
    // Start a NEW batch (printing round)
    //   Each batch owns its own book rows.
    //   - keep_targets       → clone active books (same targets, printed reset to 0)
    //   - keep_targets_zero  → clone active books (targets = 0, set later)
    //   - fresh              → start EMPTY so you add brand-new books
    // ─────────────────────────────────────────────
    public function startNewBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'nullable|string|max:255',
            'reset_mode'  => 'required|in:keep_targets,keep_targets_zero,fresh',
        ]);

        $current = ProductionBatch::current();
        $mode    = $request->reset_mode;

        // 1. Complete the current batch (its book rows stay intact as history)
        $current->update(['status' => 'completed', 'completed_at' => now()]);

        // 2. Create the new active batch
        $count = ProductionBatch::count();
        $newBatch = ProductionBatch::create([
            'name'       => $request->name ?: ('Batch ' . ($count + 1)),
            'status'     => 'active',
            'notes'      => $request->input('notes'),
            'started_at' => now(),
        ]);

        // 3. Populate the new batch's books based on mode
        if ($mode === 'keep_targets' || $mode === 'keep_targets_zero') {
            $oldBooks = Book::where('batch_id', $current->id)->get();
            foreach ($oldBooks as $b) {
                Book::create([
                    'batch_id'      => $newBatch->id,
                    'title'         => $b->title,
                    'category'      => $b->category,
                    'grade'         => $b->grade,
                    'target_qty'    => $mode === 'keep_targets_zero' ? 0 : $b->target_qty,
                    'total_printed' => 0,
                ]);
            }
            $msg = "បានចាប់ផ្ដើម {$newBatch->name} ថ្មី (សៀវភៅដដែល)! {$current->name} ត្រូវបានរក្សាទុក។";
        } else {
            // fresh → no books; user adds new ones
            $msg = "បានចាប់ផ្ដើម {$newBatch->name} ទទេ — សូមបន្ថែមសៀវភៅថ្មី! {$current->name} ត្រូវបានរក្សាទុក។";
        }

        return redirect()->route('printing.index')->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    // View a past batch's books (read-only)
    // ─────────────────────────────────────────────
    public function showBatch(ProductionBatch $batch): View
    {
        $snapshots = Book::where('batch_id', $batch->id)
            ->orderBy('category')
            ->orderByRaw("CAST(SUBSTRING_INDEX(COALESCE(grade,'0'), ' ', -1) AS UNSIGNED)")
            ->orderBy('title')
            ->get()
            ->map(function ($b) {
                // adapt Book to the shape the history view expects
                $b->printed_qty = $b->total_printed;
                return $b;
            });

        return view('printing.batch-history', compact('batch', 'snapshots'));
    }

    // ─────────────────────────────────────────────
    // Switch to another batch — just flip which one is active.
    // Each batch keeps its own book rows, so no data movement needed.
    // ─────────────────────────────────────────────
    public function restoreBatch(ProductionBatch $batch): RedirectResponse
    {
        $current = ProductionBatch::current();

        if ($batch->id === $current->id) {
            return redirect()->route('printing.index')
                ->with('success', "{$batch->name} គឺកំពុងសកម្មរួចហើយ។");
        }

        $current->update(['status' => 'completed', 'completed_at' => now()]);
        $batch->update(['status' => 'active', 'completed_at' => null]);

        return redirect()->route('printing.index')
            ->with('success', "បានប្ដូរទៅ {$batch->name}! ({$current->name} ត្រូវបានរក្សាទុក)");
    }

    // ─────────────────────────────────────────────
    // Delete a batch created by mistake (never the active one / last one)
    // ─────────────────────────────────────────────
    public function deleteBatch(ProductionBatch $batch): RedirectResponse
    {
        if ($batch->status === 'active') {
            return redirect()->route('printing.index')
                ->with('error', 'មិនអាចលុប Batch ដែលកំពុងសកម្ម។ សូមប្ដូរទៅ Batch ផ្សេងជាមុនសិន។');
        }
        if (ProductionBatch::count() <= 1) {
            return redirect()->route('printing.index')
                ->with('error', 'មិនអាចលុប Batch ចុងក្រោយបាន។');
        }

        $name = $batch->name;
        Book::where('batch_id', $batch->id)->delete(); // remove that batch's book rows
        $batch->snapshots()->delete();                 // legacy snapshots, if any
        $batch->delete();

        return redirect()->route('printing.index')
            ->with('success', "បានលុប {$name}។");
    }

    // ─────────────────────────────────────────────
    // Add single book
    // ─────────────────────────────────────────────
    public function storeBook(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'category'   => 'required|in:perfect_binding,staple',
            'grade'      => 'nullable|string|max:50',
            'target_qty' => 'required|integer|min:1',
        ]);

        $data['total_printed'] = 0;
        $data['batch_id'] = ProductionBatch::current()->id;
        Book::create($data);

        return back()->with('success', "បន្ថែមសៀវភៅ '{$data['title']}' ដោយជោគជ័យ");
    }

    // ─────────────────────────────────────────────
    // Update a book's target / grade / category
    // ─────────────────────────────────────────────
    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'category'   => 'required|in:perfect_binding,staple',
            'grade'      => 'nullable|string|max:50',
            'target_qty' => 'required|integer|min:1',
        ]);

        $book->update($data);
        return back()->with('success', "សៀវភៅ '{$book->title}' ត្រូវបានធ្វើបច្ចុប្បន្នភាព");
    }

    // ─────────────────────────────────────────────
    // Delete a book
    // ─────────────────────────────────────────────
    public function destroyBook(Book $book): RedirectResponse
    {
        $title = $book->title;
        $book->delete();
        return back()->with('success', "សៀវភៅ '{$title}' ត្រូវបានលុប");
    }

    // ─────────────────────────────────────────────
    // CSV Import — with full per-row feedback
    // ─────────────────────────────────────────────
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480', // 20 MB
        ]);

        $activeBatchId = ProductionBatch::current()->id;
        $file = $request->file('csv_file');
        $ext  = strtolower($file->getClientOriginalExtension());

        // Build a uniform array of data rows (header row skipped) from CSV or XLSX
        try {
            $dataRows = ($ext === 'xlsx' || $ext === 'xls')
                ? $this->readSpreadsheetRows($file->getRealPath())
                : $this->readCsvRows($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'មិនអាចអានឯកសារបានទេ: ' . $e->getMessage());
        }

        $created  = 0;
        $updated  = 0;
        $skipped  = [];
        $lineNum  = 1; // header was line 1

        foreach ($dataRows as $row) {
            $lineNum++;

            // Skip completely empty rows
            if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) continue;

            if (count($row) < 3) {
                $skipped[] = "Line {$lineNum}: too few columns (" . count($row) . ")";
                continue;
            }

            $title     = trim((string) ($row[0] ?? ''));
            $category  = strtolower(trim((string) ($row[1] ?? '')));
            $targetQty = (int) str_replace([',', ' '], '', (string) ($row[2] ?? '0'));
            $printed   = (int) str_replace([',', ' '], '', (string) ($row[3] ?? '0'));
            $grade     = trim((string) ($row[4] ?? ''));

            // Validate
            if (empty($title)) {
                $skipped[] = "Line {$lineNum}: empty title";
                continue;
            }
            if ($targetQty <= 0) {
                $skipped[] = "Line {$lineNum}: '{$title}' — target_qty must be > 0 (got {$targetQty})";
                continue;
            }

            // Normalise category — accept many formats
            if (str_contains($category, 'perfect') || str_contains($category, 'bind') ||
                str_contains($category, 'បិត')     || $category === 'pb') {
                $category = 'perfect_binding';
            } elseif (str_contains($category, 'staple') || str_contains($category, 'kib') ||
                      str_contains($category, 'កិប')     || $category === 'st') {
                $category = 'staple';
            } else {
                $category = 'staple'; // default fallback
            }

            $existing = Book::where('title', $title)
                ->where('grade', $grade ?: null)
                ->where('batch_id', $activeBatchId)
                ->first();

            if ($existing) {
                $existing->update([
                    'category'      => $category,
                    'target_qty'    => $targetQty,
                    'total_printed' => min($printed, $targetQty),
                ]);
                $updated++;
            } else {
                Book::create([
                    'batch_id'      => $activeBatchId,
                    'title'         => $title,
                    'category'      => $category,
                    'grade'         => $grade ?: null,
                    'target_qty'    => $targetQty,
                    'total_printed' => min($printed, $targetQty),
                ]);
                $created++;
            }
        }

        // Build detailed feedback message
        $parts = [];
        if ($created > 0) $parts[] = "✅ បន្ថែម {$created} ចំណងជើងថ្មី";
        if ($updated > 0) $parts[] = "🔄 ធ្វើបច្ចុប្បន្នភាព {$updated} ចំណងជើង";
        if ($skipped)     $parts[] = "⚠️ Skip " . count($skipped) . " ជួរ";

        $message = implode(' · ', $parts) ?: 'គ្មានទិន្នន័យ';

        if ($skipped) {
            // Store skip details in session for display
            return back()
                ->with('success', $message)
                ->with('csv_skipped', array_slice($skipped, 0, 10)); // show first 10
        }

        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────────
    // Read CSV/TXT into array of rows (header skipped, BOM + delimiter handled)
    // ─────────────────────────────────────────────
    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3);

        $delimiters = [
            ','  => substr_count($firstLine, ','),
            ';'  => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($delimiters);
        $delimiter = array_key_first($delimiters);

        fgetcsv($handle, 0, $delimiter); // skip header

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    // ─────────────────────────────────────────────
    // Read XLSX/XLS first sheet into array of rows (header skipped)
    // ─────────────────────────────────────────────
    private function readSpreadsheetRows(string $path): array
    {
        $reader = new \OpenSpout\Reader\XLSX\Reader();
        $reader->open($path);

        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break; // only the first sheet
        }
        $reader->close();

        // Drop the header row
        if (!empty($rows)) {
            array_shift($rows);
        }

        return $rows;
    }

    // ─────────────────────────────────────────────
    // Report page
    // ─────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'book_id'       => 'required|exists:books,id',
            'printed_today' => 'required|integer|min:1',
        ]);

        $book      = Book::findOrFail($request->book_id);
        $remaining = max($book->target_qty - $book->total_printed, 0);

        if ($remaining === 0) {
            return back()->with('error', "'{$book->title}' បានដល់គោលដៅហើយ");
        }

        $amount = min((int) $request->printed_today, $remaining);

        DailyPrint::create([
            'book_id'       => $book->id,
            'printed_today' => $amount,
            'date'          => now()->toDateString(),
        ]);

        $book->increment('total_printed', $amount);

        return back()->with('success', "បានកត់ {$amount} ក្បាល សម្រាប់ '{$book->title}'");
    }

    // ─────────────────────────────────────────────
    // Batch update printed quantity for multiple books
    // ─────────────────────────────────────────────
    public function batchUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'updates'          => 'required|array|min:1',
            'updates.*.id'     => 'required|exists:books,id',
            'updates.*.amount' => 'required|integer|min:0',
            'mode'             => 'required|in:add,set_done,set_progress',
        ]);

        $mode    = $request->mode;
        $count   = 0;
        $details = [];

        foreach ($request->updates as $upd) {
            $book   = Book::find($upd['id']);
            if (!$book) continue;

            if ($mode === 'set_done') {
                // Set printed = target
                $was = $book->total_printed;
                $book->total_printed = $book->target_qty;
                $book->save();
                if ($book->total_printed !== $was) {
                    DailyPrint::create([
                        'book_id'       => $book->id,
                        'printed_today' => $book->target_qty - $was,
                        'date'          => now()->toDateString(),
                    ]);
                }
            } elseif ($mode === 'add') {
                // Add amount to printed
                $remaining = max($book->target_qty - $book->total_printed, 0);
                $add       = min((int) $upd['amount'], $remaining);
                if ($add > 0) {
                    $book->increment('total_printed', $add);
                    DailyPrint::create([
                        'book_id'       => $book->id,
                        'printed_today' => $add,
                        'date'          => now()->toDateString(),
                    ]);
                }
            } elseif ($mode === 'set_progress') {
                // Set an exact total_printed value
                $val = min((int) $upd['amount'], $book->target_qty);
                $diff = $val - $book->total_printed;
                if ($diff > 0) {
                    $book->total_printed = $val;
                    $book->save();
                    DailyPrint::create([
                        'book_id'       => $book->id,
                        'printed_today' => $diff,
                        'date'          => now()->toDateString(),
                    ]);
                }
            }

            $count++;
            $details[] = $book->title;
        }

        $msg = "Updated {$count} book(s): " . implode(', ', array_slice($details, 0, 3))
             . (count($details) > 3 ? '...' : '');

        return back()->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    // Report page
    // ─────────────────────────────────────────────
    public function report(): View
    {
        $books          = $this->booksOrdered()->get();
        $telegramGroups = TelegramGroup::orderBy('name')->get();

        // Today's print stats
        $todayDate      = now()->toDateString();
        $todayPrints    = DailyPrint::where('date', $todayDate)
            ->selectRaw('book_id, SUM(printed_today) as today_qty')
            ->groupBy('book_id')
            ->pluck('today_qty', 'book_id');
        $todayTotal     = $todayPrints->sum();

        return view('printing.report', compact('books', 'telegramGroups', 'todayPrints', 'todayTotal'));
    }
}
