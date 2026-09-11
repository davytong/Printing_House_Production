<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\ProductionBatch;
use App\Models\DailyPrint;
use App\Models\StockMovement;
use App\Models\Material;
use App\Models\ActivityLog;
use App\Models\TelegramGroup;
use App\Models\Setting;

class RestoreUserDataCommand extends Command
{
    protected $signature = 'app:restore-user-data';
    protected $description = 'Restore original user database dump data';

    public function handle()
    {
        $this->info('Starting database restoration from user dump...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Activity Logs
        $activityLogs = [
            [1,'DAVY','admin','Login','Entered system as Admin','172.16.17.65','2026-06-15 23:04:34','2026-06-15 23:04:34'],
            [2,'DAVY','paper_report','Login','Entered system as Paper Report','172.16.17.65','2026-06-15 23:04:38','2026-06-15 23:04:38'],
            [3,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-15 23:04:43','2026-06-15 23:04:43'],
            [4,'DAVY','paper_report','Login','Entered system as Paper Report','172.16.17.65','2026-06-15 23:04:53','2026-06-15 23:04:53'],
            [5,'DAVY','store','Login','Entered system as Store','172.16.17.65','2026-06-15 23:04:59','2026-06-15 23:04:59'],
            [6,'DAVY','store','Logout','Left the system','172.16.17.65','2026-06-15 23:05:07','2026-06-15 23:05:07'],
            [7,'DAVY','admin','Login','Entered system as Admin','172.16.17.65','2026-06-15 23:05:12','2026-06-15 23:05:12'],
            [8,'DAVY','admin','Logout','Left the system','172.16.17.65','2026-06-15 23:05:19','2026-06-15 23:05:19'],
            [9,'DAVY','procurement','Login','Entered system as Procurement','172.16.17.65','2026-06-15 23:05:37','2026-06-15 23:05:37'],
            [10,'DAVY','admin','Login','Entered system as Admin','172.16.17.57','2026-06-15 23:14:52','2026-06-15 23:14:52'],
            [11,'DAVY','admin','Logout','Left the system','172.16.17.57','2026-06-15 23:14:57','2026-06-15 23:14:57'],
            [12,'DAVY','paper_report','Login','Entered system as Paper Report','172.16.17.57','2026-06-15 23:15:02','2026-06-15 23:15:02'],
            [13,'DAVY','paper_report','Logout','Left the system','172.16.17.57','2026-06-15 23:15:11','2026-06-15 23:15:11'],
            [14,'DAVY','store','Login','Entered system as Store','172.16.17.57','2026-06-15 23:19:23','2026-06-15 23:19:23'],
            [15,'DAVY','store','Logout','Left the system','172.16.17.57','2026-06-15 23:19:44','2026-06-15 23:19:44'],
            [16,'DAVY','admin','Login','Entered system as Admin','172.16.17.57','2026-06-15 23:19:51','2026-06-15 23:19:51'],
            [17,'DAVY','admin','Logout','Left the system','172.16.17.57','2026-06-16 01:18:01','2026-06-16 01:18:01'],
            [18,'G','admin','Login','Entered system as Admin','172.16.17.57','2026-06-16 01:18:12','2026-06-16 01:18:12'],
            [19,'G','admin','Logout','Left the system','172.16.17.57','2026-06-16 01:18:21','2026-06-16 01:18:21'],
            [20,'BUNG','paper_report','Login','Entered system as Paper Report','172.16.17.57','2026-06-16 01:18:34','2026-06-16 01:18:34'],
            [21,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-16 02:14:28','2026-06-16 02:14:28'],
            [22,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-16 02:14:35','2026-06-16 02:14:35'],
            [23,'????','press_report','Login','Entered system as Press Report','172.16.17.57','2026-06-16 02:15:09','2026-06-16 02:15:09'],
            [24,'DAVY','press_report','Logout','Left the system','172.16.17.65','2026-06-16 02:16:03','2026-06-16 02:16:03'],
            [25,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-16 02:18:00','2026-06-16 02:18:00'],
            [26,'DAVY','press_report','Logout','Left the system','172.16.17.65','2026-06-16 02:18:12','2026-06-16 02:18:12'],
            [27,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-16 02:18:17','2026-06-16 02:18:17'],
            [28,'DAVY','press_report','Login','Entered system as Press Report','172.16.17.65','2026-06-16 02:18:37','2026-06-16 02:18:37'],
            [29,'BUNG','press_report','Login','Entered system as Press Report','172.16.17.57','2026-06-16 02:19:01','2026-06-16 02:19:01'],
            [30,'Try Lybong','paper_report','Login','Entered system as Paper Report','172.16.18.152','2026-06-16 02:34:26','2026-06-16 02:34:26'],
            [31,'Keo pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report','172.16.19.48','2026-06-16 03:47:25','2026-06-16 03:47:25'],
            [32,'??? ?????','press_report','Login','Entered system as Press Report','172.16.17.237','2026-06-16 03:48:06','2026-06-16 03:48:06'],
            [33,'????','press_report','Logout','Left the system','172.16.17.57','2026-06-16 04:20:20','2026-06-16 04:20:20'],
            [34,'DAVY','admin','Login','Entered system as Admin','172.16.17.57','2026-06-16 04:20:28','2026-06-16 04:20:28'],
            [35,'DAVY','admin','Login','Entered system as Admin (Role: admin)','fe80::16bc:7d1a:eef6:5880','2026-07-06 05:13:24','2026-07-06 05:13:24'],
            [36,'davcy','admin','Login','Entered system as Admin (Role: admin)','127.0.0.1','2026-07-06 06:45:32','2026-07-06 06:45:32'],
            [37,'davcy','admin','Login','Entered system as Admin','127.0.0.1','2026-07-06 06:46:55','2026-07-06 06:46:55'],
            [38,'DAVY','admin','Login','Entered system as Admin (Role: admin)','fe80::16bc:7d1a:eef6:5880','2026-07-06 19:13:34','2026-07-06 19:13:34'],
            [39,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-06 22:06:04','2026-07-06 22:06:04'],
            [40,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-06 22:07:27','2026-07-06 22:07:27'],
            [41,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-06 22:13:15','2026-07-06 22:13:15'],
            [42,'d','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-06 22:51:49','2026-07-06 22:51:49'],
            [43,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-07 03:01:43','2026-07-07 03:01:43'],
            [44,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-07 03:02:02','2026-07-07 03:02:02'],
            [45,'d','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-07 03:02:28','2026-07-07 03:02:28'],
            [46,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','::1','2026-07-07 03:32:58','2026-07-07 03:32:58'],
            [47,'DAVY','admin','Login','Entered system as Admin (Role: admin)','::1','2026-07-07 03:36:48','2026-07-07 03:36:48'],
            [48,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-21 05:22:21','2026-08-21 05:22:21'],
            [49,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-22 02:02:15','2026-08-22 02:02:15'],
            [50,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-22 02:09:09','2026-08-22 02:09:09'],
            [51,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','136.228.131.193','2026-08-22 02:11:43','2026-08-22 02:11:43'],
            [52,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-22 02:21:37','2026-08-22 02:21:37'],
            [53,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-22 02:23:01','2026-08-22 02:23:01'],
            [54,'DAVY','admin','Login','Entered system as Admin (Role: admin)','117.20.116.18','2026-08-24 01:15:21','2026-08-24 01:15:21'],
            [55,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-24 02:24:24','2026-08-24 02:24:24'],
            [56,'DAVY','admin','Login','Entered system as Admin (Role: admin)','117.20.116.18','2026-08-24 02:47:46','2026-08-24 02:47:46'],
            [57,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-25 02:02:18','2026-08-25 02:02:18'],
            [58,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-25 02:28:58','2026-08-25 02:28:58'],
            [59,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-25 03:36:20','2026-08-25 03:36:20'],
            [60,'DAVY','admin','Login','Entered system as Admin (Role: admin)','203.144.88.253','2026-08-25 05:44:10','2026-08-25 05:44:10'],
            [61,'DAVY','admin','Login','Entered system as Admin (Role: admin)','203.144.88.253','2026-08-25 05:58:06','2026-08-25 05:58:06'],
            [62,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-25 06:25:41','2026-08-25 06:25:41'],
            [63,'DAVY','admin','Logout','Left the system','136.228.131.193','2026-08-25 07:04:03','2026-08-25 07:04:03'],
            [64,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-25 07:04:05','2026-08-25 07:04:05'],
            [65,'S','procurement','Login','Entered system as Procurement (Role: procurement)','136.228.131.193','2026-08-25 07:04:57','2026-08-25 07:04:57'],
            [66,'S','procurement','Logout','Left the system','136.228.131.193','2026-08-25 07:05:16','2026-08-25 07:05:16'],
            [67,'S','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-25 07:05:23','2026-08-25 07:05:23'],
            [68,'DAVY','admin','Login','Entered system as Admin (Role: admin)','203.144.88.253','2026-08-25 07:49:57','2026-08-25 07:49:57'],
            [69,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-26 01:41:40','2026-08-26 01:41:40'],
            [70,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-26 09:09:54','2026-08-26 09:09:54'],
            [71,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-26 09:20:48','2026-08-26 09:20:48'],
            [72,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-26 09:29:16','2026-08-26 09:29:16'],
            [73,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-26 09:36:31','2026-08-26 09:36:31'],
            [74,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-27 00:30:15','2026-08-27 00:30:15'],
            [75,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-27 01:24:00','2026-08-27 01:24:00'],
            [76,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-27 10:34:23','2026-08-27 10:34:23'],
            [77,'DAVY','paper_report','Login','Entered system as Paper Report (Role: reporter)','203.144.80.158','2026-08-27 12:58:28','2026-08-27 12:58:28'],
            [78,'DAVY','paper_report','Logout','Left the system','203.144.80.158','2026-08-27 12:58:38','2026-08-27 12:58:38'],
            [79,'DAVY','admin','Login','Entered system as Admin (Role: admin)','203.144.80.158','2026-08-27 12:58:40','2026-08-27 12:58:40'],
            [80,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-28 10:21:20','2026-08-28 10:21:20'],
            [81,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','136.228.131.193','2026-08-28 10:33:28','2026-08-28 10:33:28'],
            [82,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-29 08:35:15','2026-08-29 08:35:15'],
            [83,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','136.228.131.193','2026-08-29 08:36:06','2026-08-29 08:36:06'],
            [84,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-31 08:22:05','2026-08-31 08:22:05'],
            [85,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-08-31 09:23:46','2026-08-31 09:23:46'],
            [86,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','136.228.131.193','2026-08-31 10:40:48','2026-08-31 10:40:48'],
            [87,'DAVY','admin','Login','Entered system as Admin (Role: admin)','119.13.63.145','2026-08-31 13:16:58','2026-08-31 13:16:58'],
            [88,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-08-31 23:43:58','2026-08-31 23:43:58'],
            [89,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-09-01 06:30:35','2026-09-01 06:30:35'],
            [90,'San Chantha','press_report','Login','Entered system as Press Report (Role: reporter)','136.228.131.193','2026-09-01 09:48:53','2026-09-01 09:48:53'],
            [91,'Keo Pholchomruen Niza','finishing_report','Login','Entered system as Finishing Report (Role: reporter)','136.228.131.193','2026-09-01 10:42:15','2026-09-01 10:42:15'],
            [92,'DAVY','admin','Login','Entered system as Admin (Role: admin)','136.228.131.193','2026-09-01 12:49:09','2026-09-01 12:49:09'],
        ];

        foreach ($activityLogs as $log) {
            DB::table('activity_logs')->updateOrInsert(
                ['id' => $log[0]],
                [
                    'user_name' => $log[1],
                    'position' => $log[2],
                    'action' => $log[3],
                    'details' => $log[4],
                    'ip_address' => $log[5],
                    'created_at' => $log[6],
                    'updated_at' => $log[7],
                ]
            );
        }

        // Batches
        $batches = [
            [1,'Batch 1','suspended',null,'2026-07-06 04:59:58',null,'2026-07-06 04:59:58','2026-07-06 04:59:58'],
            [2,'Batch 2','completed',null,'2026-07-06 06:19:11','2026-08-21 05:11:34','2026-07-06 06:19:11','2026-08-21 05:11:34'],
            [3,'Batch 3','completed',null,'2026-08-21 05:11:34','2026-08-21 05:12:02','2026-08-21 05:11:34','2026-08-21 05:12:02'],
            [4,'Batch 4','completed',null,'2026-08-21 05:12:02','2026-08-31 08:22:21','2026-08-21 05:12:02','2026-08-31 08:22:21'],
            [17,'Batch 5','active',null,'2026-08-31 08:22:22',null,'2026-08-31 08:22:22','2026-08-31 08:22:22'],
        ];

        foreach ($batches as $b) {
            DB::table('production_batches')->updateOrInsert(
                ['id' => $b[0]],
                [
                    'name' => $b[1],
                    'status' => $b[2],
                    'notes' => $b[3],
                    'started_at' => $b[4],
                    'completed_at' => $b[5],
                    'created_at' => $b[6],
                    'updated_at' => $b[7],
                ]
            );
        }

        // Books
        $books = [
            [1,1,'Listening textbook','perfect_binding','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [2,1,'Reading textbook','perfect_binding','offset','ESL Level 1',1000,960,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [3,1,'Writing textbook','perfect_binding','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [4,1,'Listening workbook','staple','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [5,1,'Reading workbook','staple','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [6,1,'Writing workbook','staple','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [7,1,'Song','staple','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [8,1,'Folktale','staple','offset','ESL Level 1',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [9,1,'Listening textbook','perfect_binding','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [10,1,'Reading textbook','perfect_binding','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [11,1,'Writing textbook','perfect_binding','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [12,1,'Listening workbook','staple','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [13,1,'Reading workbook','staple','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [14,1,'Writing workbook','staple','offset','ESL Level 2',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [15,1,'Song','staple','offset','ESL Level 2',1000,600,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [16,1,'Folktale','staple','offset','ESL Level 2',1000,600,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [17,1,'Listening textbook','perfect_binding','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [18,1,'Reading textbook','perfect_binding','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [19,1,'Writing textbook','perfect_binding','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [20,1,'Listening workbook','staple','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [21,1,'Reading workbook','staple','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [22,1,'Writing workbook','staple','offset','ESL Level 3',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [23,1,'Song','staple','offset','ESL Level 3',1000,600,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [24,1,'Folktale','staple','offset','ESL Level 3',1000,600,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [25,1,'Listening textbook','perfect_binding','offset','ESL Level 4',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [26,1,'Reading textbook','perfect_binding','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [27,1,'Writing textbook','perfect_binding','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [28,1,'Listening workbook','staple','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [29,1,'Reading workbook','staple','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [30,1,'Writing workbook','staple','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [31,1,'Song','staple','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [32,1,'Folktale','staple','offset','ESL Level 4',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [33,1,'Listening textbook','perfect_binding','offset','ESL Level 5',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [34,1,'Reading textbook','perfect_binding','offset','ESL Level 5',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [35,1,'Writing textbook','perfect_binding','offset','ESL Level 5',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [36,1,'Listening workbook','staple','offset','ESL Level 5',1450,1450,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [37,1,'Reading workbook','staple','offset','ESL Level 5',1000,1000,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [38,1,'Writing workbook','staple','offset','ESL Level 5',1450,2900,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [39,1,'Song','staple','offset','ESL Level 5',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [40,1,'Folktale','staple','offset','ESL Level 5',1000,350,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [41,1,'Listening textbook','perfect_binding','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [42,1,'Reading textbook','perfect_binding','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [43,1,'Writing textbook','perfect_binding','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [44,1,'Listening workbook','staple','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [45,1,'Reading workbook','staple','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [46,1,'Writing workbook','staple','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [47,1,'Song','staple','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [48,1,'Folktale','staple','offset','ESL Level 6',1000,0,'2026-06-15 05:24:23','2026-07-06 04:59:58'],
            [49,2,'Listening Textbook','perfect_binding','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [50,2,'Reading Textbook','perfect_binding','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [51,2,'Writing Textbook','perfect_binding','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [52,2,'Listening Workbook','staple','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [53,2,'Reading Workbook','staple','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [54,2,'Writing Workbook','staple','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [55,2,'Song','staple','offset','Pre School 4',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [56,2,'Listening Textbook','perfect_binding','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [57,2,'Reading Textbook','perfect_binding','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [58,2,'Writing Textbook','perfect_binding','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [59,2,'Listening Workbook','staple','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [60,2,'Reading Workbook','staple','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [61,2,'Writing Workbook','staple','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [62,2,'Song','staple','offset','Pre School 5',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [63,2,'Listening Textbook','perfect_binding','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [64,2,'Reading Textbook','perfect_binding','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [65,2,'Writing Textbook','perfect_binding','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [66,2,'Listening Workbook','staple','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [67,2,'Reading Workbook','staple','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [68,2,'Writing Workbook','staple','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [69,2,'Song','staple','offset','Pre School 6',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [70,2,'Listening Textbook','perfect_binding','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [71,2,'Reading Textbook','perfect_binding','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [72,2,'Writing Textbook','perfect_binding','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [73,2,'Listening Workbook','staple','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [74,2,'Reading Workbook','staple','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [75,2,'Writing Workbook','staple','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [76,2,'Song','staple','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-07-06 06:51:20'],
            [77,2,'Forktale','staple','offset','Level 1',1000,1000,'2026-07-06 06:51:20','2026-08-21 05:11:09'],
            [78,3,'Listening Textbook','perfect_binding','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [79,3,'Reading Textbook','perfect_binding','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [80,3,'Writing Textbook','perfect_binding','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [81,3,'Listening Workbook','staple','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [82,3,'Reading Workbook','staple','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [83,3,'Writing Workbook','staple','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [84,3,'Song','staple','offset','Pre School 1',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [85,3,'Listening Textbook','perfect_binding','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [86,3,'Reading Textbook','perfect_binding','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [87,3,'Writing Textbook','perfect_binding','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [88,3,'Listening Workbook','staple','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [89,3,'Reading Workbook','staple','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [90,3,'Writing Workbook','staple','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [91,3,'Song','staple','offset','Pre School 2',1000,1000,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [92,3,'English Alpahabets','staple','offset','Starter 1',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [93,3,'Number','staple','offset','Starter 1',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [94,3,'Song and Artcraft','staple','offset','Starter 1',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [95,3,'Workbook','staple','offset','Starter 1',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:53'],
            [96,3,'English Alpahabets','staple','offset','Starter 2',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [97,3,'Number','staple','offset','Starter 2',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [98,3,'Song and Artcraft','staple','offset','Starter 2',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [99,3,'Workbook','staple','offset','Starter 2',500,500,'2026-08-21 05:11:44','2026-08-21 05:11:54'],
            [100,4,'Listening Textbook','perfect_binding','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [101,4,'Listening Workbook','staple','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [102,4,'Reading Textbook','perfect_binding','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [103,4,'Reading Workbook','staple','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [104,4,'Writing Textbook','perfect_binding','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [105,4,'Writing Workbook','staple','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [106,4,'CH Eloquence','perfect_binding','offset','Pre School 3 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:22'],
            [107,4,'Listening Textbook','staple','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-22 02:02:46'],
            [108,4,'Listening Workbook','perfect_binding','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:13:23'],
            [109,4,'Reading Textbook','staple','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-24 01:16:06'],
            [110,4,'Reading Workbook','perfect_binding','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-22 02:03:14'],
            [111,4,'Writing Textbook','staple','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-25 02:02:38'],
            [112,4,'Writing Workbook','perfect_binding','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-24 01:16:06'],
            [113,4,'CH Eloquence','staple','offset','Pre School 4 CSL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:13:23'],
            [114,4,'Listening Textbook','perfect_binding','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:46'],
            [115,4,'Listening Workbook','staple','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:46'],
            [116,4,'Reading Textbook','perfect_binding','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-21 05:12:46'],
            [117,4,'Reading Workbook','staple','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-26 09:39:59'],
            [118,4,'Writing Textbook','perfect_binding','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-26 09:39:59'],
            [119,4,'Writing Workbook','staple','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-27 12:59:00'],
            [120,4,'Song','staple','offset','Pre School 3 ESL',1000,1000,'2026-08-21 05:12:10','2026-08-25 02:02:49'],
            [130,17,'Listening Textbook','perfect_binding','offset','Level 11',1000,1000,'2026-08-31 08:22:37','2026-08-31 13:19:36'],
            [131,17,'Reading Textbook','perfect_binding','offset','Level 11',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [132,17,'Writing Textbook','perfect_binding','offset','Level 11',1000,1000,'2026-08-31 08:22:37','2026-09-01 12:49:33'],
            [133,17,'Listening Workbook','staple','offset','Level 11',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [134,17,'Reading Workbook','staple','offset','Level 11',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [135,17,'Writing Workbook','staple','offset','Level 11',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [136,17,'Listening Textbook','perfect_binding','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [137,17,'Reading Textbook','perfect_binding','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [138,17,'Writing Textbook','perfect_binding','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [139,17,'Listening Workbook','staple','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [140,17,'Reading Workbook','staple','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
            [141,17,'Writing Workbook','staple','offset','Level 12',1000,0,'2026-08-31 08:22:37','2026-08-31 08:22:37'],
        ];

        foreach ($books as $bk) {
            DB::table('books')->updateOrInsert(
                ['id' => $bk[0]],
                [
                    'batch_id' => $bk[1],
                    'title' => $bk[2],
                    'category' => $bk[3],
                    'printing_method' => $bk[4],
                    'grade' => $bk[5],
                    'target_qty' => $bk[6],
                    'total_printed' => $bk[7],
                    'created_at' => $bk[8],
                    'updated_at' => $bk[9],
                ]
            );
        }

        // Daily Prints
        $dailyPrints = [
            [1,25,600,'2026-06-15','2026-06-15 06:45:24','2026-06-15 06:45:24'],
            [2,37,1000,'2026-06-15','2026-06-15 06:46:32','2026-06-15 06:46:32'],
            [3,70,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [4,71,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [5,72,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [6,73,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [7,74,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [8,75,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [9,77,400,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [10,50,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [11,51,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [12,53,400,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [13,54,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [14,55,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [15,59,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [16,60,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [17,62,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [18,63,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [19,64,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [20,65,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [21,66,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [22,67,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [23,68,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [24,69,1000,'2026-08-21','2026-08-21 05:11:09','2026-08-21 05:11:09'],
            [25,78,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [26,79,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [27,80,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [28,81,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [29,82,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [30,83,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [31,84,1000,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [32,95,500,'2026-08-21','2026-08-21 05:11:53','2026-08-21 05:11:53'],
            [33,94,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [34,92,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [35,93,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [36,85,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [37,86,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [38,87,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [39,88,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [40,89,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [41,90,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [42,91,1000,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [43,99,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [44,98,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [45,96,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [46,97,500,'2026-08-21','2026-08-21 05:11:54','2026-08-21 05:11:54'],
            [47,100,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [48,102,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [49,104,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [50,101,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [51,103,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [52,105,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [53,106,1000,'2026-08-21','2026-08-21 05:12:22','2026-08-21 05:12:22'],
            [54,114,1000,'2026-08-21','2026-08-21 05:12:46','2026-08-21 05:12:46'],
            [55,116,1000,'2026-08-21','2026-08-21 05:12:46','2026-08-21 05:12:46'],
            [56,115,1000,'2026-08-21','2026-08-21 05:12:46','2026-08-21 05:12:46'],
            [57,108,1000,'2026-08-21','2026-08-21 05:13:23','2026-08-21 05:13:23'],
            [58,113,1000,'2026-08-21','2026-08-21 05:13:23','2026-08-21 05:13:23'],
            [59,107,1000,'2026-08-22','2026-08-22 02:02:46','2026-08-22 02:02:46'],
            [60,110,1000,'2026-08-22','2026-08-22 02:03:14','2026-08-22 02:03:14'],
            [61,109,1000,'2026-08-24','2026-08-24 01:16:06','2026-08-24 01:16:06'],
            [62,112,1000,'2026-08-24','2026-08-24 01:16:06','2026-08-24 01:16:06'],
            [63,111,1000,'2026-08-25','2026-08-25 02:02:38','2026-08-25 02:02:38'],
            [64,120,1000,'2026-08-25','2026-08-25 02:02:49','2026-08-25 02:02:49'],
            [65,118,1000,'2026-08-26','2026-08-26 09:39:59','2026-08-26 09:39:59'],
            [66,117,1000,'2026-08-26','2026-08-26 09:39:59','2026-08-26 09:39:59'],
            [67,119,1000,'2026-08-27','2026-08-27 12:59:00','2026-08-27 12:59:00'],
            [68,130,1000,'2026-08-31','2026-08-31 13:19:36','2026-08-31 13:19:36'],
            [69,132,1000,'2026-09-01','2026-09-01 12:49:33','2026-09-01 12:49:33'],
        ];

        foreach ($dailyPrints as $dp) {
            DB::table('daily_prints')->updateOrInsert(
                ['id' => $dp[0]],
                [
                    'book_id' => $dp[1],
                    'printed_today' => $dp[2],
                    'date' => $dp[3],
                    'created_at' => $dp[4],
                    'updated_at' => $dp[5],
                ]
            );
        }

        // Materials
        $materials = [
            [1,'PAP-0001','Glossy A1','ក្រដាសរលោង A1','paper','fa-solid fa-file','Glossy','A1','pack',10.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [2,'PAP-0002','Woodfree A1 100g','ក្រដាសស A1 100g','paper','fa-solid fa-file','Woodfree','A1','pack',10.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [3,'PAP-0003','Woodfree A1 80g','ក្រដាសស A1 80g','paper','fa-solid fa-file','Woodfree','A1','pack',10.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [4,'FLM-0004','Matte Film','ស្កុតស្រអាប់','film','fa-solid fa-film','Matte','Small Roll','roll',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [5,'FLM-0005','Glossy Film','ស្កុតរលោង','film','fa-solid fa-film','Glossy','Small Roll','roll',1.00,null,null,0.00,'active','2026-07-07 03:36:08',null,'2026-06-13 10:57:47','2026-07-07 03:38:00'],
            [6,'CON-0006','Yellow Ink','ទឹកថ្នាំលឿង','consumable','fa-solid fa-droplet','CMYK Ink',null,'can',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [7,'CON-0007','Magenta Ink','ទឹកថ្នាំក្រហម','consumable','fa-solid fa-droplet','CMYK Ink',null,'can',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [8,'CON-0008','Cyan Ink','ទឹកថ្នាំខៀវ','consumable','fa-solid fa-droplet','CMYK Ink',null,'can',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [9,'CON-0009','Black Ink','ទឹកថ្នាំខ្មៅ','consumable','fa-solid fa-droplet','CMYK Ink',null,'can',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [10,'CON-0010','Plate Cleaner','សាប៊ូជូតស្លាក','consumable','fa-solid fa-spray-can-sparkles','Cleaning',null,'bottle',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [11,'CON-0011','Printing Powder','ម្ស៉ៅបោះពុម្ព','consumable','fa-solid fa-box','Cleaning',null,'pack',3.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-08-21 05:18:27'],
            [12,'CON-0012','Cleaning Sponge','អេប៉ុងជូតផ្លាក','consumable','fa-solid fa-sponge','Cleaning',null,'pcs',5.00,null,null,0.00,'active',null,null,'2026-06-13 10:57:47','2026-08-21 05:19:27'],
            [13,'CON-0013','Rubber Blanket','កៅស៊ូ','consumable','fa-solid fa-circle-half-stroke','Press Parts',null,'sheet',2.00,null,null,0.00,'active','2026-06-14 23:06:35',null,'2026-06-13 10:57:47','2026-06-14 23:06:35'],
            [14,'CON-0014','GUMMIN','ទឹកថ្នាំ ហ្គូម','consumable','fa-solid fa-bottle-droplet','Chemicals',null,'can',1.00,null,null,0.00,'active',null,null,'2026-07-06 13:55:18','2026-07-06 06:58:01'],
            [15,'FLM-0015','Glossy Film','ស្កុតរលោង','film','fa-solid fa-film','Glossy','Large Roll','roll',1.00,null,null,0.00,'active','2026-07-07 03:36:09',null,'2026-07-07 05:51:18','2026-07-07 03:37:41'],
            [16,'FLM-0016','Matte Film','ស្កុតស្រអាប់','film','fa-solid fa-film','Matte','Large Roll','roll',5.00,null,null,0.00,'active','2026-07-07 03:36:12',null,'2026-07-07 05:51:18','2026-07-07 03:36:12'],
        ];

        foreach ($materials as $m) {
            DB::table('materials')->updateOrInsert(
                ['id' => $m[0]],
                [
                    'code' => $m[1],
                    'name' => $m[2],
                    'name_km' => $m[3],
                    'category' => $m[4],
                    'icon' => $m[5],
                    'sub_type' => $m[6],
                    'size' => $m[7],
                    'unit' => $m[8],
                    'min_stock' => $m[9],
                    'critical_stock' => $m[10],
                    'location' => $m[11],
                    'unit_cost' => $m[12],
                    'status' => $m[13],
                    'last_alerted_at' => $m[14],
                    'notes' => $m[15],
                    'created_at' => $m[16],
                    'updated_at' => $m[17],
                ]
            );
        }

        // Telegram Groups
        $groups = [
            [2,-1003150870760,'Press Processing Works','supergroup',null,null,null,1,'2026-06-13 03:58:24','2026-06-13 03:58:24'],
            [4,-1003150870760,'Press Processing Works','supergroup',881,'Paper Report','paper_stock',1,'2026-06-14 23:08:40','2026-06-14 23:08:40'],
            [5,-1003150870760,'Press Processing Works','supergroup',882,'Finishing Report','finishing_report',1,'2026-06-14 23:08:57','2026-08-22 02:19:22'],
            [6,-4646583053,'BELTEI Printing Press','group',null,null,null,0,'2026-06-16 03:58:16','2026-06-16 04:09:27'],
            [7,-1003744799209,'Testing','supergroup',null,null,'testing',1,'2026-06-16 03:58:16','2026-06-16 03:58:16'],
            [8,-1003150870760,'Press Processing Works','supergroup',2007,'Consumable Stock','consumable_stock',1,'2026-06-16 04:10:44','2026-08-22 02:29:09'],
            [9,-1003150870760,'Press Processing Works','supergroup',928,'Topic #928',null,1,'2026-07-06 04:58:48','2026-07-06 04:58:48'],
            [12,-1003150870760,'Press Processing Works','supergroup',885,'Stock Alert',null,1,'2026-08-22 02:15:25','2026-08-22 02:15:50'],
            [15,-5150858234,'Testing','group',null,null,null,0,'2026-08-25 05:20:27','2026-08-25 05:20:27'],
            [16,-1003150870760,'Press Processing Works','supergroup',914,'Stock Usage','stock_usage',1,'2026-08-25 06:46:06','2026-08-25 06:57:21'],
        ];

        foreach ($groups as $g) {
            DB::table('telegram_groups')->updateOrInsert(
                ['id' => $g[0]],
                [
                    'chat_id' => $g[1],
                    'name' => $g[2],
                    'type' => $g[3],
                    'message_thread_id' => $g[4],
                    'topic_name' => $g[5],
                    'purpose' => $g[6],
                    'is_forum' => $g[7],
                    'created_at' => $g[8],
                    'updated_at' => $g[9],
                ]
            );
        }

        // Settings
        $settings = [
            [1,'role_mapping_admin','admin','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [2,'role_mapping_paper_report','reporter','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [3,'role_mapping_press_report','reporter','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [4,'role_mapping_finishing_report','reporter','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [5,'role_mapping_procurement','procurement','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [6,'role_mapping_store','store_manager','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [7,'role_permissions_admin','{"view_dashboard":true,"manage_materials":true,"manage_stock":true,"daily_reports":true,"manage_procurement":true,"manage_suppliers":true,"manage_po":true,"view_analytics":true,"manage_machines":true,"manage_telegram":true,"manage_users":true,"view_all_reports":true}','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [8,'role_permissions_reporter','{"view_dashboard":false,"manage_materials":false,"manage_stock":false,"daily_reports":true,"manage_procurement":false,"manage_suppliers":false,"manage_po":false,"view_analytics":false,"manage_machines":false,"manage_telegram":false,"manage_users":false,"view_all_reports":false}','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [9,'role_permissions_procurement','{"view_dashboard":true,"manage_materials":false,"manage_stock":false,"daily_reports":false,"manage_procurement":true,"manage_suppliers":true,"manage_po":true,"view_analytics":false,"manage_machines":false,"manage_telegram":false,"manage_users":false,"view_all_reports":false}','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [10,'role_permissions_store_manager','{"view_dashboard":true,"manage_materials":true,"manage_stock":true,"daily_reports":true,"manage_procurement":false,"manage_suppliers":false,"manage_po":false,"view_analytics":true,"manage_machines":false,"manage_telegram":false,"manage_users":false,"view_all_reports":true}','2026-07-06 04:38:23','2026-07-06 04:38:23'],
            [11,'stock_alert_template',"{status_emoji} {status}\n📦 {name} ({name_km})\n📂 {category}\n━━━━━━━━━━━━━━━━\n📊 Stock: {stock} {unit}\n━━━━━━━━━━━━━━━━\n🕐 {date}",'2026-07-06 06:53:19','2026-08-22 02:32:20'],
            [12,'telegram_item_name_format_paper','english','2026-07-06 06:53:29','2026-07-06 06:53:29'],
            [13,'telegram_item_name_format_film','both','2026-07-06 06:53:29','2026-07-06 06:53:29'],
            [14,'telegram_item_name_format_consumable','khmer','2026-07-06 06:53:29','2026-07-06 06:53:29'],
            [15,'alert_chat_id','-1003150870760','2026-07-07 03:41:04','2026-08-22 02:16:02'],
            [16,'alert_thread_id','885','2026-07-07 03:41:04','2026-08-22 02:16:02'],
            [17,'alert_cooldown_hours','1','2026-07-07 03:41:04','2026-07-07 03:41:22'],
            [24,'low_stock_alert_enabled','1','2026-08-22 02:13:34','2026-08-22 02:13:34'],
            [25,'low_stock_default_threshold','3','2026-08-22 02:13:34','2026-08-22 02:13:34'],
            [26,'low_stock_default_critical','1','2026-08-22 02:13:34','2026-08-22 02:13:34'],
            [27,'low_stock_notification_groups','["7"]','2026-08-22 02:13:34','2026-08-24 02:48:08'],
            [28,'low_stock_message_template','','2026-08-22 02:13:34','2026-08-22 02:13:34'],
            [29,'daily_usage_chat_id','-1003744799209','2026-08-22 02:16:09','2026-08-22 02:16:09'],
            [30,'daily_usage_thread_id','','2026-08-22 02:16:09','2026-08-22 02:16:09'],
            [31,'stock_out_chat_id','-1003150870760','2026-08-25 07:06:05','2026-08-26 09:28:08'],
            [32,'stock_out_thread_id','914','2026-08-25 07:06:05','2026-08-26 09:28:08'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->updateOrInsert(
                ['id' => $s[0]],
                [
                    'key' => $s[1],
                    'value' => $s[2],
                    'created_at' => $s[3],
                    'updated_at' => $s[4],
                ]
            );
        }

        // Stock movements
        $movements = [
            [1,1,'adjust',null,61.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [2,2,'adjust',null,74.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [3,3,'adjust',null,166.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [4,4,'adjust',null,62.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [5,5,'adjust',null,42.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [6,6,'adjust',null,21.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [7,7,'adjust',null,18.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [8,8,'adjust',null,19.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [9,9,'adjust',null,15.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [10,10,'adjust',null,23.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [11,11,'adjust',null,17.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [12,12,'adjust',null,20.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [13,13,'adjust',null,2.00,'Opening stock','System',null,'2026-06-11','2026-06-13 10:57:47','2026-06-13 10:57:47'],
            [14,10,'adjust',null,21.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [15,11,'adjust',null,16.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [16,9,'adjust',null,14.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [17,8,'adjust',null,18.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [18,6,'adjust',null,20.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [19,13,'adjust',null,1.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:35','2026-06-14 23:06:35'],
            [20,5,'adjust',null,38.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:50','2026-06-14 23:06:50'],
            [21,4,'adjust',null,53.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:06:50','2026-06-14 23:06:50'],
            [22,1,'adjust',null,15.00,'Daily update',null,null,'2026-06-15','2026-06-14 23:07:16','2026-06-14 23:07:16'],
            [23,3,'adjust',null,120.00,'Daily update','???? ??????',null,'2026-06-15','2026-06-15 03:08:40','2026-06-15 03:08:40'],
            [24,4,'adjust',null,51.00,'Daily update','Keo pholchomruen Niza',null,'2026-06-15','2026-06-15 03:22:14','2026-06-15 03:22:14'],
            [25,3,'adjust',null,136.00,'Daily update','Try lybong',null,'2026-06-16','2026-06-16 02:36:24','2026-06-16 02:36:24'],
            [26,5,'adjust',null,36.00,'Daily update',null,null,'2026-06-16','2026-06-16 03:47:59','2026-06-16 03:47:59'],
            [27,4,'adjust',null,46.00,'Daily update',null,null,'2026-06-16','2026-06-16 03:47:59','2026-06-16 03:47:59'],
            [28,10,'adjust',null,17.00,'Daily update','??? ?????',null,'2026-06-16','2026-06-16 03:49:52','2026-06-16 03:49:52'],
            [29,7,'adjust',null,17.00,'Daily update','??? ?????',null,'2026-06-16','2026-06-16 03:49:52','2026-06-16 03:49:52'],
            [30,6,'adjust',null,19.00,'Daily update','??? ?????',null,'2026-06-16','2026-06-16 03:49:52','2026-06-16 03:49:52'],
            [31,1,'adjust',null,11.00,'Telegram Report','System Admin','Data Recovery from July 4 Telegram Report','2026-07-04','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [32,2,'adjust',null,59.00,'Telegram Report','System Admin','Data Recovery from July 4 Telegram Report','2026-07-04','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [33,3,'adjust',null,234.00,'Telegram Report','System Admin','Data Recovery from July 4 Telegram Report','2026-07-04','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [34,5,'adjust',null,7.00,'Telegram Report','System Admin','Data Recovery from July 4 Telegram Report','2026-07-04','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [35,4,'adjust',null,35.00,'Telegram Report','System Admin','Data Recovery from July 4 Telegram Report','2026-07-04','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [36,12,'adjust',null,25.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [37,14,'adjust',null,1.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [38,10,'adjust',null,13.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [39,11,'adjust',null,8.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [40,9,'adjust',null,11.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [41,8,'adjust',null,12.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [42,7,'adjust',null,12.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [43,6,'adjust',null,11.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [44,13,'adjust',null,10.00,'Telegram Report','System Admin','Data Recovery from July 6 Telegram Report','2026-07-06','2026-07-06 13:55:18','2026-07-06 13:55:18'],
            [45,5,'adjust',null,2.00,'Daily update','Keo pholchomruen Niza',null,'2026-07-07','2026-07-07 03:36:08','2026-07-07 03:36:08'],
            [46,15,'adjust',null,3.00,'Daily update','Keo pholchomruen Niza',null,'2026-07-07','2026-07-07 03:36:08','2026-07-07 03:36:08'],
            [47,4,'adjust',null,33.00,'Daily update','Keo pholchomruen Niza',null,'2026-07-07','2026-07-07 03:36:09','2026-07-07 03:36:09'],
            [48,16,'adjust',null,2.00,'Daily update','Keo pholchomruen Niza',null,'2026-07-07','2026-07-07 03:36:09','2026-07-07 03:36:09'],
            [49,14,'adjust',null,0.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [50,12,'adjust',null,18.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [51,10,'adjust',null,87.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [52,11,'adjust',null,27.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [53,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [54,8,'adjust',null,18.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [55,7,'adjust',null,17.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [56,6,'adjust',null,25.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [57,13,'adjust',null,6.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:20:34','2026-08-21 05:20:34'],
            [62,15,'adjust',null,5.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:21:51','2026-08-21 05:21:51'],
            [63,4,'adjust',null,32.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:21:51','2026-08-21 05:21:51'],
            [64,16,'adjust',null,3.00,'Daily update',null,null,'2026-08-21','2026-08-21 05:21:51','2026-08-21 05:21:51'],
            [65,12,'adjust',null,17.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [66,10,'adjust',null,84.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [67,11,'adjust',null,26.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [68,8,'adjust',null,17.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [69,7,'adjust',null,16.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [70,6,'adjust',null,23.00,'Daily update',null,null,'2026-08-22','2026-08-22 02:18:19','2026-08-22 02:18:19'],
            [71,10,'adjust',null,83.00,'Daily update',null,null,'2026-08-24','2026-08-24 02:28:55','2026-08-24 02:28:55'],
            [72,11,'adjust',null,25.00,'Daily update',null,null,'2026-08-24','2026-08-24 02:28:55','2026-08-24 02:28:55'],
            [73,8,'adjust',null,16.00,'Daily update',null,null,'2026-08-24','2026-08-24 02:28:55','2026-08-24 02:28:55'],
            [74,7,'adjust',null,15.00,'Daily update',null,null,'2026-08-24','2026-08-24 02:28:55','2026-08-24 02:28:55'],
            [75,10,'adjust',null,80.00,'Daily update',null,null,'2026-08-25','2026-08-25 02:31:33','2026-08-25 02:31:33'],
            [76,6,'adjust',null,22.00,'Daily update',null,null,'2026-08-25','2026-08-25 02:31:33','2026-08-25 02:31:33'],
            [83,9,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-25','2026-08-25 07:02:45','2026-08-25 07:02:45'],
            [84,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-25','2026-08-25 07:03:52','2026-08-25 07:03:52'],
            [85,9,'out','Production',1.00,'Mini App Stock Out','DAVY','Production','2026-08-25','2026-08-25 07:10:01','2026-08-25 07:10:01'],
            [86,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-25','2026-08-25 07:11:19','2026-08-25 07:11:19'],
            [87,9,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-25','2026-08-25 07:11:44','2026-08-25 07:11:44'],
            [88,9,'out','Production',1.00,'Mini App Stock Out','តុង ដាវី','Production','2026-08-25','2026-08-25 07:19:01','2026-08-25 07:19:01'],
            [89,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-25','2026-08-25 07:19:21','2026-08-25 07:19:21'],
            [90,9,'out','Production',1.00,'Mini App Stock Out','តុង ដាវី','Production','2026-08-25','2026-08-25 07:24:18','2026-08-25 07:24:18'],
            [91,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-25','2026-08-25 07:24:53','2026-08-25 07:24:53'],
            [92,12,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-25','2026-08-25 07:26:53','2026-08-25 07:26:53'],
            [93,12,'adjust',null,17.00,'Daily update',null,null,'2026-08-25','2026-08-25 07:27:17','2026-08-25 07:27:17'],
            [98,12,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-26','2026-08-26 08:48:01','2026-08-26 08:48:01'],
            [99,12,'adjust',null,17.00,'Daily update',null,null,'2026-08-26','2026-08-26 08:53:43','2026-08-26 08:53:43'],
            [100,8,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-26','2026-08-26 09:09:23','2026-08-26 09:09:23'],
            [101,8,'adjust',null,16.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:10:31','2026-08-26 09:10:31'],
            [102,9,'out','Production',1.00,'Mini App Stock Out','Davy','Production','2026-08-26','2026-08-26 09:11:01','2026-08-26 09:11:01'],
            [103,9,'adjust',null,13.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:16:47','2026-08-26 09:16:47'],
            [104,12,'out','Production',1.00,'Mini App Stock Out','តុង ដាវី - TONG DAVY (@davy_tong) 📱','Production','2026-08-26','2026-08-26 09:17:15','2026-08-26 09:17:15'],
            [105,12,'adjust',null,11.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [106,10,'adjust',null,78.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [107,11,'adjust',null,24.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [108,9,'adjust',null,12.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [109,8,'adjust',null,15.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [110,6,'adjust',null,21.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:22:50','2026-08-26 09:22:50'],
            [111,9,'out','Production',1.00,'Mini App Stock Out','Serey (@serey1160) 📱','Production','2026-08-26','2026-08-26 09:25:36','2026-08-26 09:25:36'],
            [112,9,'adjust',null,12.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:28:41','2026-08-26 09:28:41'],
            [113,7,'out','Production',1.00,'Mini App Stock Out','ផាន់ណាត់\n<b>អ្នកកត់ត្រា:</b> តុង ដាវី - TONG DAVY (@davy_tong) 📱','Production','2026-08-26','2026-08-26 09:32:40','2026-08-26 09:32:40'],
            [114,7,'adjust',null,15.00,'Daily update',null,null,'2026-08-26','2026-08-26 09:33:43','2026-08-26 09:33:43'],
            [115,7,'out','Production',1.00,'Mini App Stock Out','ផាន់ណាត់ (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Production','2026-08-26','2026-08-26 09:34:00','2026-08-26 09:34:00'],
            [116,10,'out','Cleaning',1.00,'Mini App Stock Out','សួស្ដី (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Cleaning','2026-08-27','2026-08-27 00:29:40','2026-08-27 00:29:40'],
            [133,10,'out','Production',1.00,'Mini App Stock Out','ឡុង (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Production','2026-08-27','2026-08-27 02:02:43','2026-08-27 02:02:43'],
            [134,10,'out','Cleaning',1.00,'Mini App Stock Out','ពូ ចំណាន (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Cleaning','2026-08-28','2026-08-28 00:59:24','2026-08-28 00:59:24'],
            [135,10,'out','Cleaning',1.00,'Mini App Stock Out','ចន្ថា (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Cleaning','2026-08-28','2026-08-28 01:35:22','2026-08-28 01:35:22'],
            [136,12,'adjust',null,9.00,'Daily update',null,null,'2026-08-28','2026-08-28 10:23:06','2026-08-28 10:23:06'],
            [137,15,'adjust',null,8.00,'Daily update','Keo Pholchomruen Niza',null,'2026-08-28','2026-08-28 10:34:31','2026-08-28 10:34:31'],
            [138,16,'adjust',null,6.00,'Daily update','Keo Pholchomruen Niza',null,'2026-08-28','2026-08-28 10:34:31','2026-08-28 10:34:31'],
            [139,10,'out','Cleaning',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Cleaning','2026-08-29','2026-08-28 23:17:26','2026-08-28 23:17:26'],
            [140,15,'adjust',null,7.00,'Daily update','Keo pholchomruen Niza',null,'2026-08-29','2026-08-29 08:36:50','2026-08-29 08:36:50'],
            [141,12,'adjust',null,8.00,'Daily update',null,null,'2026-08-29','2026-08-29 08:36:52','2026-08-29 08:36:52'],
            [142,10,'adjust',null,72.00,'Daily update',null,null,'2026-08-29','2026-08-29 08:36:52','2026-08-29 08:36:52'],
            [143,8,'out','Production',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Production','2026-08-31','2026-08-30 23:35:47','2026-08-30 23:35:47'],
            [144,7,'out','Production',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Production','2026-08-31','2026-08-30 23:35:56','2026-08-30 23:35:56'],
            [145,10,'out','Cleaning',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Cleaning','2026-08-31','2026-08-31 08:04:15','2026-08-31 08:04:15'],
            [146,10,'adjust',null,70.00,'Daily update',null,null,'2026-08-31','2026-08-31 09:25:51','2026-08-31 09:25:51'],
            [147,11,'adjust',null,23.00,'Daily update',null,null,'2026-08-31','2026-08-31 09:25:51','2026-08-31 09:25:51'],
            [148,6,'adjust',null,20.00,'Daily update',null,null,'2026-08-31','2026-08-31 09:25:51','2026-08-31 09:25:51'],
            [149,6,'out','Production',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Production','2026-09-01','2026-09-01 00:18:13','2026-09-01 00:18:13'],
            [150,11,'out','Production',1.00,'Mini App Stock Out','តុង ដាវី (អ្នកកត់ត្រា: តុង ដាវី - TONG DAVY)','Production','2026-09-01','2026-09-01 00:38:33','2026-09-01 00:38:33'],
            [151,10,'out','Cleaning',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Cleaning','2026-09-01','2026-09-01 06:28:38','2026-09-01 06:28:38'],
            [152,10,'out','Cleaning',1.00,'Mini App Stock Out','សាន ចន្ថា (អ្នកកត់ត្រា: សាន ចន្ថា (San Chantha))','Cleaning','2026-09-01','2026-09-01 07:32:05','2026-09-01 07:32:05'],
            [153,16,'adjust',null,5.00,'Daily update','Keo pholchomruen Niza',null,'2026-09-01','2026-09-01 10:43:20','2026-09-01 10:43:20'],
        ];

        foreach ($movements as $sm) {
            DB::table('stock_movements')->updateOrInsert(
                ['id' => $sm[0]],
                [
                    'material_id' => $sm[1],
                    'type' => $sm[2],
                    'reason' => $sm[3],
                    'quantity' => $sm[4],
                    'reference' => $sm[5],
                    'performed_by' => $sm[6],
                    'notes' => $sm[7],
                    'movement_date' => $sm[8],
                    'created_at' => $sm[9],
                    'updated_at' => $sm[10],
                ]
            );
        }

        // Low stock notifications
        $lowStocks = [
            [12,'2026-08-22','consumable',null,'Group -1003744799209',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>22/08/2026 (ថ្ងៃទី 22 ខែសីហា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-22 02:12:41',null,'2026-08-22 02:12:38','2026-08-22 02:12:41'],
            [13,'2026-08-22','consumable',6,'BELTEI Printing Press',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>22/08/2026 (ថ្ងៃទី 22 ខែសីហា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-22 02:19:27',null,'2026-08-22 02:19:26','2026-08-22 02:19:27'],
            [14,'2026-08-24','consumable',6,'BELTEI Printing Press',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>24/08/2026 (ថ្ងៃទី 24 ខែសីហា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-24 02:26:30',null,'2026-08-24 02:26:29','2026-08-24 02:26:30'],
            [15,'2026-08-24','consumable',7,'Testing',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>24/08/2026 (ថ្ងៃទី 24 ខែសីហា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-24 02:26:31',null,'2026-08-24 02:26:30','2026-08-24 02:26:31'],
            [18,'2026-08-27','consumable',7,'Testing',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>27/08/2026 (ថ្ងៃទី 27 ខែសីហា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-27 10:35:53',null,'2026-08-27 10:35:51','2026-08-27 10:35:53'],
            [19,'2026-09-01','consumable',7,'Testing',1,'[{"id":14,"name":"GUMMIN","name_km":"\\u1791\\u17b9\\u1780\\u1790\\u17d2\\u1793\\u17b6\\u17c6 \\u17a0\\u17d2\\u1782\\u17bc\\u1798","category":"consumable","category_label":"Consumable (\\u179f\\u1798\\u17d2\\u1797\\u17b6\\u179a\\u17c8\\u1794\\u17d2\\u179a\\u17be\\u1794\\u17d2\\u179a\\u17b6\\u179f\\u17cb)","sub_type":"Chemicals","size":null,"unit":"can","current_stock":0,"low_stock_threshold":1,"critical_stock":null,"status":"OUT_OF_STOCK","status_label":"\\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780 (Out of Stock)","status_badge":"<span class=\\"badge bg-dark text-white\\"><i class=\\"bi bi-x-circle-fill me-1\\"><\\/i> \\u26ab \\u17a2\\u179f\\u17cb\\u179f\\u17d2\\u178f\\u17bb\\u1780<\\/span>"}]',"សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n📅 <b>01/09/2026 (ថ្ងៃទី 1 ខែកញ្ញា ឆ្នាំ 2026)</b>\n\n<b>*Consumable (សម្ភារៈប្រើប្រាស់)*</b>\n🔴 <b>GUMMIN (ទឹកថ្នាំ ហ្គូម)</b>\n   └ Stock: <b>0 can</b> (អស់ស្តុក)\n\nសូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។",'SENT',null,'2026-08-31 23:44:20',null,'2026-08-31 23:44:18','2026-08-31 23:44:20'],
        ];

        foreach ($lowStocks as $ls) {
            DB::table('low_stock_notifications')->updateOrInsert(
                ['id' => $ls[0]],
                [
                    'report_date' => $ls[1],
                    'category' => $ls[2],
                    'destination_group_id' => $ls[3],
                    'destination_group_name' => $ls[4],
                    'items_count' => $ls[5],
                    'items_payload' => $ls[6],
                    'message' => $ls[7],
                    'status' => $ls[8],
                    'sent_by' => $ls[9],
                    'sent_at' => $ls[10],
                    'error_message' => $ls[11],
                    'created_at' => $ls[12],
                    'updated_at' => $ls[13],
                ]
            );
        }

        // System Notifications
        $sysNotifs = [
            [2,'warning','stock','?? Stock ??? ? Glossy Film','Film: Glossy Film ? Stock: 2 roll (Min: 5)',null,1,0,'2026-08-22 02:16:34','2026-07-07 03:36:08','2026-08-22 02:16:34'],
            [3,'warning','stock','?? Stock ??? ? Glossy Film','Film: Glossy Film ? Stock: 3 roll (Min: 5)',null,1,0,'2026-08-22 02:16:34','2026-07-07 03:36:08','2026-08-22 02:16:34'],
            [4,'warning','stock','?? Stock ??? ? Matte Film','Film: Matte Film ? Stock: 2 roll (Min: 5)',null,1,0,'2026-08-22 02:16:34','2026-07-07 03:36:09','2026-08-22 02:16:34'],
        ];

        foreach ($sysNotifs as $sn) {
            DB::table('system_notifications')->updateOrInsert(
                ['id' => $sn[0]],
                [
                    'type' => $sn[1],
                    'module' => $sn[2],
                    'title' => $sn[3],
                    'message' => $sn[4],
                    'action_url' => $sn[5],
                    'is_read' => $sn[6],
                    'telegram_sent' => $sn[7],
                    'read_at' => $sn[8],
                    'created_at' => $sn[9],
                    'updated_at' => $sn[10],
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Restoration completed!');
        $this->table(
            ['Entity', 'Count'],
            [
                ['Books', Book::count()],
                ['Production Batches', ProductionBatch::count()],
                ['Daily Prints', DailyPrint::count()],
                ['Materials', Material::count()],
                ['Stock Movements', StockMovement::count()],
                ['Activity Logs', ActivityLog::count()],
                ['Telegram Groups', TelegramGroup::count()],
                ['Settings', Setting::count()],
            ]
        );

        return Command::SUCCESS;
    }
}
