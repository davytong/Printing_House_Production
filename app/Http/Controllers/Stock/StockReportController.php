<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockReport;
use App\Models\TelegramGroup;
use App\Services\ImageService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ImageService $imageService,
    ) {}

    public function index(Request $request): View
    {
        $category = $request->query('category');

        $reports = StockReport::when($category, fn($q) => $q->where('category', $category))
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('stock.reports.index', compact('reports', 'category'));
    }

    public function create(Request $request): View
    {
        $category = $request->query('category'); // paper|film|offset|null
        $category = in_array($category, ['paper', 'film', 'offset', 'consumable'], true) ? $category : null;

        $summary        = $this->reportService->buildSummary($category);
        $telegramGroups = TelegramGroup::orderBy('name')->get();

        return view('stock.reports.create', compact('summary', 'telegramGroups', 'category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'report_date' => 'required|date',
            'category'    => 'nullable|in:paper,film,offset,ink',
            'title'       => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:2000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'created_by'  => 'nullable|string|max:100',
        ]);

        // Handle image upload with compression
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->store($request->file('image'));
        }

        $report = $this->reportService->createReport(
            $data['report_date'],
            $data['title'] ?? null,
            $data['notes'] ?? null,
            $imagePath,
            $data['created_by'] ?? null,
            $data['category'] ?? null,
        );

        return redirect()->route('stock.reports.show', $report)
            ->with('success', 'របាយការណ៍ Stock ត្រូវបានបង្កើត');
    }

    public function show(StockReport $stockReport): View
    {
        $telegramGroups = TelegramGroup::orderBy('name')->get();
        return view('stock.reports.show', compact('stockReport', 'telegramGroups'));
    }

    public function sendTelegram(Request $request, StockReport $stockReport): RedirectResponse
    {
        // Parse "chatId|threadId" composite value
        $rawChatId = $request->input('chat_id', '');
        if (str_contains($rawChatId, '|')) {
            [$chatId, $threadIdStr] = explode('|', $rawChatId, 2);
            $request->merge([
                'chat_id'           => $chatId,
                'message_thread_id' => $threadIdStr !== '' ? (int) $threadIdStr : null,
            ]);
        }

        $request->validate([
            'chat_id'           => 'required',
            'message_thread_id' => 'nullable|integer',
        ]);

        $success = $this->reportService->sendToTelegram(
            $stockReport,
            $request->input('chat_id'),
            $request->integer('message_thread_id') ?: null,
        );

        if ($success) {
            return back()->with('success', 'ផ្ញើរបាយការណ៍ទៅ Telegram បានជោគជ័យ');
        }

        return back()->with('error', 'ផ្ញើមិនបាន — សូមពិនិត្យ Bot Token និង Group');
    }

    public function destroy(StockReport $stockReport): RedirectResponse
    {
        if ($stockReport->image_path) {
            $this->imageService->delete($stockReport->image_path);
        }
        $stockReport->delete();
        return redirect()->route('stock.reports.index')
            ->with('success', 'របាយការណ៍ត្រូវបានលុប');
    }
}
