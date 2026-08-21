# 📄 Children (រូបកូន) Mode - Auto-Calculate Duration Feature

## 🎯 Problem Solved

**Before:** "I don't know how many days it will take. I only know I have 100 children to print and can print 25 children per day."

**Now:** Just enter the children quantities and the system **automatically calculates** the days needed!

---

## 📊 What is a "Child" (រូបកូន)?

A **child** (Khmer: រូបកូន) is a printing term meaning:
- One A2 paper sheet
- Contains 8 pages (both sides)
- One unit of production

**Example:**
- 1 child = 1 sheet of A2 paper = 8 pages
- 100 children = 100 sheets = 800 pages

---

## ✨ New Feature: Two Input Modes

### Mode 1: 📅 **Know Days** (Original)
Use when you already know the duration:
```
Task: Listening Textbook
Days: 3
Copies: 150

→ Result: Scheduled for 3 days
```

### Mode 2: 📄 **Know Children** (NEW!)
Use when you know children quantities but not duration:
```
Task: Listening Textbook
Total Children: 100 រូបកូន
Daily Capacity: 25 រូបកូន/day

→ Auto-calculated: 4 days
   (100 ÷ 25 = 4 days)
```

---

## 🔢 How It Works

### Formula
```
Days Needed = ⌈ Total Children ÷ Daily Capacity ⌉
(rounded up to nearest whole day)
```

### Examples

#### Example 1: Perfect Division
```
Total Children: 100
Daily Capacity: 25/day
Calculation: 100 ÷ 25 = 4 days ✅
```

#### Example 2: Needs Rounding
```
Total Children: 100
Daily Capacity: 30/day
Calculation: 100 ÷ 30 = 3.33... → 4 days ✅
(rounded up because you can't do 0.33 of a day)
```

#### Example 3: Multiple Tasks
```
Task 1: Listening TB
- Total: 80 children
- Daily: 20/day
- Result: 4 days (Days 10-13)

Task 2: Reading TB
- Total: 150 children
- Daily: 25/day
- Result: 6 days (Days 14-19)

Task 3: Writing TB (know days directly)
- Days: 2
- Result: 2 days (Days 20-21)
```

---

## 🖥️ User Interface

### Task Row Layout

```
┌─────────────────────────────────────────────────────────┐
│ Task Name: [Listening Textbook ▼]              [✕]     │
├─────────────────────────────────────────────────────────┤
│ Mode: [ 📅 Know Days ] [ 📄 Know Children (រូបកូន) ] │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ When "Know Days" selected:                             │
│   [Days: 3] [Total Qty: 150]                          │
│                                                         │
│ When "Know Children" selected:                         │
│   Total Children (រូបកូន): [100]                      │
│   Daily Capacity:           [25/day]                   │
│   ┌────────────────────────────────────────────┐      │
│   │ 📊 Auto-calculated: 4 days                 │      │
│   │    (100 ÷ 25 = 4 days)                     │      │
│   └────────────────────────────────────────────┘      │
└─────────────────────────────────────────────────────────┘
```

### Toggle Between Modes
- Click **"📅 Know Days"** button → Show simple days input
- Click **"📄 Know Children"** button → Show children calculator

---

## 💾 Data Storage Format

Tasks are stored with metadata to preserve the calculation:

### Format
```
TaskName [XDays] {TotalChildren|DailyCapacity/day}
```

### Examples Stored in Database

#### Children Mode:
```
Listening Textbook [4d] {100|25/day}
                    ↑    ↑   ↑
                    │    │   └── Daily capacity
                    │    └────── Total children
                    └─────────── Auto-calculated days
```

#### Days Mode (original):
```
Reading Workbook [3d] {150}
                 ↑    ↑
                 │    └────── Optional quantity
                 └─────────── Manually entered days
```

---

## 📈 Real-World Scenarios

### Scenario 1: School Textbook Printing
```
Project: Grade 1 Listening Textbook
Total Order: 500 books
Pages per book: 64 pages
Children needed: 500 × (64 ÷ 8) = 4,000 children
Machine capacity: 200 children/day

Input:
  Task: Grade 1 Listening TB
  Mode: Know Children
  Total: 4000
  Daily: 200

Result: Auto-scheduled for 20 days ✅
```

### Scenario 2: Mixed Project
```
Process: Press

Task 1 (know children):
  Name: All Cover
  Total: 50 children
  Daily: 25 children/day
  → Auto: 2 days

Task 2 (know days):
  Name: Maintenance
  Days: 1 day
  → Manual: 1 day

Total scheduled: 3 days automatically calculated
```

### Scenario 3: Variable Daily Capacity
```
Week 1 (full crew):
  Task: Big Project Part 1
  Total: 500 children
  Daily: 100/day
  → 5 days

Week 2 (reduced crew):
  Task: Big Project Part 2
  Total: 500 children
  Daily: 50/day
  → 10 days

System adapts to your actual capacity!
```

