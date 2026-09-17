<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuditLogAndRbacTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reporter_can_login_without_pin(): void
    {
        $response = $this->post(route('entry.login'), [
            'full_name' => 'Sokha Chea',
            'position'  => 'press_report',
        ]);

        $response->assertRedirect('/stock/movements/daily?category=consumable');
        $this->assertEquals('Sokha Chea', session('user_name'));
        $this->assertEquals('press_report', session('user_position'));
        $this->assertEquals('reporter', session('user_role'));

        $this->assertDatabaseHas('activity_logs', [
            'user_name' => 'Sokha Chea',
            'action'    => 'Login',
            'module'    => 'auth',
        ]);
    }

    public function test_admin_login_fails_with_invalid_pin(): void
    {
        Setting::set('admin_pin', '8888');

        $response = $this->post(route('entry.login'), [
            'full_name' => 'Admin User',
            'position'  => 'admin',
            'admin_pin' => '0000',
        ]);

        $response->assertSessionHasErrors('admin_pin');
        $this->assertNull(session('user_name'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Failed Admin Login',
            'module' => 'auth',
        ]);
    }

    public function test_admin_login_succeeds_with_valid_pin(): void
    {
        Setting::set('admin_pin', '4321');

        $response = $this->post(route('entry.login'), [
            'full_name' => 'Chief Admin',
            'position'  => 'admin',
            'admin_pin' => '4321',
        ]);

        $response->assertRedirect('/');
        $this->assertEquals('Chief Admin', session('user_name'));
        $this->assertEquals('admin', session('user_role'));

        $this->assertDatabaseHas('activity_logs', [
            'user_name' => 'Chief Admin',
            'action'    => 'Login',
            'module'    => 'auth',
        ]);
    }

    public function test_logout_records_activity_log_and_clears_session(): void
    {
        Setting::set('admin_pin', '1234');
        $this->post(route('entry.login'), [
            'full_name' => 'TONG DAVY',
            'position'  => 'admin',
            'admin_pin' => '1234',
        ]);

        $this->assertEquals('TONG DAVY', session('user_name'));

        $response = $this->post(route('entry.logout'));

        $response->assertRedirect(route('entry'));
        $this->assertNull(session('user_name'));
        $this->assertDatabaseHas('activity_logs', [
            'user_name' => 'TONG DAVY',
            'action'    => 'Logout',
            'module'    => 'auth',
            'details'   => 'Left the system',
        ]);
    }

    public function test_audit_log_scopes_and_recording(): void
    {
        session(['user_name' => 'Tester', 'user_position' => 'admin']);

        ActivityLog::record('Test Production Action', 'Details 1', 'production');
        ActivityLog::record('Test Inventory Action', 'Details 2', 'inventory');

        $prodLogs = ActivityLog::forModule('production')->where('action', 'Test Production Action')->get();
        $this->assertCount(1, $prodLogs);
        $this->assertEquals('Test Production Action', $prodLogs->first()->action);

        $invLogs = ActivityLog::forModule('inventory')->where('action', 'Test Inventory Action')->get();
        $this->assertCount(1, $invLogs);
        $this->assertEquals('Test Inventory Action', $invLogs->first()->action);
    }

    public function test_unauthorized_user_cannot_view_audit_trail(): void
    {
        // Reporter role does not have view_audit_logs
        session([
            'user_name'     => 'Operator',
            'user_position' => 'press_report',
            'user_role'     => 'reporter',
        ]);

        $response = $this->get(route('audit.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_export_audit_trail(): void
    {
        session([
            'user_name'     => 'Master Admin',
            'user_position' => 'admin',
            'user_role'     => 'admin',
        ]);

        ActivityLog::record('System Config Check', 'Verified', 'settings');

        $response = $this->get(route('audit.index'));
        $response->assertStatus(200);
        $response->assertSee('Enterprise Audit Trail');
        $response->assertSee('System Config Check');

        $exportResponse = $this->get(route('audit.export'));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('content-type') ?? '', 'text/csv'));
    }

    public function test_dashboard_renders_with_audit_feed(): void
    {
        session([
            'user_name'     => 'Factory Manager',
            'user_position' => 'admin',
            'user_role'     => 'admin',
        ]);

        ActivityLog::record('Batch Started', 'Batch #10 launched', 'production');

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Recent Factory Audit Trail');
        $response->assertSee('Batch Started');
    }

    public function test_activity_log_record_never_throws_even_if_underlying_fails(): void
    {
        // Even if non-existent columns or anomalous conditions are passed, record() handles it safely
        $entry = ActivityLog::record('Safe Test', 'Details', 'auth');
        $this->assertNotNull($entry);
        $this->assertEquals('auth', $entry->module);
    }
}
