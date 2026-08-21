<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\ProductionBatch;
use Carbon\Carbon;

class DailyReportService
{
    /**
     * Generate daily report in formatted text (Khmer style)
     */
    public function generateDailyReport(?string $date = null, ?int $batchId = null, ?string $grade = null): string
    {
        try {
            $date = $date ? Carbon::parse($date) : today();
            $dateKhmer = $this->formatKhmerDate($date);
            
            // Get current batch if not specified
            $batch = $batchId ? ProductionBatch::find($batchId) : ProductionBatch::current();
            
            if (!$batch) {
                return "មិនមាន Batch សកម្ម។ សូមបង្កើត Batch ថ្មីជាមុនសិន។";
            }
            
            // Get ALL books for grand total (unfiltered)
            $allBooks = Book::where('batch_id', $batch->id)->get();
            $allBooksTarget = $allBooks->sum('target_qty');
            $allBooksPrinted = $allBooks->sum('total_printed');
            $allBooksRemaining = max($allBooksTarget - $allBooksPrinted, 0);
            
            // Pre-aggregate today's prints per book
            $todayByBook = DailyPrint::whereIn('book_id', $allBooks->pluck('id'))
                ->whereDate('date', $date)
                ->selectRaw('book_id, SUM(printed_today) as qty')
                ->groupBy('book_id')
                ->pluck('qty', 'book_id');
                
            $allBooksToday = $todayByBook->sum();
            
            // Get books for display - filtered by grade if specified
            $booksQuery = Book::where('batch_id', $batch->id);
            
            // Filter by grade if specified
            if ($grade) {
                $booksQuery->where('grade', $grade);
            }
            
            $books = $booksQuery->ordered()->get();
                
            // Build report
            $report = $this->buildReportHeader($dateKhmer);
            
            if ($books->isEmpty()) {
                if ($grade) {
                    $report .= "មិនមានសៀវភៅសម្រាប់ {$grade} ទេ។\n\n";
                } else {
                    $report .= "គ្មាន\n\n";
                }
            } else {
                $booksByGrade = $books->groupBy('grade');
                
                $detailsText = "";
                $summariesText = "━━━━━【បូកសរុប】━━━━━\n";
                
                foreach ($booksByGrade as $gradeKey => $gradeBooks) {
                    // Calculate targets for all book types in this grade header
                    $typeTargets = [];
                    foreach ($gradeBooks as $book) {
                        $catLabel = $this->getBookCategoryLabel($book->title, $book->category ?? null);
                        $typeTargets[$catLabel] = ($typeTargets[$catLabel] ?? 0) + ($book->target_qty ?? 0);
                    }
                    
                    $targets = [];
                    foreach ($typeTargets as $label => $qty) {
                        if ($qty > 0) {
                            $targets[] = "{$label} = " . number_format($qty) . " ក្បាល";
                        }
                    }
                    
                    $gradeToday = 0;
                    $gradeTotal = 0;
                    $gradeRemaining = 0;
                    
                    // Books in this grade
                    $counter = 1;
                    $gradeItemsText = "";
                    foreach ($gradeBooks as $book) {
                        $todayPrinted = (int) ($todayByBook[$book->id] ?? 0);
                        
                        $totalPrinted = $book->total_printed ?? 0;
                        $targetQty = $book->target_qty ?? 0;
                        $remaining = max($targetQty - $totalPrinted, 0);
                        
                        // Accumulate summary totals unconditionally
                        $gradeToday += $todayPrinted;
                        $gradeTotal += $totalPrinted;
                        $gradeRemaining += $remaining;
                        
                        // Rule: Hide if remaining <= 0 AND today's production <= 0
                        if ($remaining <= 0 && $todayPrinted <= 0) {
                            continue;
                        }
                        
                        $gradeItemsText .= "{$counter}/ {$book->title}\n";
                        
                        // Show today's quantity if completed today
                        if ($todayPrinted > 0) {
                            $gradeItemsText .= "សម្រេចបានថ្ងៃនេះ៖ " . number_format($todayPrinted) . " ក្បាល\n";
                        }
                        
                        $gradeItemsText .= "សរុបមុន និងក្រោយ៖ " . number_format($totalPrinted) . " ក្បាល\n";
                        
                        // Show remaining only if there's work left
                        if ($remaining > 0) {
                            $gradeItemsText .= "នៅខ្វះសរុប៖ " . number_format($remaining) . " ក្បាល\n";
                        }
                        
                        $gradeItemsText .= "\n";
                        $counter++;
                    }
                    
                    // Only output level header if there are active items to show in details
                    if (!empty($gradeItemsText)) {
                        $detailsText .= "***សៀវភៅ {$gradeKey}\n";
                        if (!empty($targets)) {
                            $detailsText .= implode(' / ', $targets) . "\n\n";
                        } else {
                            $detailsText .= "\n";
                        }
                        $detailsText .= $gradeItemsText;
                    }
                    
                    // Grade summary (always show for tracked grades)
                    $gradeTarget = $gradeBooks->sum('target_qty');
                    $summariesText .= "បូកសរុប {$gradeKey}\n";
                    $summariesText .= "ចំនួន Order សរុប៖ " . number_format($gradeTarget) . " ក្បាល\n";
                    $summariesText .= "សរុបមុន និងក្រោយ៖ " . number_format($gradeTotal) . " ក្បាល\n";
                    $summariesText .= "នៅខ្វះសរុប៖ " . number_format($gradeRemaining) . " ក្បាល\n\n";
                }
                
                $report .= $detailsText;
                $report .= $summariesText;
            }
            
            // Grand total
            $report .= "━━【បូកសរុបការងារបោះពុម្ព】━━\n\n";
            if ($grade) {
                $report .= "🔍 លម្អិតខាងលើ៖ {$grade} | សរុបទាំងអស់ខាងក្រោម៖ គ្រប់ Level\n\n";
            }
            $report .= "សម្រេចបានសរុបទាំងអស់ថ្ងៃនេះ៖ " . number_format($allBooksToday) . " ក្បាល\n";
            $report .= "សរុបការងារបោះពុម្ពរួច៖ " . number_format($allBooksPrinted) . " ក្បាល\n";
            if ($allBooksRemaining > 0) {
                $report .= "នៅខ្វះសរុប៖ " . number_format($allBooksRemaining) . " ក្បាល\n\n";
            } else {
                $report .= "ការងារបានសម្រចរួចរាល់\n\n";
            }
            
            $report .= $this->buildReportFooter();
            
            return $report;
            
        } catch (\Exception $e) {
            \Log::error('Error generating daily report: ' . $e->getMessage());
            return "មានបញ្ហាក្នុងការបង្កើតរបាយការណ៍៖ " . $e->getMessage();
        }
    }



