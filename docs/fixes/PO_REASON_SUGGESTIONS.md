# Purchase Order - Reason Suggestions Guide

## ហេតុផលទិញ (Reason for Purchase) - Dropdown Suggestions

---

## How It Works

When creating a new PO, users can:
1. **Select from dropdown** - Choose a common reason
2. **Type custom reason** - Write their own specific reason
3. **Combine both** - Select suggestion then edit/add more details

---

## Available Suggestions by Category

### 🔴 ស្តុកទាប — Low Stock

| Suggestion | When to Use |
|------------|-------------|
| 🔴 ស្តុកសម្ភារៈទាបខ្លាំង - ត្រូវការបន្ទាន់សម្រាប់ផលិតកម្ម | Materials critically low, urgent for production |
| 📄 ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន | Paper stock low, needed for current batch |
| 🎞️ ស្តុក Film អស់ស្ទើរ - ចាំបាច់ទិញបន្ថែម | Film almost depleted, must purchase more |
| 🧴 ស្តុក Consumable ចាំបាច់បញ្ជាទិញឡើងវិញ | Consumables need reorder |

**Use Case**: When low stock alert triggered or approaching minimum stock level

---

### ✨ សម្ភារៈថ្មី — New Materials

| Suggestion | When to Use |
|------------|-------------|
| ✨ ត្រូវការសម្ភារៈថ្មីសម្រាប់ការងារថ្មី | Need new materials for new project |
| 📈 បន្ថែមសម្ភារៈថ្មីដើម្បីបង្កើនផលិតកម្ម | Adding new materials to increase production |
| 🆕 ទិញសម្ភារៈថ្មីសម្រាប់ Batch ថ្មី | Purchase new materials for new batch |

**Use Case**: Starting new projects, expanding production capacity, new batch requirements

---

### 🔄 ជំនួស — Replacement

| Suggestion | When to Use |
|------------|-------------|
| 🔄 ជំនួសសម្ភារៈចាស់ដែលខូច | Replace old/damaged materials |
| 🔧 ជំនួសគ្រឿងបន្លាស់ម៉ាស៊ីនដែលអស់ | Replace worn out machine parts |
| ⚠️ ជំនួសសម្ភារៈគុណភាពទាប | Replace low quality materials |

**Use Case**: Damaged materials, equipment failures, quality issues

---

### 🛠️ ថែទាំ — Maintenance

| Suggestion | When to Use |
|------------|-------------|
| 🛠️ ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ | Buy spare parts for scheduled maintenance |
| 🧹 ទិញសម្ភារៈថែទាំ និងសម្អាតម៉ាស៊ីន | Buy maintenance and cleaning supplies |
| 🚨 ជួសជុលម៉ាស៊ីន - ត្រូវការគ្រឿងបន្លាស់បន្ទាន់ | Machine repair - urgent spare parts needed |

**Use Case**: Preventive maintenance, machine breakdowns, routine servicing

---

### 🌟 ផ្សេងៗ — Other

| Suggestion | When to Use |
|------------|-------------|
| 👥 តម្រូវការពីអតិថិជន - ការបញ្ជាដ៏ធំ | Customer demand - large order |
| 🛡️ បន្ថែមស្តុកសុវត្ថិភាព | Add safety stock |
| 💰 ទិញមុនអស់ពីផ្សារ - តម្លៃល្អ | Buy before out of stock - good price |

**Use Case**: Large orders, strategic purchasing, bulk buying opportunities

---

## Usage Examples

### Example 1: Urgent Paper Stock
```
User selects: 📄 ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន
Textarea fills with: ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន

User can edit/add:
"ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន

ស្តុកបច្ចុប្បន្ន: 50 reams A4
ត្រូវការបន្ថែម: 100 reams
ហេតុផល: Batch 2 ចាប់ផ្តើមថ្ងៃទី 1 ខែកក្កដា"
```

### Example 2: Machine Maintenance
```
User selects: 🛠️ ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ
Textarea fills with: ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ

User adds details:
"ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ

ម៉ាស៊ីន: Printing Machine #3
កាលវិភាគថែទាំ: រៀងរាល់ 6 ខែ
ថ្ងៃចុងក្រោយថែទាំ: ខែមករា 2026
ថ្ងៃថែទាំបន្ទាប់: ខែកក្កដា 2026"
```

### Example 3: Custom Reason
```
User types directly without selecting:
"ទិញក្រដាស A4 គុណភាពខ្ពស់សម្រាប់បោះពុម្ពសៀវភៅវិទ្យាល័យ Level 1
- តម្លៃពីអ្នកផ្គត់ផ្គង់ល្អជាងធម្មតា 10%
- ត្រូវការមុនថ្ងៃទី 5 ខែកក្កដា
- ត្រូវការបន្ទាន់ដើម្បីជៀសវាងការពន្យារពេល"
```

---

## User Experience Flow

