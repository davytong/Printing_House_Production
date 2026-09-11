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
    public function generateDailyReport(?string $date = null, ?int $batchId = null, ?string $grade = null, string $audience = 'group'): string
    {
        try {
            $parsedDate = $date ? Carbon::parse($date) : today();

            if ($audience === 'both') {
                $report1 = $this->generateDailyReport($parsedDate->toDateString(), $batchId, $grade, 'group');
                $report2 = $this->generateDailyReport($parsedDate->toDateString(), $batchId, $grade, 'individual');
                return "════════════════════════════════════\n👥 ១. របាយការណ៍ក្នុងក្រុម (IN GROUP)\n════════════════════════════════════\n\n" . $report1 . "\n\n════════════════════════════════════\n👤 ២. របាយការណ៍ជូនឯកឧត្តមបណ្ឌិត (INDIVIDUAL TO HE)\n════════════════════════════════════\n\n" . $report2;
            }

            $dateKhmer = $this->formatKhmerDate($parsedDate);
            
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
                ->whereDate('date', $parsedDate)
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
            $report = $this->buildReportHeader($dateKhmer, $audience);
            
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
                $report .= "ការងារបានសម្រេចរួចរាល់\n\n";
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
    public function generateCompactReport(?string $date = null, ?int $batchId = null, ?string $grade = null, string $audience = 'group'): string
    {
        try {
            $parsedDate = $date ? Carbon::parse($date) : today();

            if ($audience === 'both') {
                $report1 = $this->generateCompactReport($parsedDate->toDateString(), $batchId, $grade, 'group');
                $report2 = $this->generateCompactReport($parsedDate->toDateString(), $batchId, $grade, 'individual');
                return "════════════════════════════════════\n👥 ១. របាយការណ៍ក្នុងក្រុម (IN GROUP)\n════════════════════════════════════\n\n" . $report1 . "\n\n════════════════════════════════════\n👤 ២. របាយការណ៍ជូនឯកឧត្តមបណ្ឌិត (INDIVIDUAL TO HE)\n════════════════════════════════════\n\n" . $report2;
            }

            $dateKhmer = $this->formatKhmerDate($parsedDate);
            $batch = $batchId ? ProductionBatch::find($batchId) : ProductionBatch::current();
            
            if (!$batch) {
                return "មិនមាន Batch សកម្ម។";
            }
            
            // Get ALL books for grand total
            $allBooks = Book::where('batch_id', $batch->id)->get();
            $todayByBook = DailyPrint::whereIn('book_id', $allBooks->pluck('id'))
                ->whereDate('date', $parsedDate)
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
                
            $report = $this->buildReportHeader($dateKhmer, $audience);

            if ($books->isEmpty()) {
                if ($grade) {
                    $report .= "មិនមានសៀវភៅសម្រាប់ {$grade} ទេ។\n\n";
                } else {
                    $report .= "មិនមានសៀវភៅក្នុង Batch នេះទេ។\n\n";
                }
            } else {
                $booksByGrade = $books->groupBy('grade');
                $report .= "━━━━━【បូកសរុប】━━━━━\n";
                
                $grandTotal = 0;
                $grandTarget = 0;
                $grandRemaining = 0;

                foreach ($booksByGrade as $gradeKey => $gradeBooks) {
                    $gradeTotal = $gradeBooks->sum('total_printed') ?? 0;
                    $gradeTarget = $gradeBooks->sum('target_qty') ?? 0;
                    $gradeRemaining = max($gradeTarget - $gradeTotal, 0);

                    $report .= "បូកសរុប {$gradeKey}\n";
                    $report .= "ចំនួន Order សរុប៖ " . number_format($gradeTarget) . " ក្បាល\n";
                    $report .= "សរុបមុន និងក្រោយ៖ " . number_format($gradeTotal) . " ក្បាល\n";
                    $report .= "នៅខ្វះសរុប៖ " . number_format($gradeRemaining) . " ក្បាល\n\n";

                    $grandTotal += $gradeTotal;
                    $grandTarget += $gradeTarget;
                    $grandRemaining += $gradeRemaining;
                }
                
                // Grand total
                $report .= "━━【បូកសរុបការងារបោះពុម្ព】━━\n\n";
                $report .= "សម្រេចបានសរុបទាំងអស់ថ្ងៃនេះ៖ " . number_format($allBooksToday) . " ក្បាល\n";
                $report .= "សរុបការងារបោះពុម្ពរួច៖ " . number_format($grandTotal) . " ក្បាល\n";
                if ($grandRemaining > 0) {
                    $report .= "នៅខ្វះសរុប៖ " . number_format($grandRemaining) . " ក្បាល\n\n";
                } else {
                    $report .= "ការងារបានសម្រេចរួចរាល់\n\n";
                }
            }

            $report .= $this->buildReportFooter();
            return $report;
        } catch (\Exception $e) {
            \Log::error('Error generating compact report: ' . $e->getMessage());
            return "មានបញ្ហាក្នុងការបង្កើតរបាយការណ៍៖ " . $e->getMessage();
        }
    }

    /**
     * Generate visual ASCII progress bar
     */
    private function getAsciiProgressBar($percent, int $totalBars = 10): string
    {
        $filled = (int) round(((float) $percent / 100) * $totalBars);
        $filled = max(0, min($totalBars, $filled));
        $empty = $totalBars - $filled;
        return '[' . str_repeat('█', $filled) . str_repeat('░', $empty) . ']';
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
    private function buildReportHeader(string $dateKhmer, string $audience = 'group'): string
    {
        $greeting = ($audience === 'individual')
            ? "សូមគោរពរាយការណ៍ជូនឯកឧត្តមបណ្ឌិត\n"
            : "សូមគោរពរាយការណ៍ជូនឯកឧត្តមបណ្ឌិត ឯកឧត្តម លោកជំទាវ និងសមាជិកក្រុមការងារ\n";
            
        $header = "{$greeting}{$dateKhmer}\n\n";
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
        $day = sprintf('%02d', $date->day);
        $year = $date->year;
        
        $months = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា',
            5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា',
            9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        
        $month = $months[$date->month];
        
        return "ថ្ងៃទី {$day} ខែ {$month} ឆ្នាំ {$year}";
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
     * Send report to Telegram (with optional one-touch tap-to-copy HTML formatting)
     * Accepts a single string or an array of message strings (e.g. for sending both options at same time)
     */
    public function sendToTelegram(string|array $report, ?int $groupId = null, bool $isMonospace = true): bool
    {
        $messages = is_array($report) ? $report : [$report];

        // If no group specified, send to all active groups
        if (!$groupId) {
            $groups = \App\Models\TelegramGroup::where('status', 'active')->get();
            if ($groups->isEmpty()) {
                return false;
            }
            foreach ($groups as $group) {
                foreach ($messages as $msg) {
                    if (empty(trim($msg))) continue;
                    $formattedMessage = $isMonospace 
                        ? "<pre>" . htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>"
                        : $msg;
                    $parseMode = $isMonospace ? 'HTML' : null;
                    \App\Jobs\SendTelegramMessageJob::dispatch($group->chat_id, $formattedMessage, $group->message_thread_id, $parseMode);
                }
            }
            return true;
        }
        
        // Send to specific group
        $group = \App\Models\TelegramGroup::find($groupId);
        if ($group) {
            foreach ($messages as $msg) {
                if (empty(trim($msg))) continue;
                $formattedMessage = $isMonospace 
                    ? "<pre>" . htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>"
                    : $msg;
                $parseMode = $isMonospace ? 'HTML' : null;
                \App\Jobs\SendTelegramMessageJob::dispatch($group->chat_id, $formattedMessage, $group->message_thread_id, $parseMode);
            }
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