    /**
     * Generate compact report for Telegram (shorter format)
     */
    public function generateCompactReport(?string $date = null, ?int $batchId = null, ?string $grade = null): string
    {
        try {
            $date = $date ? Carbon::parse($date) : today();
            $batch = $batchId ? ProductionBatch::find($batchId) : ProductionBatch::current();
            
            if (!$batch) {
                return "មិនមាន Batch សកម្ម។";
            }
            
            $report = "📊 Production Report\n";
            $report .= "Date: " . $date->format('d/m/Y') . "\n";
            $report .= "Batch: {$batch->name}\n";
            if ($grade) {
                $report .= "Filter: {$grade}\n";
            }
            $report .= "\n";
            
            // Get ALL books for grand total (unfiltered)
            $allBooks = Book::where('batch_id', $batch->id)->get();
            $allBooksTotal = $allBooks->sum('total_printed');
            $allBooksTarget = $allBooks->sum('target_qty');
            $allBooksRemaining = max($allBooksTarget - $allBooksTotal, 0);
            $overallPercent = $allBooksTarget > 0 ? round(($allBooksTotal / $allBooksTarget) * 100, 1) : 0;
            
            // Pre-aggregate today's prints per book in one query (avoids N+1)
            $todayByBook = DailyPrint::whereIn('book_id', $allBooks->pluck('id'))
                ->whereDate('date', $date)
                ->selectRaw('book_id, SUM(printed_today) as qty')
                ->groupBy('book_id')
                ->pluck('qty', 'book_id');
            
            $allBooksToday = $todayByBook->sum();
            
            // Get books for display - filtered by grade if specified
            $booksQuery = Book::where('batch_id', $batch->id);
            
            if ($grade) {
                $booksQuery->where('grade', $grade);
            }
            
            $books = $booksQuery->ordered()->get();
                
            if ($books->isEmpty()) {
                if ($grade) {
                    $report .= "មិនមានសៀវភៅសម្រាប់ {$grade} ទេ។\n\n";
                } else {
                    $report .= "មិនមានសៀវភៅក្នុង Batch នេះទេ។\n\n";
                }
            } else {
                $booksByGrade = $books->groupBy('grade');
                
                foreach ($booksByGrade as $gradeKey => $gradeBooks) {
                    $gradeTotal = $gradeBooks->sum('total_printed') ?? 0;
                    $gradeTarget = $gradeBooks->sum('target_qty') ?? 0;
                    $gradeRemaining = max($gradeTarget - $gradeTotal, 0);
                    $gradePercent = $gradeTarget > 0 ? round(($gradeTotal / $gradeTarget) * 100) : 0;
                    
                    $report .= "• {$gradeKey}\n";
                    $report .= number_format($gradeTotal) . " / " . number_format($gradeTarget) . " ({$gradePercent}%) | Remaining: " . number_format($gradeRemaining) . "\n\n";
                }
            }
            
            // Grand total
            $report .= "────────────────────\n\n";
            $report .= "Completed : " . number_format($allBooksTotal) . " / " . number_format($allBooksTarget) . " ({$overallPercent}%)\n";
            $report .= "Remaining : " . number_format($allBooksRemaining) . " Books\n";
            
            return $report;
            
        } catch (\Exception $e) {
            \Log::error('Error generating compact report: ' . $e->getMessage());
            return "មានបញ្ហាក្នុងការបង្កើតរបាយការណ៍៖ " . $e->getMessage();
        }
    }



