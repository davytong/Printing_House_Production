<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\TelegramGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrintingController extends Controller
{
    // ─────────────────────────────────────────────
    // Shared sort: category → level number → title
    // ─────────────────────────────────────────────
    private function booksOrdered()
    {
        return Book::orderBy('category')
            ->orderByRaw("CAST(SUBSTRING_INDEX(COALESCE(grade,'0'), ' ', -1) AS UNSIGNED)")
            ->orderBy('title');
    }

    // ─────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $books = $this->booksOrdered()->get();
        return view('printing.index', compact('books'));
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
            'csv_file' => 'required|file|mimes:csv,txt|max:20480', // 20 MB
        ]);

        $path   = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        // Strip UTF-8 BOM if present (common in Excel exports)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle); // not a BOM — go back to start
        }

        // Auto-detect delimiter (comma vs semicolon vs tab)
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3); // skip BOM again

        $delimiters = [
            ','  => substr_count($firstLine, ','),
            ';'  => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($delimiters);
        $delimiter = array_key_first($delimiters);

        fgetcsv($handle, 0, $delimiter); // skip header row

        $created  = 0;
        $updated  = 0;
        $skipped  = [];
        $lineNum  = 1; // header was line 1

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNum++;

            // Skip completely empty rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) continue;

            if (count($row) < 3) {
                $skipped[] = "Line {$lineNum}: too few columns (" . count($row) . ")";
                continue;
            }

            $title     = trim($row[0]);
            $category  = strtolower(trim($row[1]));
            $targetQty = (int) str_replace([',', ' '], '', $row[2] ?? '0');
            $printed   = (int) str_replace([',', ' '], '', $row[3] ?? '0');
            $grade     = trim($row[4] ?? '');

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
                    'title'         => $title,
                    'category'      => $category,
                    'grade'         => $grade ?: null,
                    'target_qty'    => $targetQty,
                    'total_printed' => min($printed, $targetQty),
                ]);
                $created++;
            }
        }

        fclose($handle);

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
