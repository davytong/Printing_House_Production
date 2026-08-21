# Fix Batch 1 Status - Show as Suspended

## Issue
Batch 1 shows "✓ Completed" but it's only 70% done (34,060 / 48,900).

## Solution
Update the database to mark Batch 1 as "suspended" instead of "completed".

---

## Method 1: Using Tinker (Recommended)

```bash
php artisan tinker
```

Then run:
```php
$batch = App\Models\ProductionBatch::find(1);
$batch->status = 'suspended';
$batch->completed_at = null;
$batch->save();
echo "Batch 1 updated to suspended status\n";
exit;
```

---

## Method 2: Direct SQL

Run this SQL query in your database:

```sql
UPDATE production_batches 
SET status = 'suspended',
    completed_at = NULL
WHERE id = 1;
```

---

## Method 3: One-Line Command

```bash
php artisan tinker --execute="App\Models\ProductionBatch::find(1)->update(['status' => 'suspended', 'completed_at' => null]); echo 'Updated';"
```

---

## After Update

**Before:**
```
Batch 1  | ✓ Completed  | 48 | 48,900 | 34,060 | 70% | 23 Jun 2026 | 24 Jun 2026
```

**After:**
```
Batch 1  | ⏸ Suspended  | 48 | 48,900 | 34,060 | 70% | 23 Jun 2026 | ⏸ Suspended
```

---

## Verify

After running the update, refresh your dashboard and you should see:
- Batch 1 with **⏸ Suspended** badge (amber/yellow color)
- Completed column shows "⏸ Suspended" instead of a date
- Progress bar remains at 70% (amber color)

---

## When to Use Each Status

| Status | Progress | Use Case |
|--------|----------|----------|
| `active` | Any % | Currently working on this batch |
| `suspended` | <100% | Work paused before completion |
| `completed` | 100% | Batch fully finished |
| `pending` | 0% | Not started yet |

---

**Current Situation:**
- Batch 1: 70% complete → Should be "suspended" ✅
- Batch 2: 3% complete, active → Already correct ✅
