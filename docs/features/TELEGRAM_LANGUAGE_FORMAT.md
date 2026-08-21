# Telegram Item Name Language Format Feature

## Overview
Added ability to choose between bilingual or Khmer-only item names in Telegram daily reports. This helps reduce message length and improves readability in Telegram.

## Implementation Date
June 27, 2026

## Problem Solved
- Telegram messages were too long with both English and Khmer names
- Difficult to read long messages on mobile
- Users wanted option to show only Khmer names for cleaner, shorter messages

## Feature Details

### Two Format Options

#### 1. Both Languages (Default) - `both`
Shows English — Khmer format
```
- Glossy Film — ហ្វីមរលោង : 9 roll
- Plate Cleaner — សាបូជូតផ្លាក : 36 bottle
- Woodfree A1 80g — ក្រដាសស A1 80g : 47 pack
```
- **Pros**: Clear, shows both languages
- **Cons**: Longer messages

#### 2. Khmer Only - `khmer`
Shows only Khmer names
```
- ហ្វីមរលោង : 9 roll
- សាបូជូតផ្លាក : 36 bottle
- ក្រដាសស A1 80g : 47 pack
```
- **Pros**: Shorter, easier to read, cleaner
- **Cons**: English names not shown

### Comparison

**Before (Both Languages):**
```
🧴 Consumable (សម្ភារៈប្រើប្រាស់) នៅសល់មានចំនួន:
- Cleaning Sponge — អេប៉ុងជូតផ្លាក : 9 pcs
- GUMME — ទឹកថ្នាំ ហ្គូម : 1 can
- Plate Cleaner — សាបូជូតផ្លាក : 36 bottle
- Printing Powder — ម្ស៉ៅបោះពុម្ព : 13 pack
- Black Ink — ទឹកថ្នាំខ្មៅ : 12 can
- Cyan Ink — ទឹកថ្នាំខៀវ : 16 can
- Magenta Ink — ទឹកថ្នាំក្រហម : 16 can
- Yellow Ink — ទឹកថ្នាំលឿង : 17 can
- Rubber Blanket — កៅស៊ូ : 11 sheet
#Consumable_Stock
```
**Character count: ~450 characters**

**After (Khmer Only):**
```
🧴 Consumable (សម្ភារៈប្រើប្រាស់) នៅសល់មានចំនួន:
- អេប៉ុងជូតផ្លាក : 9 pcs
- ទឹកថ្នាំ ហ្គូម : 1 can
- សាបូជូតផ្លាក : 36 bottle
- ម្ស៉ៅបោះពុម្ព : 13 pack
- ទឹកថ្នាំខ្មៅ : 12 can
- ទឹកថ្នាំខៀវ : 16 can
- ទឹកថ្នាំក្រហម : 16 can
- ទឹកថ្នាំលឿង : 17 can
- កៅស៊ូ : 11 sheet
#Consumable_Stock
```
**Character count: ~280 characters** (38% shorter!)

## How to Use

### Step 1: Navigate to Setting
1. Go to **Telegram Setup** page (`/telegram`)
2. Find **"ទម្រង់ឈ្មោះទំនិញ (Item Name Format)"** panel
3. Located after Category Labels section

### Step 2: Choose Format
Select from dropdown:
- **📚 ភាសាទាំងពីរ — English — ខ្មែរ (លំនាំដើម)** - Both languages
- **🇰🇭 ភាសាខ្មែរតែមួយ — ខ្មែរ (សារខ្លី)** - Khmer only (shorter messages)

### Step 3: Save
Click **"រក្សាទុក"** button

### Step 4: Verify
Send a daily report to see the new format in action!

## UI Features

### Visual Examples
The setup page shows side-by-side comparison:
- **Left box**: Both languages example (current)
- **Right box**: Khmer only example (recommended for shorter messages)

### Icons & Labels
- 📚 Both Languages icon
- 🇰🇭 Khmer Only icon
- ✅ Visual checkmark on recommended option
- Info tooltips explaining each option

## Technical Implementation

### Backend Changes

**1. TelegramSetupController.php**
- Added `$itemNameFormat` variable to index method
- Added `saveItemNameFormat()` method
- Passes format to view

**2. MovementController.php**
- Updated `sendDailyTelegram()` method
- Reads `telegram_item_name_format` setting
- Conditional logic for name display:
  - `'both'`: Shows "English — Khmer"
  - `'khmer'`: Shows only Khmer name

**3. routes/web.php**
- Added `POST /telegram/item-name-format` route

### Database Storage
Setting stored in `settings` table:
- Key: `telegram_item_name_format`
- Values: `'both'` or `'khmer'`
- Default: `'both'`

### Logic Flow
```php
$nameFormat = \App\Models\Setting::get('telegram_item_name_format', 'both');

if ($nameFormat === 'khmer') {
    // Show only Khmer
    $nameDisplay = $it['name_km'] ?: $it['name'];
} else {
    // Show both languages
    $nameDisplay = $it['name_km']
        ? "{$it['name']} — {$it['name_km']}"
        : $it['name'];
}
```

## Benefits

### For Readability
✅ **37-40% shorter messages** with Khmer only
✅ **Easier to read** on mobile devices
✅ **Less scrolling** in Telegram
✅ **Cleaner appearance** without English text

### For Users
✅ **Quick scanning** - find items faster
✅ **Better mobile experience** - fits screen better
✅ **Less data usage** - smaller messages
✅ **Preferred language** - staff knows Khmer names

### For System
✅ **Under Telegram limits** - reduces risk of message size issues
✅ **Faster delivery** - smaller payload
✅ **Flexible** - can switch back anytime
✅ **User choice** - not forced

## When to Use Each Format

### Use "Both Languages" When:
- Staff needs English reference names
- Working with international suppliers
- Training new staff who don't know Khmer names
- Documentation purposes

### Use "Khmer Only" When:
- All staff knows Khmer names
- Messages are too long
- Mobile readability is priority
- Want cleaner, simpler format

## Recommendation

**✅ Use "Khmer Only" for production**
- Most staff knows Khmer names
- Shorter messages = better mobile experience
- Easier to read quickly
- Sufficient for daily operations

**Use "Both Languages" for:**
- Initial setup period
- Training new staff
- When working with new materials

## Files Modified

1. **app/Http/Controllers/TelegramSetupController.php**
   - Added itemNameFormat to index
   - Added saveItemNameFormat method

2. **app/Http/Controllers/Stock/MovementController.php**
   - Updated sendDailyTelegram logic
   - Conditional name display

3. **resources/views/telegram/setup.blade.php**
   - Added language format selector panel
   - Visual examples
   - Save button

4. **routes/web.php**
   - Added item-name-format route

## Testing

### Test Scenarios
1. **Default (Both)**: Messages show "English — Khmer"
2. **Switch to Khmer**: Messages show only Khmer names
3. **Switch back**: Messages restore to both languages
4. **Items without Khmer**: Falls back to English name

### Verification Steps
1. Go to Telegram Setup
2. Change format to "Khmer Only"
3. Save
4. Send daily report for any category
5. Check Telegram message - should show only Khmer names
6. Repeat test with "Both Languages"

## Success Messages

**After Save:**
```
បានកំណត់ទម្រង់ឈ្មោះទំនិញ: ភាសាខ្មែរតែមួយ
(Set item name format: Khmer only)
```

or

```
បានកំណត់ទម្រង់ឈ្មោះទំនិញ: ភាសាទាំងពីរ (English — ខ្មែរ)
(Set item name format: Both languages)
```

## Status
✅ **COMPLETE** - Ready for production use
