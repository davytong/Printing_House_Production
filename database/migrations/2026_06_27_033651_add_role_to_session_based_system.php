<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create settings for position-to-role mapping
        // This allows flexible role assignment per position without changing code
        
        $defaultMappings = [
            'admin' => 'admin',
            'paper_report' => 'reporter',
            'press_report' => 'reporter',
            'finishing_report' => 'reporter',
            'procurement' => 'procurement',
            'store' => 'store_manager',
        ];
        
        foreach ($defaultMappings as $position => $role) {
            DB::table('settings')->insertOrIgnore([
                'key' => "role_mapping_{$position}",
                'value' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Create role permissions configuration
        $rolePermissions = [
            'admin' => json_encode([
                'view_dashboard' => true,
                'manage_materials' => true,
                'manage_stock' => true,
                'daily_reports' => true,
                'manage_procurement' => true,
                'manage_suppliers' => true,
                'manage_po' => true,
                'view_analytics' => true,
                'manage_machines' => true,
                'manage_telegram' => true,
                'manage_users' => true,
                'view_all_reports' => true,
            ]),
            'reporter' => json_encode([
                'view_dashboard' => false,
                'manage_materials' => false,
                'manage_stock' => false,
                'daily_reports' => true,
                'manage_procurement' => false,
                'manage_suppliers' => false,
                'manage_po' => false,
                'view_analytics' => false,
                'manage_machines' => false,
                'manage_telegram' => false,
                'manage_users' => false,
                'view_all_reports' => false,
            ]),
            'procurement' => json_encode([
                'view_dashboard' => true,
                'manage_materials' => false,
                'manage_stock' => false,
                'daily_reports' => false,
                'manage_procurement' => true,
                'manage_suppliers' => true,
                'manage_po' => true,
                'view_analytics' => false,
                'manage_machines' => false,
                'manage_telegram' => false,
                'manage_users' => false,
                'view_all_reports' => false,
            ]),
            'store_manager' => json_encode([
                'view_dashboard' => true,
                'manage_materials' => true,
                'manage_stock' => true,
                'daily_reports' => true,
                'manage_procurement' => false,
                'manage_suppliers' => false,
                'manage_po' => false,
                'view_analytics' => true,
                'manage_machines' => false,
                'manage_telegram' => false,
                'manage_users' => false,
                'view_all_reports' => true,
            ]),
        ];
        
        foreach ($rolePermissions as $role => $permissions) {
            DB::table('settings')->insertOrIgnore([
                'key' => "role_permissions_{$role}",
                'value' => $permissions,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove role mappings
        DB::table('settings')
            ->where('key', 'LIKE', 'role_mapping_%')
            ->delete();
            
        // Remove role permissions
        DB::table('settings')
            ->where('key', 'LIKE', 'role_permissions_%')
            ->delete();
    }
};
