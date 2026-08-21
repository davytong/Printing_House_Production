# 📦 Batch Management Guide

## How to Switch Between Batches

---

## Understanding Batch Status

Your system has different batch statuses:

| Status | Icon | Meaning | Visible in History? |
|--------|------|---------|---------------------|
| **Active** | 🔄 | Currently working on this batch | No (it's the current one) |
| **Suspended** | ⏸ | Paused, will resume later | ✅ Yes |
| **Completed** | ✓ | Finished, archived | ✅ Yes |
| **Pending** | ⏳ | Not started yet | No |

---

## How to Access Old Batches

### On `/printing` Page:

Look for these buttons at the top:

```
┌────────────────────────────────────────────────────────┐
│  [Export]  [Batch 2 (active)]  [ប្រវត្តិ Batch ▼]  [+] │
└────────────────────────────────────────────────────────┘
```

### Step 1: Click "ប្រវត្តិ Batch" Button
This opens a dropdown showing all non-active batches

### Step 2: See Available Batches
```
┌──────────────────────────────────────────┐
│ អតីតកាល Batches — ចុច Switch ដើម្បីត្រឡប់│
├──────────────────────────────────────────┤
│ ⏸ Batch 1    [Switch] [Delete]          │
│   Suspended                              │
│                                          │
│ ✓ Batch 0    [Switch] [Delete]          │
│   23/06/2026                             │
└──────────────────────────────────────────┘
```

### Step 3: Choose Action

#### Option A: **View** (Click batch name)
- Shows batch details
- Read-only view
- See what books were in that batch

#### Option B: **Switch** (Click "Switch" button)
- Makes this batch **active**
- Current batch (Batch 2) gets **saved** automatically
- You can now work on the old batch
- **Confirmation**: "ប្ដូរទៅ Batch 1? Batch បច្ចុប្បន្ននឹងត្រូវរក្សាទុក។"

#### Option C: **Delete** (Click "Delete" button)
- ⚠️ **DANGER**: Permanently deletes batch
- All books and progress data will be lost
- Cannot be undone
- **Confirmation**: "លុប Batch 1 ជាអចិន្ត្រៃយ៍? សៀវភៅ និងលទ្ធផលរបស់វានឹងបាត់បង់ទាំងស្រុង។"

---

## Common Scenarios

### Scenario 1: Resume Working on Batch 1 (Suspended)

**Current State**: Batch 2 is active, Batch 1 is suspended at 70%

**Steps**:
1. Go to `/printing` page
2. Click **"ប្រវត្តិ Batch"** button
3. Find **⏸ Batch 1** (shows as Suspended)
4. Click **"Switch"** button next to Batch 1
5. Confirm: "ប្ដូរទៅ Batch 1?"
6. ✅ Now Batch 1 is active, you can continue printing from 70%

**Result**:
- Batch 1 → **Active** (can add printing records)
- Batch 2 → **Suspended** (saved, can resume later)

---

### Scenario 2: View Batch 1 Without Switching

**Steps**:
1. Go to `/printing` page
2. Click **"ប្រវត្តិ Batch"** button
3. Click on **"Batch 1"** text (not the Switch button)
4. View batch details page (read-only)

**Result**:
- See all books in Batch 1
- See progress (70%)
- See completion dates
- **Current batch stays Batch 2** (no change)

---

### Scenario 3: Work on Multiple Batches

**Example Timeline**:

1. **Start**: Batch 1 active (working)
2. **Pause**: Need to start Batch 2 (urgent)
   - Click "ចាប់ផ្ដើម Batch ថ្មី"
   - Batch 1 becomes **Suspended**
   - Batch 2 becomes **Active**
3. **Later**: Need to finish Batch 1
   - Click "ប្រវត្តិ Batch" → Switch to Batch 1
   - Batch 1 becomes **Active** again
   - Batch 2 becomes **Suspended**
4. **Complete**: Batch 1 reaches 100%
   - Mark as completed
   - Batch 1 becomes **Completed** (archived)
5. **Resume**: Back to Batch 2
   - Click "ប្រវត្តិ Batch" → Switch to Batch 2
   - Batch 2 becomes **Active** again

---

## Visual Guide

### Before Switch (Current State):
```
Active:     Batch 2 🔄 (3% complete)
Suspended:  Batch 1 ⏸ (70% complete) ← You want to work on this
```

### After Clicking "Switch" on Batch 1:
```
Active:     Batch 1 🔄 (70% complete) ← Now working here
Suspended:  Batch 2 ⏸ (3% complete)
```

---

## Updated Features (June 27, 2026)

### ✅ NEW: Suspended Batches Now Visible
- **Before**: Only "completed" batches showed in dropdown
- **After**: Both "suspended" and "completed" batches show
- **Benefit**: Easy to switch back to Batch 1

### ✅ NEW: Visual Status Indicators
- **Suspended**: ⏸ Orange pause icon
- **Completed**: ✓ Green check icon
- **Active**: 🔄 Blue spinning icon

### ✅ NEW: Better Labels
- Dropdown header: "អតីតកាល Batches — ចុច Switch ដើម្បីត្រឡប់"
- Clear "Suspended" label for paused batches
- Date shown for completed batches

---

## Batch Operations Summary

| Action | Button Location | Result | Reversible? |
|--------|----------------|--------|-------------|
| **View Batch** | Click batch name | Read-only view | N/A |
| **Switch Batch** | "Switch" button | Make batch active | ✅ Yes |
| **Delete Batch** | "Delete" button | Permanently remove | ❌ No |
| **Create New Batch** | "ចាប់ផ្ដើម Batch ថ្មី" | Start new batch, current becomes suspended | ✅ Yes |

---

## Frequently Asked Questions

### Q: Can I work on multiple batches at the same time?
**A**: No, only ONE batch can be **active** at a time. Others must be suspended or completed.

### Q: Will I lose my progress when switching batches?
**A**: No! When you switch:
- Current batch is **automatically saved**
- Progress is preserved (70% stays 70%)
- You can switch back anytime

### Q: What's the difference between "Suspended" and "Completed"?
**A**: 
- **Suspended** ⏸: Paused temporarily, will resume later (can be 70% done)
- **Completed** ✓: 100% done, archived (cannot add more records)

### Q: Can I delete an active batch?
**A**: No, you can only delete:
- Suspended batches
- Completed batches
- The active batch cannot be deleted

### Q: How do I complete a batch?
**A**: When all books reach 100%, you can mark the batch as "Completed" (feature should be available in batch settings)

### Q: Can I rename a batch?
**A**: Currently not implemented. Batches are named automatically (Batch 1, Batch 2, etc.)

---

## Safety Features

### ✅ Confirmation Dialogs
- Switching batches: Asks for confirmation
- Deleting batches: ⚠️ Warns about permanent deletion

### ✅ Auto-Save
- Current batch is **automatically saved** when switching
- No manual save needed

### ✅ Data Preservation
- All printing records are preserved
- Progress percentages stay intact
- Book data remains unchanged

---

## Quick Reference

### To Switch to Batch 1:
1. Click **"ប្រវត្តិ Batch"**
2. Find **⏸ Batch 1**
3. Click **"Switch"**
4. Confirm

### To View Batch 1 (Without Switching):
1. Click **"ប្រវត្តិ Batch"**
2. Click **"Batch 1"** (the name, not button)

### To Create New Batch:
1. Click **"ចាប់ផ្ដើម Batch ថ្មី"**
2. Enter batch details
3. Current batch becomes suspended

---

## Current Batch Workflow

```
Start
  ↓
Batch 1 Created (Active)
  ↓
Work on Batch 1... (70% done)
  ↓
Need urgent Batch 2
  ↓
Create Batch 2 → Batch 1 becomes Suspended ⏸
  ↓
Work on Batch 2... (3% done)
  ↓
Need to finish Batch 1
  ↓
Switch to Batch 1 → Batch 1 becomes Active 🔄
  ↓              → Batch 2 becomes Suspended ⏸
Work on Batch 1... (70% → 100%)
  ↓
Batch 1 Completed ✓
  ↓
Switch to Batch 2 → Batch 2 becomes Active 🔄
  ↓
Work on Batch 2... (3% → 100%)
  ↓
Batch 2 Completed ✓
```

---

## Troubleshooting

### Problem: "ប្រវត្តិ Batch" button not showing
**Solution**: This means there are no suspended or completed batches. You only have the active batch.

### Problem: Cannot find Batch 1 in dropdown
**Solution**: Check batch status:
- If Batch 1 is **active**, it won't show (it's the current one)
- If Batch 1 is **pending**, it won't show yet
- If Batch 1 is **suspended** or **completed**, it should show

### Problem: Accidentally deleted a batch
**Solution**: ❌ Cannot recover. Batch deletion is permanent. Be careful with the Delete button!

---

## Summary

✅ **To resume work on Batch 1**: Click "ប្រវត្តិ Batch" → Switch to Batch 1
✅ **Batch 1 will now show in the dropdown** (with ⏸ Suspended icon)
✅ **Your Batch 2 progress is safe** (automatically saved when switching)
✅ **You can switch back and forth** as many times as needed

---

**Last Updated**: June 27, 2026
**Feature Status**: ✅ Suspended batches now visible in history dropdown
