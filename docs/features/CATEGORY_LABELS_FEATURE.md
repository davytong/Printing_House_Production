# Category Labels Editor Feature

## Overview
Implemented editable category labels for Telegram daily report messages. Users can now customize category names (e.g., change "Film (ហ្វីម)" to "Laminate Film") through the UI instead of editing code.

## Implementation Date
June 27, 2026

## Features

### 1. Editable Category Labels
- **Paper Label**: Customizable name for paper category
- **Film Label**: Customizable name for film category (e.g., "Laminate Film")
- **Consumable Label**: Customizable name for consumable category
- All labels support bilingual text (Khmer + English)

### 2. UI Location
- **Path**: Telegram Setup page → Category Labels Editor section
- **Route**: `/telegram` → "កែប្រែ Category Labels" panel
- **Position**: Between "Daily Report Template" and "Registered Groups" sections

### 3. Functionality
- Edit all 3 category labels through text inputs
- Save button stores labels in database
- Reset button restores default values
- Labels automatically apply to all daily report messages
- Preview example shown in info box

### 4. Default Values
```
Paper:      ក្រដាស (Paper)
Film:       Film (ហ្វីម)
Consumable: Consumable (សម្ភារៈប្រើប្រាស់)
```

## Files Modified

### 1. Controller
**File**: `app/Http/Controllers/TelegramSetupController.php`
- Added `saveCategoryLabels()` method - saves labels to database
- Added `resetCategoryLabels()` method - restores defaults
- Updated `index()` method to pass category labels to view

### 2. Routes
**File**: `routes/web.php`
- Added `POST /telegram/category-labels` → saveCategoryLabels
- Added `POST /telegram/category-labels/reset` → resetCategoryLabels

### 3. View
**File**: `resources/views/telegram/setup.blade.php`
- Added Category Labels Editor panel with:
  - 3 input fields (Paper, Film, Consumable)
  - Save button
  - Reset button
  - Helper text showing defaults
  - Info box with example usage

### 4. Movement Controller
**File**: `app/Http/Controllers/Stock/MovementController.php`
- Updated `sendDailyTelegram()` method:
  - Changed from hardcoded labels to reading from Settings
  - Uses `\App\Models\Setting::get('category_label_film', 'Film (ហ្វីម)')`
- Updated `dailyStore()` success message:
  - Now uses dynamic labels from settings

## Database Storage
Labels are stored in `settings` table using the `Setting` model:
- `category_label_paper`
- `category_label_film`
- `category_label_consumable`

## Usage Example

### Before Customization
```
🎞️ Film (ហ្វីម) នៅសល់មានចំនួន:
- Glossy Film — ហ្វីមរលោង : 9 roll
- Matte Film — ហ្វីមម៉ាត់ : 37 roll
```

### After Changing Film Label to "Laminate Film"
```
🎞️ Laminate Film នៅសល់មានចំនួន:
- Glossy Film — ហ្វីមរលោង : 9 roll
- Matte Film — ហ្វីមម៉ាត់ : 37 roll
```

## How to Use

1. **Navigate** to Telegram Setup page (`/telegram`)
2. **Find** "កែប្រែ Category Labels" panel
3. **Edit** any of the 3 category labels
4. **Click** "រក្សាទុក Labels" to save
5. **Test** by sending a daily report - new label will appear

## Reset to Defaults

1. **Click** "លំនាំដើម (Reset)" button
2. **Confirm** the reset action
3. All labels restored to default values

## Benefits

✅ **Flexible**: Each installation can use their own terminology
✅ **User-friendly**: No code editing required
✅ **Bilingual**: Supports Khmer and English text
✅ **Consistent**: Changes apply to all daily reports automatically
✅ **Safe**: Reset button available to restore defaults
✅ **Professional**: Clean UI with visual feedback

## Technical Notes

- Labels are retrieved on each message send (no caching)
- Changes take effect immediately
- Supports any UTF-8 text (Khmer, English, mixed)
- Maximum length: 100 characters per label
- Validation ensures labels cannot be empty

## Related Features

- Daily Report Template Editor
- Low-Stock Alert Template Editor
- Telegram Group Management
- Daily Stock Updates

## Status
✅ **COMPLETE** - All features implemented and tested
