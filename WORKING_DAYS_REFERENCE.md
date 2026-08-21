# 📅 Working Days Reference - កាលវិភាគផលិតកម្ម

## ⏰ Working Schedule

### Your Working Days
```
ច័ន្ទ (Monday)    ✅ Work
អង្គារ (Tuesday)   ✅ Work  
ពុធ (Wednesday)  ✅ Work
ព្រហស្បតិ៍ (Thursday) ✅ Work
សុក្រ (Friday)    ✅ Work
សៅរ៍ (Saturday)   ✅ Work
អាទិត្យ (Sunday)    ❌ HOLIDAY
```

### Total Working Days per Week: **6 days**
### Holiday: **Sunday only**

---

## 🔢 Day of Week Values (Carbon)

```php
0 = Sunday    ❌ Skip (Holiday)
1 = Monday    ✅ Work
2 = Tuesday   ✅ Work
3 = Wednesday ✅ Work
4 = Thursday  ✅ Work
5 = Friday    ✅ Work
6 = Saturday  ✅ Work
```

---

## 📊 Example Calculations

### Example 1: Starting Thursday
```
Input: 
  Start: Thursday (ព្រហស្បតិ៍)
  Task: 3 days

Schedule:
  Day 1: Thursday ✅
  Day 2: Friday ✅
  Day 3: Saturday ✅
  
Total: 3 consecutive working days
```

### Example 2: Starting Saturday
```
Input:
  Start: Saturday (សៅរ៍)
  Task: 4 days

Schedule:
  Day 1: Saturday ✅
  Day 2: Sunday ❌ SKIP
  Day 3: Monday ✅
  Day 4: Tuesday ✅
  Day 5: Wednesday ✅
  
Working Days: 4
Calendar Days: 5 (includes Sunday)
```

### Example 3: Children Mode
```
Input:
  Start: Thursday
  ចំនួនកូន: 120
  ក្នុង១ថ្ងៃ: 20
  Calculation: 120 ÷ 20 = 6 days

Schedule:
  Thu: Day 1 ✅
  Fri: Day 2 ✅
  Sat: Day 3 ✅
  Sun: SKIP ❌
  Mon: Day 4 ✅
  Tue: Day 5 ✅
  Wed: Day 6 ✅
  
Working Days: 6
Calendar Days: 7 (includes 1 Sunday)
```

### Example 4: Multiple Tasks
```
Input:
  Start: Friday
  Task 1: 2 days
  Task 2: 3 days

Schedule:
  Fri: Task 1 (1/2) ✅
  Sat: Task 1 (2/2) ✅
  Sun: SKIP ❌
  Mon: Task 2 (1/3) ✅
  Tue: Task 2 (2/3) ✅
  Wed: Task 2 (3/3) ✅
  
Total Working Days: 5
Total Calendar Days: 6
```

---

## 🎯 Production Capacity per Week

### Maximum Working Days
```
1 Week = 6 working days (Mon-Sat)
2 Weeks = 12 working days
3 Weeks = 18 working days
4 Weeks = 24 working days
```

### Example: Monthly Planning
```
July 2026 has 31 days
Sundays: 5, 12, 19, 26 (4 Sundays)
Working Days: 31 - 4 = 27 working days
```

---

## 📝 Planning Tips

### 1. **Weekly Planning**
```
Week 1 (Mon-Sat): 6 days capacity
Week 2 (Mon-Sat): 6 days capacity
Week 3 (Mon-Sat): 6 days capacity
Week 4 (Mon-Sat): 6 days capacity
Week 5: Remaining days (if any)
```

### 2. **Children Calculation**
```
Daily capacity: 50 children/day
Weekly capacity: 50 × 6 = 300 children/week

Example order:
  Total: 500 children
  Daily: 50 children
  Days needed: 500 ÷ 50 = 10 days
  Weeks needed: 10 ÷ 6 = 1.67 weeks (~2 weeks)
```

