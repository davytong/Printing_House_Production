<?php

namespace Tests\Feature;

use App\Models\DailyReportSubmission;
use App\Models\ReportRequirement;
use App\Services\DailyReportTrackerService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DailyReportTrackingTest extends TestCase
{
    private DailyReportTrackerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up previous test submissions for fresh state
        DailyReportSubmission::query()->delete();

        // Seed test requirements
        (new \Database\Seeders\ReportRequirementSeeder)->run();

        // Fake all outbound Telegram HTTP requests so tests run offline safely
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 999]], 200),
        ]);

        $this->service = app(DailyReportTrackerService::class);
    }

    public function test_ignores_unrelated_messages_and_chatter(): void
    {
        // 1. Random chatter from unregistered user
        $msg1 = [
            'message_id' => 101,
            'from'       => ['id' => 99999999, 'first_name' => 'John'],
            'chat'       => ['id' => -100123456],
            'text'       => 'Good morning everyone! Machine started.',
        ];
        $this->assertFalse($this->service->processIncomingMessage($msg1));

        // 2. Chatter from registered staff without report tag
        $req = ReportRequirement::first();
        $msg2 = [
            'message_id' => 102,
            'from'       => ['id' => (int) $req->telegram_user_id, 'first_name' => 'Staff A'],
            'chat'       => ['id' => -100123456],
            'text'       => 'Where is the paper? We are going to lunch.',
        ];
        $this->assertFalse($this->service->processIncomingMessage($msg2));
    }

    public function test_records_on_time_submission(): void
    {
        $req = ReportRequirement::where('report_type', 'morning_1')->first();
        $this->assertNotNull($req);

        // Simulate time at 06:45 AM (before 07:00 AM deadline)
        $simulatedTime = Carbon::now('Asia/Phnom_Penh')->setTime(6, 45, 0);
        Carbon::setTestNow($simulatedTime);

        $reportText = "{$req->identifier_tag}\nសូមគោរពរាយការណ៍ជូនបង ពូ និងក្រុមការងារទាំងអស់\nកាលបរិច្ឆេទ : 17-09-2026 ព្រឹក\n* សៀវភៅ BIU CS ECO TX Wood Free\n* បោះពុម្ពបានចំនួន 11 កូន (4/4)";

        $msg = [
            'message_id' => 201,
            'from'       => ['id' => (int) $req->telegram_user_id, 'first_name' => 'Staff A'],
            'chat'       => ['id' => -100123456],
            'text'       => $reportText,
        ];

        $handled = $this->service->processIncomingMessage($msg);
        $this->assertTrue($handled);

        $submission = DailyReportSubmission::where('report_requirement_id', $req->id)
            ->where('report_date', $simulatedTime->toDateString())
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals('submitted', $submission->status);
        $this->assertEquals(0, $submission->late_minutes);
        $this->assertEquals(201, $submission->telegram_message_id);

        Carbon::setTestNow(); // Reset time
    }

    public function test_records_late_submission_with_correct_late_minutes(): void
    {
        $req = ReportRequirement::where('report_type', 'morning_2')->first(); // Deadline 15:10
        $this->assertNotNull($req);

        // Simulate submission at 15:27 (17 minutes late)
        $deadline      = $req->calculateDeadlineForDate(Carbon::now('Asia/Phnom_Penh'));
        $simulatedTime = $deadline->copy()->addMinutes(17);
        Carbon::setTestNow($simulatedTime);

        $reportText = "[Second Production Report]\nសូមគោរពរាយការណ៍\n* បោះពុម្ពបានចំនួន 3000 ក្បាល\n* ឥឡូវនេះកំពុងបោះពុម្ពបន្ត";

        $msg = [
            'message_id' => 301,
            'from'       => ['id' => (int) $req->telegram_user_id, 'first_name' => 'Staff B'],
            'chat'       => ['id' => -100123456],
            'text'       => $reportText,
        ];

        $handled = $this->service->processIncomingMessage($msg);
        $this->assertTrue($handled);

        $submission = DailyReportSubmission::where('report_requirement_id', $req->id)
            ->where('report_date', $simulatedTime->toDateString())
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals('late', $submission->status);
        $this->assertEquals(17, $submission->late_minutes);

        Carbon::setTestNow(); // Reset time
    }

    public function test_duplicate_submission_protection_policy(): void
    {
        $req = ReportRequirement::where('report_type', 'evening_3')->first(); // Deadline 23:50
        $this->assertNotNull($req);

        // 1st submission at 23:30 (On-time)
        $time1 = Carbon::now('Asia/Phnom_Penh')->setTime(23, 30, 0);
        Carbon::setTestNow($time1);

        $msg1 = [
            'message_id' => 401,
            'from'       => ['id' => (int) $req->telegram_user_id, 'first_name' => 'Staff C'],
            'chat'       => ['id' => -100123456],
            'text'       => "[Evening Production Report]\nFirst draft of evening report with complete details for today.",
        ];
        $this->service->processIncomingMessage($msg1);

        // 2nd corrected submission at 23:55 (after deadline)
        $time2 = Carbon::now('Asia/Phnom_Penh')->setTime(23, 55, 0);
        Carbon::setTestNow($time2);

        $msg2 = [
            'message_id' => 402,
            'from'       => ['id' => (int) $req->telegram_user_id, 'first_name' => 'Staff C'],
            'chat'       => ['id' => -100123456],
            'text'       => "[Evening Production Report]\nCorrected numbers for evening report. Final total 6500 copies.",
        ];
        $this->service->processIncomingMessage($msg2);

        // Verify only 1 row exists
        $submissionsCount = DailyReportSubmission::where('report_requirement_id', $req->id)
            ->where('report_date', $time1->toDateString())
            ->count();
        $this->assertEquals(1, $submissionsCount);

        $submission = DailyReportSubmission::where('report_requirement_id', $req->id)
            ->where('report_date', $time1->toDateString())
            ->first();

        // Under first_valid policy, on-time status and initial submitted_at are protected
        $this->assertEquals('submitted', $submission->status);
        $this->assertEquals(1, $submission->revision_count);
        $this->assertEquals(402, $submission->telegram_message_id);
        $this->assertStringContainsString('Corrected numbers', $submission->message_text);

        Carbon::setTestNow(); // Reset time
    }

    public function test_three_reporting_slots_are_independent(): void
    {
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        $req1 = ReportRequirement::where('report_type', 'morning_1')->first();
        $req2 = ReportRequirement::where('report_type', 'morning_2')->first();
        $req3 = ReportRequirement::where('report_type', 'evening_3')->first();

        // Staff submitting slot 1 should not satisfy slot 2 or slot 3
        $sub1 = DailyReportSubmission::updateOrCreate(
            ['report_requirement_id' => $req1->id, 'report_date' => $today],
            ['status' => 'submitted', 'submitted_at' => now(), 'telegram_user_id' => $req1->telegram_user_id, 'deadline_at' => now()]
        );

        $sub2 = DailyReportSubmission::where('report_requirement_id', $req2->id)->where('report_date', $today)->first();
        $sub3 = DailyReportSubmission::where('report_requirement_id', $req3->id)->where('report_date', $today)->first();

        $this->assertEquals('submitted', $sub1->status);
        $this->assertNotEquals('submitted', $sub2?->status);
        $this->assertNotEquals('submitted', $sub3?->status);
    }

    public function test_settings_page_and_maintenance_actions(): void
    {
        // 1. Settings page renders tracking maintenance section
        $response = $this->withSession(['user_name' => 'Admin', 'user_role' => 'admin'])->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Daily Report Tracking');

        // 2. Save settings with direct target dropdown (chat_id|thread_id)
        $saveResponse = $this->post('/settings/report-tracking', [
            'report_duplicate_policy' => 'latest_valid',
            'report_send_ack'         => '1',
            'report_alert_target'     => '-1003150870760|885',
        ]);
        $saveResponse->assertRedirect();
        $this->assertEquals('latest_valid', \App\Models\Setting::get('report_duplicate_policy'));
        $this->assertEquals('-1003150870760', \App\Models\Setting::get('report_alert_chat_id'));
        $this->assertEquals('885', (string)\App\Models\Setting::get('report_alert_thread_id'));

        // 3. Reset alerts maintenance action
        $resetResponse = $this->post('/settings/report-tracking/reset-alerts');
        $resetResponse->assertRedirect();

        // 4. Sync today records maintenance action
        $syncResponse = $this->post('/settings/report-tracking/sync');
        $syncResponse->assertRedirect();
    }

    public function test_daily_tracking_web_routes_render_successfully(): void
    {
        $response1 = $this->get('/reports/tracking');
        $response1->assertStatus(200);
        $response1->assertSee('Daily Report Tracking');

        $response2 = $this->get('/reports/requirements');
        $response2->assertStatus(200);
        $response2->assertSee('Report Requirements');
    }

    public function test_automatic_user_capture_and_fetch(): void
    {
        // 1. Test auto-capturing user from Telegram payload
        $captured = \App\Models\TelegramUser::capture([
            'id' => 999888777,
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'username'   => 'johndoe_press',
        ]);
        $this->assertNotNull($captured);
        $this->assertEquals('999888777', $captured->telegram_user_id);
        $this->assertEquals('John Doe', $captured->display_name);

        // 2. Test requirements page shows the captured user in dropdown
        $reqView = $this->get('/reports/requirements');
        $reqView->assertStatus(200);
        $reqView->assertSee('John Doe');
        $reqView->assertSee('999888777');

        // 3. Test JSON fetch-users endpoint
        $jsonResp = $this->postJson('/reports/requirements/fetch-users');
        $jsonResp->assertStatus(200)
            ->assertJsonStructure(['success', 'count', 'users']);

        // 4. Test settings fetch-users route
        $settingsResp = $this->post('/settings/report-tracking/fetch-users');
        $settingsResp->assertRedirect();
    }

    public function test_safety_guard_blocks_test_messages_to_production_work_groups(): void
    {
        $telegramService = app(TelegramService::class);

        // 1. Ensure test messages are BLOCKED from live staff work groups
        $this->assertTrue($telegramService->isTestMessageBlocked('-4646583053', '[TEST] Production alert verification'));
        $this->assertTrue($telegramService->isTestMessageBlocked('-1003150870760', 'This is a test mode message'));
        $this->assertTrue($telegramService->isTestMessageBlocked('-4646583053', 'សារសាកល្បង [trial]'));

        // 2. Ensure test messages are ALLOWED in designated testing groups
        $this->assertFalse($telegramService->isTestMessageBlocked('-1003744799209', '[TEST] Normal test in testing supergroup'));
        $this->assertFalse($telegramService->isTestMessageBlocked('-5150858234', '[TEST] Normal test in testing group'));

        // 3. Ensure sending a test message to production work group returns false
        $result = $telegramService->sendMessage('-4646583053', '[TEST] Should be blocked immediately');
        $this->assertFalse($result);
    }
}
