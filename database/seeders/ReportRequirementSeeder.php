<?php

namespace Database\Seeders;

use App\Models\ReportRequirement;
use Illuminate\Database\Seeder;

class ReportRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultChatId = config('services.telegram.alert_chat_id');

        $requirements = [
            [
                'staff_name'        => 'Staff A (Morning Lead)',
                'telegram_user_id'  => '1000000001',
                'telegram_username' => 'staff_a_press',
                'report_type'       => 'morning_1',
                'report_title'      => 'Morning Production Report',
                'identifier_tag'    => '[Morning Production Report]',
                'deadline_time'     => '07:00',
                'required_days'     => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                'alert_chat_id'     => $defaultChatId,
                'active'            => true,
                'send_ack'          => true,
                'notes'             => 'Daily 07:00 AM Morning Shift Output Report',
            ],
            [
                'staff_name'        => 'Staff B (Afternoon Lead)',
                'telegram_user_id'  => '1000000002',
                'telegram_username' => 'staff_b_press',
                'report_type'       => 'morning_2',
                'report_title'      => 'Second Production Report',
                'identifier_tag'    => '[Second Production Report]',
                'deadline_time'     => '15:10',
                'required_days'     => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                'alert_chat_id'     => $defaultChatId,
                'active'            => true,
                'send_ack'          => true,
                'notes'             => 'Daily 03:10 PM Afternoon/Midday Report',
            ],
            [
                'staff_name'        => 'Staff C (Evening Lead)',
                'telegram_user_id'  => '1000000003',
                'telegram_username' => 'staff_c_press',
                'report_type'       => 'evening_3',
                'report_title'      => 'Evening Production Report',
                'identifier_tag'    => '[Evening Production Report]',
                'deadline_time'     => '23:50',
                'required_days'     => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                'alert_chat_id'     => $defaultChatId,
                'active'            => true,
                'send_ack'          => true,
                'notes'             => 'Daily 11:50 PM Evening Wrap-up Report',
            ],
        ];

        foreach ($requirements as $req) {
            ReportRequirement::updateOrCreate(
                [
                    'telegram_user_id' => $req['telegram_user_id'],
                    'report_type'      => $req['report_type'],
                ],
                $req
            );
        }
    }
}