---

## 🎨 Visual Workflow

```
START
  │
  ├─ Click Cell → Add Task
  │
  ├─ Choose Mode:
  │   │
  │   ├─ 📅 Know Days?
  │   │   └─ Enter: Days + Optional Qty
  │   │       └─ DONE ✅
  │   │
  │   └─ 📄 Know Children?
  │       └─ Enter: Total Children + Daily Capacity
  │           └─ System calculates days automatically
  │               └─ Shows: "📊 Auto-calculated: X days"
  │                   └─ DONE ✅
  │
  └─ Save → Tasks scheduled across calculated days
```

---

## 🔍 Technical Details

### JavaScript Calculation
```javascript
function updateCalculation() {
    const total = parseFloat(totalChildrenInput.value || 0);
    const daily = parseFloat(dailyChildrenInput.value || 1);
    const calculatedDays = Math.ceil(total / daily);
    
    // Display to user
    display.textContent = calculatedDays + ' days';
    
    // Update hidden field for form submission
    updateTaskHiddenInput();
}
```

### Data Parsing (Backend)
```php
// Parse: "Task [4d] {100|25/day}"
preg_match('/\{([^}]+)\}/', $taskStr, $qtyMatch);

if ($qtyMatch) {
    $parts = explode('|', $qtyMatch[1]);
    $totalChildren = $parts[0];     // 100
    $dailyCapacity = $parts[1];     // "25/day"
    
    // Use for progress tracking
    $totalQuantity = (int) $totalChildren;
}
```

---

## ✅ Benefits

### For Users
✅ **No manual calculation** - Enter children, get days automatically
✅ **Clear tracking** - Know total children and daily progress
✅ **Flexible** - Switch between modes as needed
✅ **Real-time calculation** - See days update as you type

### For Planning
✅ **Realistic scheduling** - Based on actual capacity
✅ **Progress tracking** - Track children printed vs. target
✅ **Adaptable** - Adjust daily capacity if crew changes
✅ **Transparent** - Shows calculation formula

### For Production
✅ **Daily targets** - Know exactly how many children to print
✅ **Quality control** - Track if daily targets are met
✅ **Resource planning** - Schedule based on machine/crew capacity
✅ **Accountability** - Clear metrics for performance

---

## 📝 Usage Tips

### 1. **Choose the Right Mode**
- Know exact duration? → Use **"Know Days"** mode
- Know quantities and capacity? → Use **"Know Children"** mode

### 2. **Estimate Daily Capacity Realistically**
Consider:
- Machine speed
- Crew size
- Quality checks
- Setup/cleanup time
- Typical delays

### 3. **Round Down for Safety**
```
Theoretical capacity: 30 children/day
Realistic capacity: 25 children/day ← Use this
Reason: Accounts for breaks, issues, quality control
```

### 4. **Update Daily Capacity if Conditions Change**
- Machine upgrade → Increase capacity
- Crew reduction → Decrease capacity
- Just re-enter and system recalculates!

### 5. **Mix Both Modes**
Different tasks can use different modes:
- Production tasks → Children mode
- Admin tasks (maintenance, delivery) → Days mode

---

## 🆚 Comparison

### Old Way (Manual)
```
❌ Step 1: Calculate yourself: 100 ÷ 25 = 4 days
❌ Step 2: Enter "4" in days field
❌ Step 3: Enter "100" in quantity field
❌ Step 4: Remember what calculation you did
❌ Problem: Error-prone, no audit trail
```

### New Way (Children Mode)
```
✅ Step 1: Enter total children: 100
✅ Step 2: Enter daily capacity: 25
✅ Step 3: System shows: "4 days" automatically
✅ Step 4: Click save
✅ Benefit: Fast, accurate, auditable
```

---

## 🚀 Future Enhancements

Potential improvements:
1. **Historical capacity analysis** - Track actual daily production over time
2. **Smart suggestions** - Suggest daily capacity based on past performance
3. **Alerts** - Warn if daily target not met
4. **Capacity presets** - Save common capacity rates per machine/crew
5. **Efficiency scoring** - Compare planned vs. actual production

---

## ❓ FAQ

**Q: Can I change from Children mode to Days mode later?**
A: Yes! Just edit the task and toggle the mode. Your data is preserved.

**Q: What if I don't know daily capacity?**
A: Use "Know Days" mode instead, or estimate based on machine specs.

**Q: Does this work with existing tasks?**
A: Yes! Old tasks (simple days mode) continue to work. New tasks can use either mode.

**Q: What if daily capacity changes mid-project?**
A: Edit the task and update the daily capacity. Days will recalculate.

**Q: Can different tasks have different daily capacities?**
A: Absolutely! Each task tracks its own capacity.

---

**សរុបមក (Summary):** The Children mode makes scheduling **much easier** when you know quantities but not duration. Just enter the numbers, and let the system calculate for you! 🎉
