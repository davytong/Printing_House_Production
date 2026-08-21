# 🧪 Schedule Logic - Test Scenarios

## ✅ Core Logic Rules

### 1. **Weekend Skipping**
- **Saturday (6) and Sunday (0)** are automatically skipped
- Tasks only occupy working days (Monday-Friday)
- Weekend days remain empty (yellow background)

### 2. **Sequential Task Placement**
- Tasks are placed **in order** as entered
- **Task 2 starts after Task 1 ends**
- Each task occupies consecutive working days

### 3. **Multi-Day Tasks**
- A task spanning multiple days shows: `Task (1/3)`, `Task (2/3)`, `Task (3/3)`
- Each part occupies one working day
- Weekends don't count toward the day count

---

## 📅 Test Scenario 1: Simple Sequential Tasks

### Input
```
Start Day: Thursday, 10/07/2026
Process: Press

Task 1: RSS TX Pre4 - 2 days
Task 2: Listening TB - 3 days
```

### Expected Result
```
Thu 10/07: RSS TX Pre4 (1/2)
Fri 11/07: RSS TX Pre4 (2/2)
Sat 12/07: [SKIPPED - Weekend]
Sun 13/07: [SKIPPED - Weekend]
Mon 14/07: Listening TB (1/3)
Tue 15/07: Listening TB (2/3)
Wed 16/07: Listening TB (3/3)
```

### Total Working Days: 5 days
### Total Calendar Days: 7 days (including weekend)

---

## 📅 Test Scenario 2: Children Mode Calculation

### Input
```
Start Day: Monday, 14/07/2026
Process: Binding

Task 1: All Cover
  - ចំនួនកូន: 50
  - ក្នុង១ថ្ងៃ: 25
  - Calculated: 2 days

Task 2: Reading Workbook
  - ចំនួនកូន: 150
  - ក្នុង១ថ្ងៃ: 25
  - Calculated: 6 days
```

### Expected Result
```
Mon 14/07: All Cover (1/2)
Tue 15/07: All Cover (2/2)
Wed 16/07: Reading Workbook (1/6)
Thu 17/07: Reading Workbook (2/6)
Fri 18/07: Reading Workbook (3/6)
Sat 19/07: [SKIPPED - Weekend]
Sun 20/07: [SKIPPED - Weekend]
Mon 21/07: Reading Workbook (4/6)
Tue 22/07: Reading Workbook (5/6)
Wed 23/07: Reading Workbook (6/6)
```

### Total Working Days: 8 days
### Total Calendar Days: 10 days (including weekend)

---

## 📅 Test Scenario 3: Mixed Days and Children Mode

### Input
```
Start Day: Wednesday, 10/07/2026
Process: Folding

Task 1: Listening TB (manual) - 2 days
Task 2: Reading TB (children)
  - ចំនួនកូន: 100
  - ក្នុង១ថ្ងៃ: 30
  - Calculated: 4 days (100÷30=3.33→4)
Task 3: Writing (manual) - 1 day
```

### Expected Result
```
Wed 10/07: Listening TB (1/2)
Thu 11/07: Listening TB (2/2)
Fri 12/07: Reading TB (1/4)
Sat 13/07: [SKIPPED - Weekend]
Sun 14/07: [SKIPPED - Weekend]
Mon 15/07: Reading TB (2/4)
Tue 16/07: Reading TB (3/4)
Wed 17/07: Reading TB (4/4)
Thu 18/07: Writing
```

### Total Working Days: 7 days
### Total Calendar Days: 9 days

---

## 📅 Test Scenario 4: Starting on Friday (Weekend Boundary)

### Input
```
Start Day: Friday, 12/07/2026
Process: Press

Task 1: Cover Printing - 3 days
Task 2: Text Pages - 2 days
```

### Expected Result
```
Fri 12/07: Cover Printing (1/3)
Sat 13/07: [SKIPPED - Weekend]
Sun 14/07: [SKIPPED - Weekend]
Mon 15/07: Cover Printing (2/3)
Tue 16/07: Cover Printing (3/3)
Wed 17/07: Text Pages (1/2)
Thu 18/07: Text Pages (2/2)
```

### Note
- Task 1 starts Friday, resumes Monday after weekend
- Task 2 immediately follows after Task 1 ends

---

## 📅 Test Scenario 5: Large Quantity with Small Daily Capacity

### Input
```
Start Day: Monday, 07/07/2026
Process: Press

Task: Grade 1 Textbook
  - ចំនួនកូន: 500
  - ក្នុង១ថ្ងៃ: 50
  - Calculated: 10 days
```

### Expected Result
```
Week 1:
Mon 07/07: Grade 1 Textbook (1/10)
Tue 08/07: Grade 1 Textbook (2/10)
Wed 09/07: Grade 1 Textbook (3/10)
Thu 10/07: Grade 1 Textbook (4/10)
Fri 11/07: Grade 1 Textbook (5/10)
Sat 12/07: [SKIPPED - Weekend]
Sun 13/07: [SKIPPED - Weekend]

Week 2:
Mon 14/07: Grade 1 Textbook (6/10)
Tue 15/07: Grade 1 Textbook (7/10)
Wed 16/07: Grade 1 Textbook (8/10)
Thu 17/07: Grade 1 Textbook (9/10)
Fri 18/07: Grade 1 Textbook (10/10)
```

### Total Working Days: 10 days
### Total Calendar Days: 12 days (2 weekend days skipped)

---

