# Production Setup Guide

## 1. Environment Configuration

### Update `.env` for Production:

```env
# IMPORTANT: Change these for production
APP_ENV=production
APP_DEBUG=false

# Set your production URL
APP_URL=http://your-production-domain.com

# Use strong application key (keep the existing one or generate new)
php artisan key:generate
```

### Why `APP_DEBUG=false`?

When `APP_DEBUG=true`:
- ❌ Users see full error stack traces
- ❌ Database queries are visible
- ❌ File paths are exposed
- ❌ Security vulnerability

When `APP_DEBUG=false`:
- ✅ Users see friendly error pages
- ✅ Technical details are hidden
- ✅ Errors logged to files instead
- ✅ More secure

---

## 2. Error Handling

### Current Implementation:

All export functions now have **try-catch blocks**:

```php
try {
    // Export logic
    return $exportService->exportProductionReport($books, $filename);
} catch (\Throwable $e) {
    // Log technical error for IT/developers
    \Log::error('Production export failed: ' . $e->getMessage());
    
    // Show friendly message to users (bilingual)
    return back()->with('error', 
        'មិនអាចទាញយក Excel បានទេ។ សូមព្យាយាមម្តងទៀត។ / Unable to export Excel. Please try again.'
    );
}
```

### Error Flow:

1. **Error Occurs** → Exception thrown
2. **Catch Block** → Error is caught
3. **Log Error** → Technical details saved to `storage/logs/laravel.log`
4. **User Sees** → Friendly bilingual message
5. **IT Reviews** → Can check logs for debugging

---

## 3. Custom Error Pages (Optional)

### Create Custom Error Views:

Laravel allows custom error pages for different HTTP errors:

```bash
# Create error views in resources/views/errors/
404.blade.php  # Page not found
500.blade.php  # Server error
403.blade.php  # Forbidden
```

### Example `resources/views/errors/500.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div style="text-align:center;padding:4rem 2rem">
    <div style="font-size:4rem;margin-bottom:1rem">⚠️</div>
    <h1>កំហុសប្រព័ន្ធ / System Error</h1>
    <p style="margin:1rem 0;color:#666">
        សូមទោស! មានបញ្ហាបច្ចេកទេស។ សូមព្យាយាមម្តងទៀតក្នុងពេលបន្តិច។<br>
        Sorry! A technical issue occurred. Please try again in a moment.
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
        <i class="bi bi-house"></i> ត្រឡប់ទៅ Dashboard / Back to Dashboard
    </a>
</div>
@endsection
```

---

## 4. Logging Configuration

### Log Channels (config/logging.php):

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'], // Multiple channels
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14, // Keep logs for 14 days
    ],
],
```

### Log Levels (from least to most severe):
- `debug` - Detailed debugging information
- `info` - Interesting events (user logged in)
- `notice` - Normal but significant events
- `warning` - Exceptional occurrences that are not errors
- `error` - Runtime errors (what we use for exports)
- `critical` - Critical conditions
- `alert` - Action must be taken immediately
- `emergency` - System is unusable

### View Logs:

```bash
# View recent logs
tail -f storage/logs/laravel.log

# View last 50 lines
tail -n 50 storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

---

## 5. Error Notification (Advanced)

### Option 1: Email Notifications

Add to `config/logging.php`:

```php
'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => 'Laravel Log',
    'emoji' => ':boom:',
    'level' => 'error', // Only send errors and above
],
```

### Option 2: Telegram Notifications

Create custom error handler in `app/Exceptions/Handler.php`:

```php
public function report(Throwable $exception)
{
    // Send critical errors to Telegram
    if ($this->shouldReport($exception) && app()->environment('production')) {
        $telegramService = app(\App\Services\TelegramService::class);
        $telegramService->sendMessage(
            env('TELEGRAM_ALERT_CHAT_ID'),
            "🚨 Error Alert\n\n" . 
            "Type: " . get_class($exception) . "\n" .
            "Message: " . $exception->getMessage() . "\n" .
            "File: " . $exception->getFile() . ":" . $exception->getLine()
        );
    }
    
    parent::report($exception);
}
```

---

## 6. Production Checklist

### Before Going Live:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set correct `APP_URL`
- [ ] Use strong database password
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set up daily log rotation
- [ ] Test all export functions
- [ ] Create custom error pages (404, 500)
- [ ] Set up backup system
- [ ] Configure error notifications (optional)
- [ ] Review file permissions (storage, bootstrap/cache)
- [ ] Enable HTTPS (recommended)

### Performance Optimization:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Clear all caches if needed
php artisan optimize:clear
```

---

## 7. Monitoring Errors

### Daily Tasks:
1. Check `storage/logs/laravel.log` for errors
2. Review Telegram/email notifications
3. Monitor disk space for logs
4. Rotate old logs if needed

### Weekly Tasks:
1. Review error patterns
2. Fix recurring issues
3. Update error messages if needed
4. Test error scenarios

### Log Rotation (Automatic):
Laravel's `daily` driver automatically:
- Creates new log file each day
- Keeps logs for specified days (default 7)
- Deletes old logs automatically
- File format: `laravel-YYYY-MM-DD.log`

---

## 8. User-Friendly Error Messages

### Best Practices:

✅ **Good Error Messages:**
- Bilingual (Khmer + English)
- Clear and simple
- Suggest action ("Try again")
- Offer help ("Contact IT")

❌ **Bad Error Messages:**
- Technical jargon
- Database queries
- File paths
- Stack traces

### Examples:

**Good:**
```
មិនអាចទាញយក Excel បានទេ។ សូមព្យាយាមម្តងទៀត ឬទំនាក់ទំនង IT។
Unable to export Excel. Please try again or contact IT.
```

**Bad:**
```
SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
at vendor/laravel/framework/src/Illuminate/Database/Connection.php:712
```

---

## 9. Current Status

### ✅ Implemented:
- Try-catch blocks in all export methods
- Error logging to Laravel log
- Friendly bilingual error messages
- User redirected back with error message

### 📋 Recommended for Production:
- Set `APP_DEBUG=false` in `.env`
- Create custom 404/500 error pages
- Set up Telegram error notifications (optional)
- Configure log rotation (already automatic with 'daily' driver)

---

## 10. Testing Error Handling

### Test Scenarios:

1. **Invalid Data:**
   - Try export with corrupted database
   - Expected: Friendly error message

2. **Permission Issues:**
   - Remove write permissions from storage
   - Expected: Friendly error message

3. **Memory Limit:**
   - Export very large dataset
   - Expected: Friendly error message or success

4. **Network Issues:**
   - Disconnect database during export
   - Expected: Friendly error message

### Test Command:
```bash
# Temporarily set debug to false to test production behavior
APP_DEBUG=false php artisan serve
```

---

## Summary

**Development (Current):**
- `APP_DEBUG=true` - See all errors for debugging
- Errors show full details
- Good for development and testing

**Production (Recommended):**
- `APP_DEBUG=false` - Hide technical details
- Errors logged to files
- Users see friendly messages
- IT/developers check logs for issues

**Current Implementation:**
- ✅ All export functions have error handling
- ✅ Errors are logged
- ✅ Users see friendly messages
- ✅ Bilingual error messages
- ✅ System stays stable even when errors occur

**Next Step:**
When deploying to production, simply change `.env`:
```
APP_DEBUG=false
```

All error handling is already in place! 🚀
