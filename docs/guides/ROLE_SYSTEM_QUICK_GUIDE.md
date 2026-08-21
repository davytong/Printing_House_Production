# ROLE SYSTEM - QUICK GUIDE 🚀

## HOW ROLES WORK

```
USER LOGS IN
    ↓
Selects Position (paper_report, admin, etc.)
    ↓
System looks up: role_mapping_{position}
    ↓
Assigns Role (admin, reporter, procurement, store_manager)
    ↓
Role stored in session('user_role')
    ↓
Permissions checked everywhere
```

---

## QUICK REFERENCE

### Check Permission (Code)
```php
use App\Services\RoleService;

// Check specific permission
if (RoleService::can('manage_materials')) {
    // allowed
}

// Check if admin
if (RoleService::isAdmin()) {
    // admin only
}
```

### Check Permission (Blade)
```blade
@if(\App\Services\RoleService::can('manage_telegram'))
    <!-- Show for users with manage_telegram permission -->
@endif

@if(\App\Services\RoleService::isAdmin())
    <!-- Admin only -->
@endif
```

### Protect Route
```php
// Admin only
Route::middleware('admin')->get('/telegram/setup', ...);

// Specific permission
Route::middleware('can:manage_materials')->get('/materials', ...);
```

---

## AVAILABLE PERMISSIONS

- `view_dashboard`
- `manage_materials`
- `manage_stock`
- `daily_reports`
- `manage_procurement`
- `manage_suppliers`
- `manage_po`
- `view_analytics`
- `manage_machines`
- `manage_telegram`
- `manage_users`
- `view_all_reports`

---

## ROLE SUMMARY

| Who | Role | What They Can Do |
|-----|------|------------------|
| **Admin** | `admin` | Everything |
| **Paper/Press/Finishing Reporter** | `reporter` | Daily reports only |
| **Store** | `store_manager` | Stock & materials |
| **Procurement** | `procurement` | POs & suppliers |

---

## CUSTOMIZE ROLES

**Change role for a position**:
```php
Setting::set('role_mapping_paper_report', 'store_manager');
```

**Give role new permission**:
```php
$perms = json_decode(Setting::get('role_permissions_reporter'), true);
$perms['new_permission'] = true;
Setting::set('role_permissions_reporter', json_encode($perms));
```

---

## WHERE ROLE IS CHECKED

✅ Entry login → Assigns role  
✅ CheckAdmin middleware → Checks if admin  
✅ CheckPermission middleware → Checks specific permission  
✅ Sidebar menu → Hides items user can't access  
✅ Controllers → Can check before actions  
✅ Views → Can hide features conditionally  

---

**SIMPLE. PRACTICAL. USEFUL.** 🎯