## 📅 Test Scenario 6: Month Boundary (End of Month)

### Input
```
Start Day: Thursday, 30/07/2026 (near end of July)
Process: Cutting

Task 1: Final Trimming - 2 days
Task 2: Packaging - 3 days
```

### Expected Behavior
```
Thu 30/07: Final Trimming (1/2)
Fri 31/07: Final Trimming (2/2)
Sat 01/08: [SKIPPED - Weekend + Next Month]
Sun 02/08: [SKIPPED - Weekend + Next Month]
Mon 03/08: Packaging (1/3) [Next Month]
Tue 04/08: Packaging (2/3)
Wed 05/08: Packaging (3/3)
```

### Note
Tasks can overflow to next month if needed

---

## 🔍 Edge Cases to Test

### Edge Case 1: Single Day Task on Friday
```
Input: Friday, 1 day task
Expected: Occupies Friday only
Next task: Starts Monday
```

### Edge Case 2: Zero or Negative Values
```
Input: ចំនួនកូន: 0 or negative
Expected: Use manual days or default to 1 day
```

### Edge Case 3: Empty Daily Capacity
```
Input: ចំនួនកូន: 100, ក្នុង១ថ្ងៃ: (empty)
Expected: Fall back to manual days
```

### Edge Case 4: Very Large Numbers
```
Input: ចំនួនកូន: 10000, ក្នុង១ថ្ងៃ: 50
Calculation: 10000 ÷ 50 = 200 days
Expected: Scheduled across multiple months
Warning: Consider splitting into multiple tasks
```

---

## ✅ Controller Logic Checklist

### Input Processing
- ✅ Parse comma-separated tasks
- ✅ Extract `[Xd]` for days
- ✅ Extract `{qty|capacity/day}` for children mode
- ✅ Clean task names (remove markers)

### Day Calculation
- ✅ Check if children mode active (total + daily present)
- ✅ Calculate: `days = ceil(total ÷ daily)`
- ✅ Use manual days if children mode not active
- ✅ Default to 1 day if nothing specified

### Scheduling
- ✅ Start from input day
- ✅ For each task:
  - ✅ Loop through required days
  - ✅ Check day of week (Carbon->dayOfWeek)
  - ✅ Skip if Saturday (6) or Sunday (0)
  - ✅ Place task on working day
  - ✅ Label multi-day tasks: `(1/3)`, `(2/3)`, etc.
- ✅ Move to next working day for next task
- ✅ Create database records
- ✅ Clear old data for affected days

### Weekend Detection
```php
$dayOfWeek = Carbon::createFromDate($year, $month, $day)->dayOfWeek;

// 0 = Sunday
// 1 = Monday
// 2 = Tuesday
// 3 = Wednesday
// 4 = Thursday
// 5 = Friday
// 6 = Saturday

if ($dayOfWeek === 0 || $dayOfWeek === 6) {
    // Skip this day
    continue;
}
```

---

## 🎯 Expected UI Behavior

### Calendar Grid
- Weekend columns (Sat/Sun) show **yellow background**
- Tasks appear only in working day columns
- Empty weekend cells remain empty

### Task Input
- Enter task name
- Choose mode:
  - **Manual**: Enter days directly
  - **Children**: Enter total children + daily capacity
- Auto-calculation shows result
- Multiple tasks stack sequentially

### After Save
- Tasks distributed across working days
- Weekends automatically skipped
- Clear day labels: `(1/3)`, `(2/3)`, etc.
- Success message shows total days saved

---

## 🐛 Common Issues to Watch For

### Issue 1: Weekend Not Skipped
**Symptom:** Tasks appear on Saturday/Sunday
**Fix:** Check `dayOfWeek` comparison (0 and 6, not 1 and 7)

### Issue 2: Tasks Overlap
**Symptom:** Task 2 starts same day as Task 1
**Fix:** Ensure `$currentDay` advances after each task

### Issue 3: Incorrect Day Count
**Symptom:** 3-day task only occupies 2 days
**Fix:** Use `while` loop instead of `for` loop to handle weekend skipping

### Issue 4: Month Overflow
**Symptom:** Tasks disappear if they exceed month end
**Fix:** Allow overflow to next month OR warn user

### Issue 5: Children Calculation Wrong
**Symptom:** 100÷25 shows 3 days instead of 4
**Fix:** Use `Math.ceil()` in JavaScript and `ceil()` in PHP

---

## 📊 Data Format Examples

### Stored in Database

```php
// Simple manual mode
'task' => 'Listening Textbook (1/2)'

// Children mode with metadata
'task' => 'Listening Textbook (1/4) {100|25/day}'
```

### Parsing Format
```
Pattern: TaskName [days]d {totalChildren|dailyCapacity/day}

Examples:
- "Cover [2d]"                    → 2 days, no children tracking
- "Text {500|50/day}"             → Auto-calc: 10 days
- "Text [10d] {500|50/day}"       → Override to 10 days + tracking
- "Cover [2d], Text [3d]"         → Multiple tasks
```

---

## 🚀 Performance Considerations

### Database Efficiency
- Delete old cells before inserting new ones
- Use `whereIn('day', $affectedDays)` for batch delete
- Create (not updateOrCreate) for cleaner logic

### Frontend Responsiveness
- Real-time calculation in JavaScript
- Visual feedback for auto-calculated days
- Color-coded result display

---

**សរុប (Summary):** The system now correctly skips weekends, places tasks sequentially, and handles both manual days and auto-calculated children mode! 🎉
