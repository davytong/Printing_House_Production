# Film → Laminate (ស្គុត) Terminology Update

## Date
June 27, 2026

## Changes Made

### 1. Database Updates (Migration)
**Migration**: `2026_06_27_030342_update_film_to_laminate_terminology.php`

- Updated all materials with category='film' where name_km contains "ហ្វីម" → replaced with "ស្គុត"
- Updated category_label_film setting from "Film (ហ្វីម)" → "Laminate (ស្គុត)"
- Migration is reversible

**Examples of materials updated:**
```
Before: "Glossy Film — ហ្វីមរលោង"
After:  "Glossy Film — ស្គុតរលោង"

Before: "Matte Film — ហ្វីមម៉ាត់"
After:  "Matte Film — ស្គុតម៉ាត់"
```

### 2. Default Labels Updated
**Controllers updated**:
- `TelegramSetupController.php` - Default changed to "Laminate (ស្គុត)"
- `MovementController.php` - Default changed to "Laminate (ស្គុត)"

### 3. Category-Specific Language Format
**New Feature**: Separate language format for each category

**Before** (Global setting):
- One setting for all categories

**After** (Per-category):
- Paper: Choose "both" or "khmer"
- Laminate: Choose "both" or "khmer"
- Consumable: Choose "both" or "khmer"

**UI Location**: Telegram Setup → "ទម្រង់ឈ្មោះទំនិញ (Item Name Format)"

**Database Keys**:
```
telegram_item_name_format_paper
telegram_item_name_format_film
telegram_item_name_format_consumable
```

## Benefits

### Terminology Accuracy
✅ "ស្គុត" is the correct Khmer term for laminate film
✅ More accurate for staff communication
✅ Better understanding in local language

### Flexibility
✅ Each category can have its own language format
✅ Example: Paper uses both languages, Laminate/Consumable use Khmer only
✅ Reduces message length where needed

## How to Use

### Step 1: Verify Updates
1. Go to **Stock → Materials**
2. Filter by category "film"
3. Check Khmer names now show "ស្គុត" instead of "ហ្វីម"

### Step 2: Configure Language Format
1. Go to **Telegram Setup** (`/telegram`)
2. Find "ទម្រង់ឈ្មោះទំនិញ" section
3. Set format for each category:
   - Paper: Both or Khmer only
   - Laminate: Both or Khmer only ✅ Recommended
   - Consumable: Both or Khmer only ✅ Recommended
4. Click "រក្សាទុកទាំងអស់"

### Step 3: Test
1. Go to **Stock → Daily Update**
2. Select "film" category
3. Enter stock data
4. Send to Telegram
5. Verify message shows "Laminate (ស្គុត)" and correct language format

## Migration Details

**Run**: `php artisan migrate`

**What it does**:
- Replaces "ហ្វីម" with "ស្គុត" in materials.name_km
- Updates category_label_film setting
- Only affects records with "film" category

**Rollback**: `php artisan migrate:rollback`
- Reverts all changes back to "Film (ហ្វីម)"

## Files Modified

### Backend (3 files)
1. **TelegramSetupController.php**
   - Changed default label to "Laminate (ស្គុត)"
   - Updated to support per-category language format
   - Added saveItemNameFormat with 3 separate fields

2. **MovementController.php**
   - Changed default label to "Laminate (ស្គុត)"
   - Updated to use category-specific language format setting

3. **Migration file**
   - Database updates for terminology change

### Frontend (1 file)
4. **telegram/setup.blade.php**
   - Updated to show 3 separate dropdowns (Paper, Laminate, Consumable)
   - Changed "Film" label to "Laminate (ស្គុត)"

### Database
5. **materials table** - name_km column updated
6. **settings table** - category_label_film value updated

## Example Messages

### Before Changes
```
🎞️ Film (ហ្វីម) នៅសល់មានចំនួន:
- Glossy Film — ហ្វីមរលោង : 9 roll
- Matte Film — ហ្វីមម៉ាត់ : 37 roll
#Film_Stock
```

### After Changes (Both Languages)
```
🎞️ Laminate (ស្គុត) នៅសល់មានចំនួន:
- Glossy Film — ស្គុតរលោង : 9 roll
- Matte Film — ស្គុតម៉ាត់ : 37 roll
#Film_Stock
```

### After Changes (Khmer Only)
```
🎞️ Laminate (ស្គុត) នៅសល់មានចំនួន:
- ស្គុតរលោង : 9 roll
- ស្គុតម៉ាត់ : 37 roll
#Film_Stock
```

## Recommended Settings

For cleaner, shorter messages:

**Paper**: Both languages
- English names are standard (A1, A4, etc.)
- Staff knows both names

**Laminate (ស្គុត)**: Khmer only ✅
- Shorter messages
- Staff knows Khmer names well

**Consumable**: Khmer only ✅
- Shorter messages
- Many items, long messages
- Staff knows Khmer names

## Status
✅ **COMPLETE** - Migration run successfully
✅ Database updated
✅ UI updated
✅ Default labels changed
✅ Per-category language format working
