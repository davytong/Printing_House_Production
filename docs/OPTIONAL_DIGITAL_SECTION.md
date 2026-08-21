# Optional Digital Printing Section

## Date: June 27, 2026

## Feature: Toggle Digital Section in Report

### Problem Solved
**User Need**: 
- Digital printing doesn't run every day
- Don't want to insert fake digital books into database
- Want ability to show/hide Digital section in report as needed

**Solution**: 
Added checkbox toggle in preview modal - controls whether Digital Printing section appears in report **WITHOUT touching database**

## How It Works

### UI Added
**Location**: Preview Modal  
**Control**: Toggle switch "បង្ហាញ Digital Printing"

```
┌─────────────────────────────────────────┐
│ ជ្រើស Level / Grade    [Level 1▼]     │
│ បង្ហាញ Digital Printing [Toggle ▢]     │
│   ↳ បើគ្មាន digital នឹងបង្ហាញ "គ្មាន"  │
└─────────────────────────────────────────┘
```

### Behavior

**Toggle OFF (Default for now)**:
```
━━━━━━━━━━【Offset Press】━━━━━━━━━━
[Shows offset books]

━━━━━━━━━━【បូកសរុប】━━━━━━━━━━
[Grand total]
```

**Toggle ON**:
```
━━━━━━━━━━【Digital Printing】━━━━━━━━━━
គ្មាន                    ← Shows "គ្មាន" if no digital books

━━━━━━━━━━【Offset Press】━━━━━━━━━━
[Shows offset books]

━━━━━━━━━━【បូកសរុប】━━━━━━━━━━
[Grand total]
```

If you HAD digital books:
```
━━━━━━━━━━【Digital Printing】━━━━━━━━━━
[Shows digital books here]

━━━━━━━━━━【Offset Press】━━━━━━━━━━
[Shows offset books]

━━━━━━━━━━【បូកសរុប】━━━━━━━━━━
[Grand total]
```

## Implementation

### 1. Frontend (Modal)
**File**: `resources/views/Printing/report.blade.php`

Added toggle switch:
```html
<div class="form-check form-switch">
  <input class="form-check-input" type="checkbox" 
         id="showDigitalSection" 
         onchange="loadPreviewWithFilter()">
  <label class="form-check-label">
    បង្ហាញផ្នែក Digital
  </label>
</div>
```

### 2. JavaScript
**File**: `resources/views/Printing/report.blade.php`

```javascript
const showDigital = document.getElementById('showDigitalSection').checked;
url += `&show_digital=${showDigital ? '1' : '0'}`;
```

### 3. Backend Service
**File**: `app/Services/DailyReportService.php`

```php
public function generateDailyReport(
    ?string $date = null, 
    ?int $batchId = null, 
    ?string $grade = null, 
    bool $showDigital = true  // NEW parameter
): string {
    // ...
    
    // Digital section - only show if enabled
    if ($showDigital) {
        $report .= $this->buildSectionDivider('Digital Printing');
        if ($digitalBooks->isEmpty()) {
            $report .= "គ្មាន\n\n";
        } else {
            $report .= $this->buildBooksSection($digitalBooks->groupBy('grade'), $date);
        }
    }
    
    // Offset section - always shown
    $report .= $this->buildSectionDivider('Offset Press');
    // ...
}
```

### 4. Controller
**File**: `app/Http/Controllers/PrintingController.php`

```php
$showDigital = $request->input('show_digital', '1') === '1';
$report = $reportService->generateDailyReport($date, $batchId, $grade, $showDigital);
```

## Usage

### Day-to-Day Use:

**Scenario 1: Normal Day (No Digital)**
1. Open Preview Modal
2. Leave toggle **OFF**
3. Report shows only Offset Press section
4. Copy & send to Telegram

**Scenario 2: Special Day (With Digital)**
1. Open Preview Modal
2. Turn toggle **ON**
3. Report shows both Digital (គ្មាន) and Offset sections
4. Copy & send to Telegram

**Scenario 3: Future - Actually Running Digital**
1. Add digital books to database via CSV (printing_method = 'digital')
2. Turn toggle **ON**
3. Report shows both sections with actual digital book data

## Benefits

✅ **No Database Changes Needed** - Toggle is just for display  
✅ **Flexible Reporting** - Show/hide as needed per day  
✅ **No Errors** - Even with toggle ON, just shows "គ្មាន" if no digital books  
✅ **Future Ready** - When you add digital books, toggle will show real data  
✅ **Clean Reports** - Don't show empty sections unnecessarily  

## Files Modified

1. **resources/views/Printing/report.blade.php**
   - Added toggle switch UI
   - Updated JavaScript to send show_digital parameter

2. **app/Services/DailyReportService.php**
   - Added $showDigital parameter
   - Conditional rendering of Digital section

3. **app/Http/Controllers/PrintingController.php**
   - Added show_digital parameter handling
   - Defaults to '1' (show) for backward compatibility

## Default Behavior

**Default**: Toggle is **OFF** (unchecked)  
**Reason**: Since digital doesn't run always, don't show it by default  
**Override**: Turn ON when you need to include Digital section

## Future Enhancement

When you actually start using digital printing regularly:

1. Add digital books via CSV:
   ```csv
   Title,Category,Target,Printed,Grade,PrintingMethod
   Digital Book 1,staple,500,0,Level 1,digital
   ```

2. Toggle ON in preview

3. Report will show actual digital book progress instead of "គ្មាន"

## Testing

```bash
# Test with toggle OFF
curl "http://localhost/report/daily?show_digital=0"
# Should NOT show Digital Printing section

# Test with toggle ON
curl "http://localhost/report/daily?show_digital=1"
# Should show Digital Printing section (គ្មាន if no digital books)
```

## Summary

This feature gives you **complete control** over whether the Digital Printing section appears in your daily report, without forcing you to add fake data to your database. Toggle it on when you need it, keep it off when you don't! 🎯
