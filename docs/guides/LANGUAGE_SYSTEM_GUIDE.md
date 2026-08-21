# Language Switching System - Implementation Guide

## Overview
The PrintTracker Pro system now supports dynamic language switching between **Khmer (ខ្មែរ)** and **English** across the entire application.

## How It Works

### 1. Language Storage
- Current language is stored in the **session** (`app_locale`)
- Default language: **Khmer (km)**
- Available languages: **km** (Khmer) and **en** (English)

### 2. Middleware
`app/Http/Middleware/SetLanguage.php` runs on every request and:
- Reads the `app_locale` from session
- Sets the application locale using `App::setLocale()`
- Defaults to `km` if no language is set

### 3. Language Switcher UI
Located in the **topbar** (top-right corner):
- 🇰🇭 ខ្មែរ - Switches to Khmer
- 🇬🇧 EN - Switches to English
- Active language is highlighted with primary color
- Clicking switches language and reloads the page

### 4. Translation Files
Located in `lang/` directory:
- `lang/km/common.php` - Khmer translations
- `lang/en/common.php` - English translations

### 5. Helper Functions

#### `t($key, $file = 'common')`
Returns bilingual or single language based on current locale:
- **Khmer mode**: Returns "ខ្មែរ — English" format
- **English mode**: Returns "English" only

Example:
```php
{{ t('dashboard') }}
// Khmer mode: "ទំព័រដើម — Dashboard"
// English mode: "Dashboard"
```

#### `trans_only($key, $file = 'common')`
Returns translation in current language only:
```php
{{ trans_only('dashboard') }}
// Khmer mode: "ទំព័រដើម"
// English mode: "Dashboard"
```

## Implementation Status

### ✅ Completed
1. **Language Infrastructure**
   - SetLanguage middleware registered and working
   - LanguageController with switch method
   - Language helper functions (t, trans_only)
   - Translation files with comprehensive keys
   - Language switcher UI in topbar
   - Composer autoload configured

2. **Sidebar Menu - Fully Translated**
   - Overview section (Dashboard, Analytics)
   - Production section (Printing, Reports, Print Requests, Production Schedule)
   - Procurement section (Requests, Purchase Orders, Suppliers)
   - Stock section (Paper Report, Film Report, Consumable Report, Materials List, Movement History, Stock Reports)
   - Equipment section (Machines & Maintenance)
   - System section (Notifications, Telegram Bot)

### 🔄 Next Steps (To Be Implemented)

1. **Update All Forms** - Replace hardcoded bilingual text with `t()` function:
   ```php
   <!-- OLD -->
   <label>ថ្ងៃខែ — Date *</label>
   
   <!-- NEW -->
   <label>{{ t('date') }} *</label>
   ```

2. **Update Page Titles and Headers**
   ```php
   <!-- OLD -->
   <h1>ស្នើរសុំទិញ — New Procurement Request</h1>
   
   <!-- NEW -->
   <h1>{{ t('new_request') }} — {{ t('procurement') }}</h1>
   ```

3. **Update Table Headers**
   ```php
   <!-- OLD -->
   <th>ថ្ងៃខែ — Date</th>
   
   <!-- NEW -->
   <th>{{ t('date') }}</th>
   ```

4. **Update Button Labels**
   ```php
   <!-- OLD -->
   <button>រក្សាទុក — Save</button>
   
   <!-- NEW -->
   <button>{{ t('save') }}</button>
   ```

5. **Update Status Badges and Messages**
   ```php
   <!-- OLD -->
   Pending — រង់ចាំ
   
   <!-- NEW -->
   {{ t('pending') }}
   ```

6. **Add More Translation Keys** as needed for specific pages

## Translation Keys Currently Available

### Menu & Navigation
- overview, dashboard, analytics
- production, procurement, stock, equipment, system
- printing, printing_report, print_requests, production_schedule
- requests, purchase_orders, suppliers
- materials, materials_list, stock_movements, stock_reports
- paper_report, film_report, consumable_report
- machines_maintenance
- notifications, telegram_bot

### Actions
- add, edit, delete, save, cancel, submit, back
- search, filter, export, import, print, download, upload
- view, close, refresh

### Common Fields
- name, description, date, time, status, category
- quantity, price, total, unit, remarks, notes
- created_at, updated_at

### Status
- active, inactive, pending, approved, rejected
- completed, cancelled

### Priority
- low, medium, high, urgent

### Messages
- success, error, warning, info
- confirm_delete, no_data, loading

### Form
- required, optional, select, choose_file, no_file_chosen

### Pagination
- showing, to, of, results, previous, next

## Usage Examples

### In Blade Templates
```php
{{-- Menu item --}}
<a href="{{ route('dashboard') }}">
  <i class="bi bi-speedometer2"></i>
  <span>{{ t('dashboard') }}</span>
</a>

{{-- Section label (uppercase, current language only) --}}
<p class="sidebar-section-label">{{ strtoupper(trans_only('production')) }}</p>

{{-- Form label --}}
<label class="form-label">{{ t('name') }} *</label>

{{-- Button --}}
<button class="btn btn-primary">{{ t('save') }}</button>

{{-- Status badge --}}
<span class="badge badge-pending">{{ t('pending') }}</span>

{{-- Page title --}}
<h1>{{ t('new_request') }}</h1>
```

### Adding New Translations

1. **Add to `lang/km/common.php`**:
```php
'my_new_key' => 'ការបកប្រែជាភាសាខ្មែរ',
```

2. **Add to `lang/en/common.php`**:
```php
'my_new_key' => 'English Translation',
```

3. **Use in blade templates**:
```php
{{ t('my_new_key') }}
```

## Testing the Language Switcher

1. Log into the system
2. Look at the top-right corner of the page
3. Click on **🇰🇭 ខ្មែរ** to see Khmer-English bilingual mode
4. Click on **🇬🇧 EN** to see English-only mode
5. Notice the sidebar menu changes according to the selected language

## Benefits

1. **User-Friendly**: Users can choose their preferred language
2. **Maintainable**: All translations centralized in language files
3. **Consistent**: Same translation keys used across the entire application
4. **Flexible**: Easy to add new languages in the future (e.g., French, Chinese)
5. **SEO-Friendly**: Content adapts to user preference without separate URLs

## Future Enhancements

1. **User Preference Storage**: Save language preference to user profile in database
2. **More Languages**: Add support for Thai, Vietnamese, French, etc.
3. **RTL Support**: Add support for right-to-left languages (Arabic, Hebrew)
4. **Date/Number Formatting**: Localize dates and numbers based on locale
5. **Dynamic Content Translation**: Translate user-generated content (e.g., product names, descriptions)

---

**Status**: Language system foundation is complete and working. Sidebar menu is fully translated. Next step is to update forms, tables, and page content to use translation functions.