    /**
     * Build books section for a collection of books grouped by grade.
     * $todayByBook is a [book_id => today_qty] map to avoid per-book queries.
     */
    private function buildBooksSection($booksByGrade, $todayByBook): string
    {
        $section = "";
        
        foreach ($booksByGrade as $gradeKey => $gradeBooks) {
            // Grade header - show quantities for all book types
            $typeTargets = [];
            foreach ($gradeBooks as $book) {
                $catLabel = $this->getBookCategoryLabel($book->title, $book->category ?? null);
                $typeTargets[$catLabel] = ($typeTargets[$catLabel] ?? 0) + ($book->target_qty ?? 0);
            }
            
            $targets = [];
            foreach ($typeTargets as $label => $qty) {
                if ($qty > 0) {
                    $targets[] = "{$label} = " . number_format($qty) . " ក្បាល";
                }
            }
            
            $section .= "សៀវភៅ {$gradeKey}\n";
            if (!empty($targets)) {
                $section .= implode(' / ', $targets) . "\n\n";
            }
            
            $gradeToday = 0;
            $gradeTotal = 0;
            $gradeRemaining = 0;
            
            // Books in this grade
            $counter = 1;
            foreach ($gradeBooks as $book) {
                // Look up today's prints from the pre-built map (no query here)
                $todayPrinted = (int) ($todayByBook[$book->id] ?? 0);
                
                $totalPrinted = $book->total_printed ?? 0;
                $targetQty = $book->target_qty ?? 0;
                $remaining = max($targetQty - $totalPrinted, 0);
                
                $section .= "{$counter}/ {$book->title}\n";
                
                // Always show today's quantity
                if ($todayPrinted > 0) {
                    $section .= "សម្រេចបានថ្ងៃនេះ៖ " . number_format($todayPrinted) . " ក្បាល\n";
                }
                
                $section .= "សរុបមុន និងក្រោយ៖ " . number_format($totalPrinted) . " ក្បាល\n";
                
                // Only show remaining if there's still work to do
                if ($remaining > 0) {
                    $section .= "នៅខ្វះសរុប៖ " . number_format($remaining) . " ក្បាល\n";
                }
                
                $section .= "\n";
                
                $gradeToday += $todayPrinted;
                $gradeTotal += $totalPrinted;
                $gradeRemaining += $remaining;
                $counter++;
            }
            
            // Grade summary
            $gradeTarget = $gradeBooks->sum('target_qty');
            $section .= $this->buildSectionDivider('បូកសរុប');
            $section .= "បូកសរុប {$gradeKey}\n";
            $section .= "ចំនួន Order សរុប៖ " . number_format($gradeTarget) . " ក្បាល\n";
            $section .= "សរុបមុន និងក្រោយ៖ " . number_format($gradeTotal) . " ក្បាល\n";
            $section .= "នៅខ្វះសរុប៖ " . number_format($gradeRemaining) . " ក្បាល\n\n";
        }
        
        return $section;
    }

