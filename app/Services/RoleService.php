<?php

namespace App\Services;

use App\Models\Setting;

class RoleService
{
    /**
     * Get role for a position
     */
    public static function getRoleForPosition(string $position): string
    {
        $mapping = Setting::get("role_mapping_{$position}");
        
        // Fallback to default if not configured
        return $mapping ?: match($position) {
            'admin' => 'admin',
            'paper_report', 'press_report', 'finishing_report' => 'reporter',
            'procurement' => 'procurement',
            'store' => 'store_manager',
            default => 'reporter',
        };
    }
    
    /**
     * Get permissions for a role
     */
    public static function getPermissions(string $role): array
    {
        $permissions = Setting::get("role_permissions_{$role}");
        
        if ($permissions) {
            return json_decode($permissions, true) ?: [];
        }
        
        // Fallback defaults
        return match($role) {
            'admin' => [
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
            ],
            'reporter' => [
                'view_dashboard' => false,
                'daily_reports' => true,
            ],
            'procurement' => [
                'view_dashboard' => true,
                'manage_procurement' => true,
                'manage_suppliers' => true,
                'manage_po' => true,
            ],
            'store_manager' => [
                'view_dashboard' => true,
                'manage_materials' => true,
                'manage_stock' => true,
                'daily_reports' => true,
                'view_analytics' => true,
                'view_all_reports' => true,
            ],
            default => ['daily_reports' => true],
        };
    }
    
    /**
     * Check if user has permission
     */
    public static function can(string $permission): bool
    {
        $role = session('user_role');
        
        if (!$role) {
            return false;
        }
        
        // Admin always has all permissions
        if ($role === 'admin') {
            return true;
        }
        
        $permissions = self::getPermissions($role);
        return $permissions[$permission] ?? false;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return session('user_role') === 'admin';
    }
    
    /**
     * Get role display name
     */
    public static function getRoleLabel(string $role): string
    {
        return match($role) {
            'admin' => 'អ្នកគ្រប់គ្រង (Admin)',
            'reporter' => 'អ្នករាយការណ៍ (Reporter)',
            'procurement' => 'ផ្នែកលទ្ធកម្ម (Procurement)',
            'store_manager' => 'គ្រប់គ្រងស្តុក (Store Manager)',
            default => ucfirst($role),
        };
    }
    
    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return [
            'admin' => 'អ្នកគ្រប់គ្រង (Admin)',
            'store_manager' => 'គ្រប់គ្រងស្តុក (Store Manager)',
            'procurement' => 'ផ្នែកលទ្ធកម្ម (Procurement)',
            'reporter' => 'អ្នករាយការណ៍ (Reporter)',
        ];
    }
    
    /**
     * Get menu items allowed for current role
     */
    public static function getAllowedMenuItems(): array
    {
        $role = session('user_role');
        
        if (!$role) {
            return [];
        }
        
        $permissions = self::getPermissions($role);
        $menu = [];
        
        if ($permissions['view_dashboard'] ?? false) {
            $menu[] = 'dashboard';
            $menu[] = 'analytics';
        }
        
        if ($permissions['daily_reports'] ?? false) {
            $menu[] = 'daily_reports';
        }
        
        if ($permissions['manage_materials'] ?? false) {
            $menu[] = 'materials';
        }
        
        if ($permissions['manage_stock'] ?? false) {
            $menu[] = 'stock';
            $menu[] = 'stock_reports';
        }
        
        if ($permissions['manage_procurement'] ?? false) {
            $menu[] = 'procurement';
        }
        
        if ($permissions['manage_suppliers'] ?? false) {
            $menu[] = 'suppliers';
        }
        
        if ($permissions['manage_po'] ?? false) {
            $menu[] = 'purchase_orders';
        }
        
        if ($permissions['manage_machines'] ?? false) {
            $menu[] = 'machines';
        }
        
        if ($permissions['manage_telegram'] ?? false) {
            $menu[] = 'telegram';
        }
        
        if ($permissions['view_all_reports'] ?? false) {
            $menu[] = 'all_reports';
        }
        
        return $menu;
    }
}