### Step 1: See Dropdown
```
┌──────────────────────────────────────────┐
│ ហេតុផលទិញ *                              │
├──────────────────────────────────────────┤
│ [▼ ជ្រើសរើសហេតុផល ឬវាយដោយខ្លួនឯង —]  │
│                                          │
│ [Textarea - empty]                       │
└──────────────────────────────────────────┘
```

### Step 2: Click Dropdown
```
┌──────────────────────────────────────────┐
│ — ជ្រើសរើសហេតុផល ឬវាយដោយខ្លួនឯង —     │
│                                          │
│ ស្តុកទាប — Low Stock                     │
│   🔴 ស្តុកសម្ភារៈទាបខ្លាំង...             │
│   📄 ស្តុកក្រដាសនៅសល់តិច...              │
│   🎞️ ស្តុក Film អស់ស្ទើរ...              │
│                                          │
│ សម្ភារៈថ្មី — New Materials              │
│   ✨ ត្រូវការសម្ភារៈថ្មី...               │
│   ...                                    │
└──────────────────────────────────────────┘
```

### Step 3: After Selection
```
┌──────────────────────────────────────────┐
│ ហេតុផលទិញ *                              │
├──────────────────────────────────────────┤
│ [▼ ជ្រើសរើសហេតុផល... (reset)]          │
│                                          │
│ [Textarea - filled with selection]       │
│ ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់... │
│ ▊ (cursor here for editing)             │
│                                          │
└──────────────────────────────────────────┘
```

---

## JavaScript Functionality

### Function: `fillReason()`

**What it does:**
1. Gets selected value from dropdown
2. Checks if textarea is empty or has content
3. If empty → fills with selected reason
4. If has content → asks user:
   - **Replace** current text (OK button)
   - **Append** to current text (Cancel button)
5. Resets dropdown to default
6. Focuses textarea for user to edit/add more

**Smart Behavior:**
- ✅ Doesn't overwrite user's custom text without asking
- ✅ Allows combining multiple selections
- ✅ User can still type freely
- ✅ Always editable after selection

---

## Benefits

### For Users:
1. ⚡ **Fast** - Click instead of typing
2. 🎯 **Consistent** - Standard reasons across all POs
3. 📝 **Flexible** - Can still customize
4. 🌐 **Bilingual** - Khmer with context

### For Managers:
1. 📊 **Trackable** - Common reasons easy to filter/report
2. ✅ **Complete** - Users less likely to skip reason
3. 🔍 **Searchable** - Standard text easy to search
4. 📈 **Analytics** - Can analyze most common purchase reasons

### For System:
1. 💾 **Data Quality** - Better structured data
2. 🔎 **Filtering** - Can filter POs by reason category
3. 📋 **Reporting** - Generate reports by reason type
4. 🤖 **Automation** - Can auto-classify purchases

---

## Customization

To add/edit suggestions, modify the `<select id="reasonSelect">` in:
```
resources/views/purchase-orders/create.blade.php
```

### Format:
```html
<optgroup label="CATEGORY — Translation">
  <option value="actual_text_to_insert">🔴 Display Text</option>
</optgroup>
```

### Example - Adding New Reason:
```html
<optgroup label="ការកាត់បន្ថយចំណាយ — Cost Reduction">
  <option value="ទិញច្រើនដើម្បីទទួលបានតម្លៃរំលោះ">
    💵 ទិញច្រើនដើម្បីទទួលបានតម្លៃរំលោះ
  </option>
</optgroup>
```

---

## Tips for Users

1. **Start with dropdown** - See if there's a match
2. **Select closest option** - Then customize details
3. **Add specifics** - Include quantities, dates, machine numbers
4. **Be clear** - Help managers understand urgency
5. **Multiple selections** - Can combine reasons (appends with newline)

---

## Visual Guide

```
Before Selection:
┌────────────────────────────────────┐
│ Dropdown: — Select reason —        │
│ Textarea: [Empty]                  │
└────────────────────────────────────┘

After Selection:
┌────────────────────────────────────┐
│ Dropdown: — Select reason —        │
│ Textarea: ស្តុកក្រដាសនៅសល់តិច...   │
│           ▊                        │
└────────────────────────────────────┘

After User Adds More:
┌────────────────────────────────────┐
│ Dropdown: — Select reason —        │
│ Textarea: ស្តុកក្រដាសនៅសល់តិច...   │
│           ស្តុកបច្ចុប្បន្ន: 50 reams│
│           ត្រូវការបន្ថែម: 100      │
│           ▊                        │
└────────────────────────────────────┘
```

---

## Summary

✅ **15 predefined reasons** across 5 categories
✅ **Smart fill** with replace/append options
✅ **Fully editable** after selection
✅ **Emoji icons** for visual clarity
✅ **Bilingual** Khmer + English
✅ **User-friendly** dropdown + freeform textarea
✅ **Flexible** can use suggestions or write custom

**Result**: Faster PO creation with better data quality! 🚀

---

Last Updated: June 27, 2026
