<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\StockMovement;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class TelegramStockOutMessageFormatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_telegram_message_uses_professional_enterprise_format_without_toy_emojis(): void
    {
        $mat1 = Material::create([
            'code'      => 'TEST-CLEAN-1',
            'name'      => 'Plate Cleaner Pro',
            'category'  => 'consumable',
            'unit'      => 'bottle',
            'min_stock' => 2,
            'status'    => 'active',
        ]);
        StockMovement::create([
            'material_id'   => $mat1->id,
            'type'          => 'in',
            'quantity'      => 10,
            'movement_date' => now()->toDateString(),
        ]);

        $mat2 = Material::create([
            'code'      => 'TEST-CLEAN-2',
            'name'      => 'Cyan Ink Pro',
            'category'  => 'consumable',
            'unit'      => 'can',
            'min_stock' => 2,
            'status'    => 'active',
        ]);
        StockMovement::create([
            'material_id'   => $mat2->id,
            'type'          => 'in',
            'quantity'      => 8,
            'movement_date' => now()->toDateString(),
        ]);

        // Mock TelegramService to capture the message sent
        $sentMessage = null;
        $mockTg = Mockery::mock(TelegramService::class);
        $mockTg->shouldReceive('sendMessage')
            ->andReturnUsing(function ($chatId, $message, $threadId, $parseMode) use (&$sentMessage) {
                $sentMessage = $message;
                return true;
            });
        $this->app->instance(TelegramService::class, $mockTg);

        \App\Models\Setting::set('stock_out_chat_id', '-1001234567890');

        $response = $this->postJson('/api/telegram/app/stock-out', [
            'items' => [
                ['material_id' => $mat1->id, 'quantity' => 2],
                ['material_id' => $mat2->id, 'quantity' => 1],
            ],
            'reason'        => 'Production',
            'performed_by'  => 'សាន ចន្ថា',
            'recorder_name' => 'សាន ចន្ថា (San Chantha)',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertNotNull($sentMessage, 'Telegram message was not sent.');

        // Verify professional enterprise format requested by user
        $this->assertStringContainsString('របាយការណ៍ដកស្តុកប្រើប្រាស់', $sentMessage);
        $this->assertStringContainsString('━━━━━━━━━━━━━━━', $sentMessage);
        $this->assertStringContainsString('លេខយោង:', $sentMessage);
        // Taker is alone in the header (NOT joined with recorder on the same line)
        $this->assertStringContainsString('<b>អ្នកដក:</b> សាន ចន្ថា' . "\n", $sentMessage);
        $this->assertStringNotContainsString('<b>អ្នកដក:</b> សាន ចន្ថា |', $sentMessage);
        // Recorder is in the footer (after last divider)
        $this->assertStringContainsString("<b>អ្នកកត់ត្រា:</b> សាន ចន្ថា (San Chantha)", $sentMessage);
        $this->assertStringContainsString('<b>គោលបំណង:</b> ប្រើប្រាស់ក្នុងការបោះពុម្ព', $sentMessage);
        $this->assertStringContainsString('សម្ភារៈដែលបានយកប្រើប្រាស់ (2 មុខ)', $sentMessage);
        $this->assertStringContainsString('01. Plate Cleaner Pro', $sentMessage);
        $this->assertStringContainsString('• យកប្រើប្រាស់: <b>2 bottle</b>', $sentMessage);
        $this->assertStringContainsString('• ស្តុកនៅសល់: <b>8 bottle</b>', $sentMessage);
        $this->assertStringContainsString('02. Cyan Ink Pro', $sentMessage);
        $this->assertStringContainsString('• យកប្រើប្រាស់: <b>1 can</b>', $sentMessage);
        $this->assertStringContainsString('• ស្តុកនៅសល់: <b>7 can</b>', $sentMessage);
        $this->assertStringContainsString('កំណត់ត្រាត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ', $sentMessage);
        // Recorder line appears BEFORE the auto-generated footer, AFTER the last divider
        $footerPos   = strpos($sentMessage, 'កំណត់ត្រាត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ');
        $recorderPos = strpos($sentMessage, 'អ្នកកត់ត្រា:');
        $this->assertNotFalse($recorderPos);
        $this->assertLessThan($footerPos, $recorderPos, 'Recorder line should appear before the auto-generated footer line');
        // Verify no inline separator between taker and recorder in the header
        $this->assertStringNotContainsString('|អ្នកកត់ត្រា', $sentMessage);

        // Verify absence of toy / messy emojis
        $this->assertStringNotContainsString('📦', $sentMessage);
        $this->assertStringNotContainsString('📋', $sentMessage);
        $this->assertStringNotContainsString('🎯', $sentMessage);
        $this->assertStringNotContainsString('👤', $sentMessage);
        $this->assertStringNotContainsString('📅', $sentMessage);
        $this->assertStringNotContainsString('🏷️', $sentMessage);
        $this->assertStringNotContainsString('🔖', $sentMessage);
        $this->assertStringNotContainsString('🤖', $sentMessage);
    }
}
