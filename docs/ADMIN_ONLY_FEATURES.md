# Admin-Only Features - Security Implementation

## Date
June 27, 2026

## Overview
Restricted advanced system settings to admin users only for better security and system organization. Regular users cannot access or modify critical configurations.

## Implementation

### 1. CheckAdmin Middleware
**File**: `app/Http/Middleware/CheckAdmin.php`

**Logic**:
```php
$user = $request->session()->get('user_name');
$admins = ['Admin', 'DAVY', 'admin'];

if (!$user || !in_array($user, $admins)) {
    return redirect()->route('dashboard')
        ->with('error', 'អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។');
}
```

**Admin Users**:
- Admin
- DAVY
- admin

### 2. Middleware Registration
**File**: `bootstrap/app.php`

Registered as alias:
```php
$middleware->alias([
    'admin' => \App\Http\Middleware\CheckAdmin::class,
]);
```

### 3. Protected Routes
**File**: `routes/web.php`

All Telegram setup routes now require admin:
```php
Route::prefix('telegram')->middleware('admin')->group(function () {
    // All telegram routes protected
});
```

**Protected Routes**:
- `/telegram` - Main setup page
- `/telegram/set-webhook` - Configure webhook
- `/telegram/delete-webhook` - Remove webhook
- `/telegram/poll` - Manual polling
- `/telegram/add-group` - Add groups
- `/telegram/groups/{group}` - Manage groups
- `/telegram/alert-template` - Edit alert template
- `/telegram/daily-report-template` - Edit report template
- `/telegram/category-labels` - Edit category labels
- `/telegram/item-name-format` - Language format settings

### 4. UI Visibility
**File**: `resources/views/layouts/app.blade.php`

Telegram Bot menu only visible to admins:
```php
@php
  $userName = session('user_name');
  $isAdmin = in_array($userName, ['Admin', 'DAVY', 'admin']);
@endphp
@if($isAdmin)
  <li>
    <a href="{{ route('telegram.setup') }}">
      Telegram Bot
    </a>
  </li>
@endif
```

## Features Protected

### Telegram Configuration (Admin Only)
- ✅ Bot setup and webhook configuration
- ✅ Group and topic management
- ✅ Alert template customization
- ✅ Daily report template customization
- ✅ Category label editing
- ✅ Language format settings

### Regular User Access (Everyone)
- ✅ Dashboard
- ✅ Production (Books, Daily Prints, Print Requests)
- ✅ Procurement (Requests, Suppliers, Purchase Orders)
- ✅ Stock Management (Materials, Movements, Reports)
- ✅ Equipment (Machines & Maintenance)
- ✅ Inventory (Legacy)
- ✅ Notifications

## How It Works

### For Admin Users
1. Login as Admin/DAVY/admin
2. See "Telegram Bot" in sidebar
3. Can access all Telegram configuration pages
4. Can modify templates, labels, and settings

### For Regular Users
1. Login as regular user
2. **Don't see** "Telegram Bot" in sidebar
3. If they try to access `/telegram` directly → Redirected to dashboard
4. See error message: "អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។"
5. Can still use all other features normally

## Adding More Admins

**Method 1: Update Middleware** (Temporary)
Edit `app/Http/Middleware/CheckAdmin.php`:
```php
$admins = ['Admin', 'DAVY', 'admin', 'NewAdminName'];
```

**Method 2: Database Flag** (Recommended for Future)
Add `is_admin` column to users table:
```sql
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT 0;
UPDATE users SET is_admin = 1 WHERE name IN ('Admin', 'DAVY', 'admin');
```

Then update middleware:
```php
$user = auth()->user();
if (!$user || !$user->is_admin) {
    return redirect()->route('dashboard')->with('error', '...');
}
```

## Security Benefits

### 1. Prevents Accidental Changes
- Regular users can't accidentally modify Telegram bot settings
- Templates and configurations stay consistent
- Reduces support burden

### 2. Controlled Access
- Only authorized personnel can configure system
- Clear separation between operators and administrators
- Audit trail of who has admin access

### 3. System Stability
- Critical settings protected from unintended changes
- Bot configurations remain stable
- Less chance of breaking integrations

### 4. Professional Organization
- Clean separation of concerns
- Role-based access control
- Better system governance

## Testing

### Test as Admin
1. Login as "Admin", "DAVY", or "admin"
2. Check sidebar → See "Telegram Bot" menu
3. Click "Telegram Bot" → Access granted
4. Can modify all settings

### Test as Regular User
1. Login as any other user
2. Check sidebar → **No** "Telegram Bot" menu
3. Try accessing `/telegram` directly:
   - Redirected to dashboard
   - See error: "អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។"
4. All other features work normally

## Error Messages

**Khmer + English**:
```
អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។ / You do not have permission to access this section.
```

## Future Enhancements (Optional)

### 1. Role-Based System
- Create roles table (admin, manager, operator, viewer)
- Assign permissions per role
- More granular access control

### 2. Admin Dashboard
- Separate admin panel
- User management
- Permission assignment
- Activity logs

### 3. Audit Logging
- Log all admin actions
- Track who changed what settings
- Timestamp all modifications

### 4. Two-Factor Authentication
- Extra security for admin accounts
- SMS or email verification
- Prevent unauthorized access

## Files Modified

1. ✅ `app/Http/Middleware/CheckAdmin.php` - Created middleware
2. ✅ `bootstrap/app.php` - Registered middleware
3. ✅ `routes/web.php` - Protected Telegram routes
4. ✅ `resources/views/layouts/app.blade.php` - Conditional menu display

## Status
✅ **COMPLETE** - Admin-only protection active
✅ Regular users cannot access Telegram settings
✅ Admin users have full access
✅ Clean and secure implementation
