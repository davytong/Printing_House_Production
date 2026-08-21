<?php
/**
 * Extract correct Khmer material names from the backup SQL file
 * and update the database. Also set up batch structure.
 * 
 * Run with: php fix_data.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ============================================================
// STEP 1: Fix Khmer names by reading from the original backup
// ============================================================
echo "=== Step 1: Fixing Khmer Material Names ===\n";

$backupFile = 'C:\\Users\\USER\\Downloads\\printing_system (1).sql';
$content = file_get_contents($backupFile);

// Find the materials INSERT block
if (preg_match("/INSERT INTO `materials`.*?VALUES\s*\n(.*?);/s", $content, $match)) {
    $valuesBlock = $match[1];
    
    // Parse each row: (id, code, name, name_km, ...)
    preg_match_all("/\((\d+),\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',/", $valuesBlock, $rows, PREG_SET_ORDER);
    
    foreach ($rows as $row) {
        $id = (int) $row[1];
        $nameKm = $row[4];
        
        if (!empty($nameKm)) {
            DB::table('materials')->where('id', $id)->update(['name_km' => $nameKm]);
            echo "  Updated #{$id}: name_km = {$nameKm}\n";
        }
    }
} else {
    echo "  ERROR: Could not find materials INSERT in backup file!\n";
}

// Verify
echo "\n=== Verification ===\n";
$materials = DB::table('materials')->select('id', 'name', 'name_km')->get();
foreach ($materials as $m) {
    echo "  #{$m->id} {$m->name} => {$m->name_km}\n";
}

echo "\nDone!\n";
