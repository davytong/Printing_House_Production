<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Setting;
use App\Models\TelegramGroup;
use App\Models\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    public function test_normal_stock_returns_no_low_stock_alert()
    {
        $material = Material::create([
            'name'       => 'A4 Paper',
            'category'   => 'paper',
            'unit'       => 'ream',
            'min_stock'  => 5,
            'status'     => 'active',
        ]);

        $response = $this->postJson(route('stock.low-stock.check'), [
            'items' => [
                ['material_id' => $material->id, 'current_stock' => 10],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'has_low_stock' => false,
                'count'         => 0,
            ]);
    }

    public function test_low_stock_detection_returns_item_details()
    {
        $material = Material::create([
            'name'           => 'Cleaning Sponge',
            'name_km'        => 'សាប៊ូជូត',
            'category'       => 'consumable',
            'unit'           => 'piece',
            'min_stock'      => 3,
            'critical_stock' => 1,
            'status'         => 'active',
        ]);

        $response = $this->postJson(route('stock.low-stock.check'), [
            'items' => [
                ['material_id' => $material->id, 'current_stock' => 1],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'has_low_stock' => true,
                'count'         => 1,
            ]);

        $this->assertEquals('CRITICAL', $response->json('items.0.status'));
    }

    public function test_low_stock_preview_generation()
    {
        $material = Material::create([
            'name'      => 'Cleaning Sponge',
            'category'  => 'consumable',
            'unit'      => 'piece',
            'min_stock' => 3,
            'status'    => 'active',
        ]);

        $group = TelegramGroup::create([
            'chat_id' => '-100123456789',
            'name'    => 'Production Leaders',
        ]);

        Setting::set('low_stock_notification_groups', json_encode([$group->id]));

        $response = $this->postJson(route('stock.low-stock.preview'), [
            'items' => [
                [
                    'name'                => 'Cleaning Sponge',
                    'category'            => 'consumable',
                    'unit'                => 'piece',
                    'current_stock'       => 1,
                    'low_stock_threshold' => 3,
                ],
            ],
            'report_date' => '2026-08-21',
            'performed_by' => 'Tester',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['preview_text', 'destination_groups']);

        $this->assertStringContainsString('Cleaning Sponge', $response->json('preview_text'));
        $this->assertStringContainsString('2026', $response->json('preview_text'));
    }

    public function test_prevent_duplicate_notification_sending()
    {
        $group = TelegramGroup::create([
            'chat_id' => '-100987654321',
            'name'    => 'Warehouse Group',
        ]);

        LowStockNotification::create([
            'report_date'            => '2026-08-21',
            'category'               => 'paper',
            'destination_group_id'   => $group->id,
            'destination_group_name' => $group->displayLabel(),
            'items_count'            => 1,
            'items_payload'          => [],
            'message'                => 'Test message',
            'status'                 => 'SENT',
            'sent_by'                => 'Tester',
            'sent_at'                => now(),
        ]);

        $response = $this->postJson(route('stock.low-stock.send'), [
            'items' => [
                [
                    'name'                => 'Paper A4',
                    'category'            => 'paper',
                    'unit'                => 'ream',
                    'current_stock'       => 2,
                    'low_stock_threshold' => 5,
                ],
            ],
            'group_ids'   => [$group->id],
            'report_date' => '2026-08-21',
            'category'    => 'paper',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('SKIPPED', $response->json('logs.0.status'));
    }

    public function test_admin_settings_update()
    {
        $response = $this->post(route('stock.low-stock.update-settings'), [
            'low_stock_alert_enabled'     => '1',
            'low_stock_default_threshold' => '4.5',
            'low_stock_default_critical'  => '1.5',
            'low_stock_message_template'  => 'Custom template {{items_list}}',
        ]);

        $response->assertRedirect();

        $this->assertEquals('1', Setting::get('low_stock_alert_enabled'));
        $this->assertEquals('4.5', Setting::get('low_stock_default_threshold'));
        $this->assertEquals('1.5', Setting::get('low_stock_default_critical'));
    }
}
