<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\TelegramGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrintingController extends Controller
{
    // ─────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $books = Book::orderBy('category')->orderBy('title')->get();

        return view('printing.index', compact('books'));
    }

    // ─────────────────────────────────────────────
    // CSV Import
    // ─────────────────────────────────────────────
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        fgetcsv($handle); // skip header row
        $count = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) < 3) {
                continue;
            }

            $title     = trim($row[0]);
            $category  = trim($row[1]);
            $targetQty = (int) str_replace(',', '', $row[2] ?? 0);
            $printed   = (int) str_replace(',', '', $row[3] ?? 0);
            $grade     = trim($row[4] ?? '');

            if (empty($title) || $targetQty <= 0) {
                $errors[] = "Skipped invalid row: " . implode(', ', $row);
                continue;
            }

            if (! in_array($category, ['perfect_binding', 'staple'])) {
                $category = 'staple';
            }

            Book::updateOrCreate(
                ['title' => $title],
                [
                    'category'      => $category,
                    'grade'         => $grade ?: null,
                    'target_qty'    => $targetQty,
                    'total_printed' => min($printed, $targetQty),
                ]
            );

            $count++;
        }

        fclose($handle);

        $message = "CSV imported: {$count} book(s) processed.";
        if ($errors) {
            $message .= ' Skipped ' . count($errors) . ' invalid row(s).';
        }

        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────────
    // Log daily print
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
            return back()->with('error', "'{$book->title}' has already reached its target quantity.");
        }

        // Server-side cap: never exceed remaining
        $amount = min((int) $request->printed_today, $remaining);

        DailyPrint::create([
            'book_id'       => $book->id,
            'printed_today' => $amount,
            'date'          => now()->toDateString(),
        ]);

        $book->increment('total_printed', $amount);

        return back()->with('success', "Logged {$amount} copies for '{$book->title}'.");
    }

    // ─────────────────────────────────────────────
    // Report page
    // ─────────────────────────────────────────────
    public function report(): View
    {
        $books          = Book::orderBy('category')->orderBy('title')->get();
        $telegramGroups = TelegramGroup::orderBy('name')->get();

        return view('printing.report', compact('books', 'telegramGroups'));
    }
}
