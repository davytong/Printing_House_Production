# Fix Telegram Daily Report Template

## Problem
The current saved template is missing the `{emoji}` placeholder, so the category icon (📄 🎞️ 🧴) doesn't appear in reports.

**Current (wrong) template:**
```
សូមគោរពរាយការណ៍ជូនបង ពូ 📩
{date}

{category} នៅសល់មានចំនួន:
{items}
{person}
{hashtag}
```

**Correct template should be:**
```
សូមគោរពរាយការណ៍ជូនបង ពូ 📩
ថ្ងៃទី {date}

{emoji} {category} នៅសល់មានចំនួន:
{items}
{person}
{hashtag}
```

## How to Fix

### Method 1: Reset to Default (Recommended)
1. Go to **Telegram Setup** page (`/telegram`)
2. Find **"Template រាយការណ៍ប្រចាំថ្ងៃ (Daily Report)"** section
3. Click the **"លំនាំដើម (Reset)"** button (red button)
4. Confirm reset
5. Done! ✅

### Method 2: Manual Edit
1. Go to **Telegram Setup** page
2. Find the template editor textarea
3. Add `{emoji}` before `{category}`
4. Add "ថ្ងៃទី " before `{date}` (with space)
5. Your template should look like:
```
សូមគោរពរាយការណ៍ជូនបង ពូ 📩
ថ្ងៃទី {date}

{emoji} {category} នៅសល់មានចំនួន:
{items}
{person}
{hashtag}
```
6. Click **"រក្សាទុក Template"**
7. Done! ✅

## What Changed
- ✅ Added **space** after "ថ្ងៃទី " (was: "ថ្ងៃទី{date}" → now: "ថ្ងៃទី {date}")
- ✅ Ensured **`{emoji}`** is in default template
- ✅ Updated preview to match actual output

## Expected Result

After fixing, your daily reports will look like:
```
សូមគោរពរាយការណ៍ជូនបង ពូ 📩
ថ្ងៃទី 26 ខែមិថុនា ឆ្នាំ 2026

🎞️ Film (ហ្វីម) នៅសល់មានចំនួន:
- Glossy Film — ហ្វីមរលោង : 9 roll
- Matte Film — ហ្វីមម៉ាត់ : 37 roll

👤 Keo pholchomruen Niza
#Film_Stock
```

Notice:
- ✅ Proper spacing: "ថ្ងៃទី 26" (with space)
- ✅ Emoji shows: 🎞️
- ✅ Category shows: Film (ហ្វីម)

## Quick Check
Click "មើលជាមុន" (Preview) button to verify. You should see:
- Icon emoji (🎞️ or 📄 or 🧴)
- Proper spacing in date line
- All placeholders replaced correctly