### 3. **Avoid Starting on Saturday**
```
❌ Not recommended:
  Start Saturday → Work 1 day → Sunday off → Resume Monday
  (Creates split in same task)

✅ Better:
  Start Monday → Continuous 6-day work week
```

### 4. **Multiple Tasks Sequencing**
```
Good practice:
  - Task 1: Monday-Wednesday (3 days)
  - Task 2: Thursday-Saturday (3 days)
  - Task 3: Monday-Tuesday (2 days, next week)
  
Result: Efficient use of 6-day work weeks
```

---

## 🗓️ Monthly Capacity Examples

### July 2026 (31 days, 4 Sundays)
```
Total Days: 31
Sundays: 5, 12, 19, 26 (4 days)
Working Days: 27 days

At 50 children/day capacity:
  50 × 27 = 1,350 children/month
```

### August 2026 (31 days, 5 Sundays)
```
Total Days: 31
Sundays: 2, 9, 16, 23, 30 (5 days)
Working Days: 26 days

At 50 children/day capacity:
  50 × 26 = 1,300 children/month
```

---

## ⚙️ System Behavior

### Auto-Skip Logic
```php
// System automatically skips Sundays
if ($dayOfWeek === 0) { // 0 = Sunday
    continue; // Skip to next day
}
```

### Task Placement
```
1. Start from input day
2. Check if Sunday → Skip
3. Place task on working day
4. Move to next day
5. Repeat until task days completed
```

### Sequential Tasks
```
Task 1 ends: Saturday
System checks: Next day is Sunday → Skip
Task 2 starts: Monday (automatically)
```

---

## 📊 Quick Reference Table

| Scenario | Start Day | Task Days | Sundays Hit | Calendar Span |
|----------|-----------|-----------|-------------|---------------|
| Short task | Monday | 3 | 0 | 3 days |
| Mid task | Thursday | 5 | 1 | 6 days |
| Long task | Monday | 12 | 2 | 14 days |
| Very long | Monday | 24 | 4 | 28 days |

---

## 🎨 UI Visual Cues

### Calendar Grid
```
Yellow background = Sunday (holiday)
White background = Working day (Mon-Sat)

Example:
[Mon] [Tue] [Wed] [Thu] [Fri] [Sat] [Sun]
 ✅    ✅    ✅    ✅    ✅    ✅    🟨
```

### Task Labels
```
Single day:  "Task Name"
Multi-day:   "Task Name (1/3)", "Task Name (2/3)", "Task Name (3/3)"
With qty:    "Task Name (1/3) {100|25/day}"
```

---

## ✅ Testing Checklist

- [ ] Single day task (no Sunday)
- [ ] Single day task (spans Sunday)
- [ ] Multi-day task (no Sunday)
- [ ] Multi-day task (crosses Sunday)
- [ ] Task starting Saturday
- [ ] Task starting Sunday (should move to Monday)
- [ ] Children mode calculation
- [ ] Multiple sequential tasks
- [ ] Month boundary crossing
- [ ] Full month schedule

---

## 💡 Best Practices

### For Scheduling
1. **Start early in week** (Monday-Tuesday) for long tasks
2. **Group short tasks** together before weekend
3. **Plan around Sundays** - don't split critical tasks
4. **Use children mode** when you know quantities
5. **Review calendar** before saving to check Sunday placements

### For Capacity Planning
1. Calculate **weekly capacity**: Daily × 6 days
2. Calculate **monthly capacity**: Count working days in month
3. **Buffer time**: Don't plan at 100% capacity (allow 10-15% buffer)
4. **Account for setup time**: First day might be slower
5. **Quality checks**: Reserve time for inspection

---

**សង្ខេប (Summary):**
- ធ្វើការ: ច័ន្ទ-សៅរ៍ (6 ថ្ងៃ/សប្តាហ៍)
- ឈប់សម្រាក: អាទិត្យ (1 ថ្ងៃ/សប្តាហ៍)
- System រំលងថ្ងៃអាទិត្យដោយស្វ័យប្រវត្តិ! ✅
