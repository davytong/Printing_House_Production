<?php

namespace App\Services;

use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;

class ExcelExportService
{
    /**
     * Export production report (books with progress)
     */
    public function exportProductionReport($books, $filename = 'production_report.xlsx')
    {
        $writer = new Writer();
        $writer->openToBrowser($filename);

        // Header row
        $headerStyle = (new Style())->setFontBold();
        
        $headers = [
            'Book Code',
            'Title', 
            'Category',
            'Target',
            'Printed',
            'Remaining',
            'Progress %',
            'Grade',
            'Status'
        ];

        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(Row::fromCells($headerCells, $headerStyle));

        // Data rows
        foreach ($books as $book) {
            $remaining = $book->target_qty - $book->total_printed;
            $progress = $book->target_qty > 0 ? round(($book->total_printed / $book->target_qty) * 100, 1) : 0;
            
            $status = 'Pending';
            if ($book->total_printed >= $book->target_qty && $book->target_qty > 0) {
                $status = 'Complete';
            } elseif ($book->total_printed > 0) {
                $status = 'In Progress';
            }

            $data = [
                $book->code ?? '',
                $book->title,
                $book->category,
                $book->target_qty,
                $book->total_printed,
                $remaining,
                $progress . '%',
                $book->grade ?? '-',
                $status
            ];

            $cells = array_map(fn($d) => Cell::fromValue($d), $data);
            $writer->addRow(Row::fromCells($cells));
        }

        $writer->close();
    }

    /**
     * Export stock movements report
     */
    public function exportStockMovements($movements, $filename = 'stock_movements.xlsx')
    {
        $writer = new Writer();
        $writer->openToBrowser($filename);

        // Header
        $headerStyle = (new Style())->setFontBold();
        
        $headers = [
            'Date',
            'Type',
            'Material',
            'Quantity',
            'Unit',
            'From',
            'To',
            'Notes',
            'Created'
        ];

        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(Row::fromCells($headerCells, $headerStyle));

        // Data rows
        foreach ($movements as $movement) {
            $typeMap = [
                'in' => 'In',
                'out' => 'Out',
                'adjust' => 'Adjust',
                'transfer' => 'Transfer'
            ];

            $data = [
                $movement->movement_date,
                $typeMap[$movement->type] ?? $movement->type,
                $movement->material->name ?? '-',
                $movement->quantity,
                $movement->material->unit ?? '-',
                $movement->from_location ?? '-',
                $movement->to_location ?? '-',
                $movement->notes ?? '-',
                $movement->created_at->format('Y-m-d H:i')
            ];

            $cells = array_map(fn($d) => Cell::fromValue($d), $data);
            $writer->addRow(Row::fromCells($cells));
        }

        $writer->close();
    }

    /**
     * Export purchase orders report
     */
    public function exportPurchaseOrders($purchaseOrders, $filename = 'purchase_orders.xlsx')
    {
        $writer = new Writer();
        $writer->openToBrowser($filename);

        // Header
        $headerStyle = (new Style())->setFontBold();
        
        $headers = [
            'PO Number',
            'Supplier',
            'Order Date',
            'Expected Date',
            'Currency',
            'Total',
            'Status',
            'Notes'
        ];

        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(Row::fromCells($headerCells, $headerStyle));

        // Data rows
        foreach ($purchaseOrders as $po) {
            $statusMap = [
                'draft' => 'Draft',
                'sent' => 'Sent',
                'partially_received' => 'Partial',
                'received' => 'Received',
                'cancelled' => 'Cancelled'
            ];

            $data = [
                $po->po_number ?? '-',
                $po->supplier->name ?? '-',
                $po->order_date,
                $po->expected_date ?? '-',
                $po->currency,
                number_format($po->total_amount, 2),
                $statusMap[$po->status] ?? $po->status,
                $po->notes ?? '-'
            ];

            $cells = array_map(fn($d) => Cell::fromValue($d), $data);
            $writer->addRow(Row::fromCells($cells));
        }

        $writer->close();
    }

    /**
     * Export materials stock report
     */
    public function exportMaterialsStock($materials, $filename = 'materials_stock.xlsx')
    {
        $writer = new Writer();
        $writer->openToBrowser($filename);

        // Header
        $headerStyle = (new Style())->setFontBold();
        
        $headers = [
            'Code',
            'Name',
            'Category',
            'Sub Type',
            'Current Stock',
            'Min Level',
            'Unit',
            'Status',
            'Location'
        ];

        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(Row::fromCells($headerCells, $headerStyle));

        // Data rows
        foreach ($materials as $material) {
            $stockStatus = 'OK';
            if ($material->current_stock <= $material->min_stock_level) {
                $stockStatus = 'Low';
            }

            $categoryMap = [
                'paper' => 'Paper',
                'film' => 'Film',
                'consumable' => 'Consumable'
            ];

            $data = [
                $material->code ?? '',
                $material->name,
                $categoryMap[$material->category] ?? $material->category,
                $material->sub_type ?? '-',
                $material->current_stock,
                $material->min_stock_level,
                $material->unit,
                $stockStatus,
                $material->location ?? '-'
            ];

            $cells = array_map(fn($d) => Cell::fromValue($d), $data);
            $writer->addRow(Row::fromCells($cells));
        }

        $writer->close();
    }

    /**
     * Export print requests report
     */
    public function exportPrintRequests($printRequests, $filename = 'print_requests.xlsx')
    {
        $writer = new Writer();
        $writer->openToBrowser($filename);

        // Header
        $headerStyle = (new Style())->setFontBold();
        
        $headers = [
            'Request Code',
            'Client',
            'Date',
            'Priority',
            'Status',
            'Items',
            'Notes'
        ];

        $headerCells = array_map(fn($h) => Cell::fromValue($h), $headers);
        $writer->addRow(Row::fromCells($headerCells, $headerStyle));

        // Data rows
        foreach ($printRequests as $pr) {
            $priorityMap = [
                'low' => 'Low',
                'normal' => 'Normal',
                'high' => 'High',
                'urgent' => 'Urgent'
            ];

            $statusMap = [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ];

            $data = [
                $pr->request_code ?? '-',
                $pr->client_name ?? '-',
                $pr->request_date,
                $priorityMap[$pr->priority] ?? $pr->priority,
                $statusMap[$pr->status] ?? $pr->status,
                $pr->items->count(),
                $pr->notes ?? '-'
            ];

            $cells = array_map(fn($d) => Cell::fromValue($d), $data);
            $writer->addRow(Row::fromCells($cells));
        }

        $writer->close();
    }
}