    /**
     * Build report header
     */
    private function buildReportHeader(string $dateKhmer): string
    {
        $header = "សូមគោរពរាយការណ៍ជូនឯកឧត្តមបណ្ឌិតឯកឧត្តម លោកជំទាវ និងសមាជិកក្រុមការងារ\n";
        $header .= "{$dateKhmer}\n\n";
        $header .= "ក្រុមការងារខ្ញុំ សូមគោរពរាយការណ៍អំពីស្ថានភាពការងារបោះពុម្ពសៀវភៅ ដូចខាងក្រោម៖\n\n";
        return $header;
    }

    /**
     * Build section divider
     */
    private function buildSectionDivider(string $title): string
    {
        return "━━━━━━━━━━━━━━━━━━【{$title}】━━━━━━━━━━━━━━━━━━\n";
    }

    /**
     * Build report footer
     */
    private function buildReportFooter(): string
    {
        return "សូមគោរពអរគុណ 🙏\n";
    }

    /**
     * Format date in Khmer
     */
    private function formatKhmerDate(Carbon $date): string
    {
        $day = $this->numberToKhmer($date->day);
        $year = $this->numberToKhmer($date->year);
        
        $months = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា',
            5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា',
            9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        
        $month = $months[$date->month];
        
        return "ថ្ងៃទី {$day} ខែ{$month} ឆ្នាំ {$year}";
    }

    /**
     * Convert number to Khmer numerals
     */
    private function numberToKhmer($number): string
    {
        $khmerNumerals = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
        $numStr = (string) $number;
        $result = '';
        
        for ($i = 0; $i < strlen($numStr); $i++) {
            $digit = $numStr[$i];
            $result .= $khmerNumerals[$digit];
        }
        
        return $result;
    }

    /**
     * Send report to Telegram
     */
    public function sendToTelegram(string $report, ?int $groupId = null): bool
    {
        // If no group specified, send to all active groups
        if (!$groupId) {
            $groups = \App\Models\TelegramGroup::where('status', 'active')->get();
            if ($groups->isEmpty()) {
                return false;
            }
            foreach ($groups as $group) {
                \App\Jobs\SendTelegramMessageJob::dispatch($group->chat_id, $report, $group->message_thread_id);
            }
            return true;
        }
        
        // Send to specific group
        $group = \App\Models\TelegramGroup::find($groupId);
        if ($group) {
            \App\Jobs\SendTelegramMessageJob::dispatch($group->chat_id, $report, $group->message_thread_id);
            return true;
        }
        
        return false;
    }

    /**
     * Categorize book title or category dynamically for grade targets header
     */
    private function getBookCategoryLabel(string $title, ?string $category = null): string
    {
        $titleLower = strtolower($title);
        if (str_contains($titleLower, 'textbook')) return 'Textbook';
        if (str_contains($titleLower, 'workbook')) return 'Workbook';
        if (str_contains($titleLower, 'song')) return 'Song';
        if (str_contains($titleLower, 'forktale') || str_contains($titleLower, 'folktale')) return 'Folktale';
        if (str_contains($titleLower, 'eloquence')) return 'Eloquence';
        if (str_contains($titleLower, 'flashcard')) return 'Flashcard';
        if (str_contains($titleLower, 'guidebook')) return 'Guidebook';

        if (!empty($category)) {
            return ucfirst(trim($category));
        }

        $parts = explode(' ', trim($title));
        $last = end($parts);
        return !empty($last) ? ucfirst($last) : 'Other';
    }
}
