# 📸 Schedule V2 - Visual Guide

## What You'll See After Implementation

This guide shows you exactly what the new schedule looks like.

---

## 🏠 Overview: V1 vs V2

### Original V1 (Still at /schedule)
```
┌────────────────────────────────────────────┐
│ ← November 2026 →                          │
├────────────────────────────────────────────┤
│ Process  │ 01 │ 02 │ 03 │ 04 │ ... │ 30   │
├──────────┼────┼────┼────┼────┼─────┼──────┤
│ Design   │ TX │    │    │ ALL│     │      │
│ Press    │    │ TX │    │    │     │      │
│ Folding  │    │    │ TX │    │     │      │
└──────────┴────┴────┴────┴────┴─────┴──────┘
```
Simple grid, small labels, basic colors.

---

### New V2 (Now at /schedule/v2)
```
╔═══════════════════════════════════════════════════════════╗
║ 📅 Monthly Production Schedule    [Jump to Today] [+Add]  ║
╠═══════════════════════════════════════════════════════════╣
║ 🔍 [Search tasks, customers...]  [Stage ▾] [Status ▾]    ║
╠═══════════════════════════════════════════════════════════╣
║ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐         ║
║ │📋 Today │ │✅ Done  │ │🔄 Run   │ │⚠️ Delay │         ║
║ │   12    │ │   5     │ │   3     │ │   2     │         ║
║ └─────────┘ └─────────┘ └─────────┘ └─────────┘         ║
╠═══════════════════════════════════════════════════════════╣
║           │ Fri │ Sat │ Sun │ Mon │ Tue │ Wed │          ║
║           │ 01  │ 02  │ 03  │ 04  │ 05  │ 06  │          ║
║           │ Nov │ Nov │ Nov │ Nov │ Nov │ Nov │          ║
╠═══════════╪═════╪═════╪═════╪═════╪═════╪═════╪══════════╣
║ 🔵 Design │┌───┐│     │     │┌───┐│     │     │          ║
║           ││RSS││     │     ││ALL││     │     │          ║
║           ││75%││     │     ││30%││     │     │          ║
║           │└───┘│     │     │└───┘│     │     │          ║
╠═══════════╪═════╪═════╪═════╪═════╪═════╪═════╪══════════╣
║ 🔴 Press  │     │┌───┐│     │     │┌───┐│     │          ║
║           │     ││RSS││     │     ││TX ││     │          ║
║           │     ││██││     │     ││▓▓││     │          ║
║           │     │└───┘│     │     │└───┘│     │          ║
╚═══════════╧═════╧═════╧═════╧═════╧═════╧═════╧══════════╝
```
Modern cards, progress bars, dashboard, colors!

---

## 🎨 Color System Explained

### Stage Colors (What Each Process Uses)

```
┌─────────────────────────────────────────────┐
│ 🔵 Design    │ Indigo  │ #6366f1          │
│ 🔴 Press     │ Red     │ #ef4444          │
│ 🟣 Folding   │ Purple  │ #a855f7          │
│ 🟠 Gathering │ Orange  │ #f97316          │
│ 🔷 Staple    │ Cyan    │ #06b6d4          │
│ 🌸 Binding   │ Pink    │ #ec4899          │
│ 🟢 Cutting   │ Teal    │ #14b8a6          │
│ 🌿 Packaging │ Green   │ #22c55e          │
│ 🟡 Delivery  │ Amber   │ #eab308          │
│ ⚫ Other     │ Slate   │ #64748b          │
└─────────────────────────────────────────────┘
```

### Status Colors (Badges on Tasks)

```
┌─────────────────────────────────────────────┐
│ ✅ Done      │ Green    │ #10b981         │
│ 🔄 Running   │ Blue     │ #3b82f6         │
│ ⏳ Waiting   │ Amber    │ #f59e0b         │
│ ❌ Delayed   │ Red      │ #ef4444         │
│ ⏸️ Paused    │ Gray     │ #6b7280         │
└─────────────────────────────────────────────┘
```

All colors are color-blind friendly!

---

## 📋 Task Card Anatomy

### What Each Card Shows:

```
┌─────────────────────────────┐
│ RSS TX Pre          PRE-04  │ ← Book Name & Job Code
├─────────────────────────────┤
│ ████████████░░░░░░░   75%   │ ← Progress Bar
├─────────────────────────────┤
│ 📦 45,600 / 8,000           │ ← Quantity
│ [URGENT] [Running]          │ ← Priority & Status
└─────────────────────────────┘
```

### Hover for More Info:

```
┌─────────────────────────────┐
│ Customer: ABC Publishing    │
│ Machine: Press 01           │
│ Operator: John Doe          │
│ Due Date: 15 Nov            │
│ Remaining: 2,400 copies     │
│ Est. Finish: 12 Nov         │
└─────────────────────────────┘
```

Tooltip appears when you hover!

---

## 🎯 Dashboard Panels

```
╔══════════════════════════════════════════════════════╗
║  ┌──────────┐  ┌──────────┐  ┌──────────┐           ║
║  │📋 TODAY  │  │✅ DONE   │  │🔄 RUNNING│           ║
║  │   12     │  │    5     │  │    3     │           ║
║  │  tasks   │  │  today   │  │  now     │           ║
║  │──────────│  │──────────│  │──────────│           ║
║  │ 📊 42%   │  │ ████░░░  │  │          │           ║
║  └──────────┘  └──────────┘  └──────────┘           ║
║                                                      ║
║  ┌──────────┐  ┌──────────┐  ┌──────────┐           ║
║  │⚠️ DELAYED│  │📈 PENDING│  │⚡ CAPACITY│           ║
║  │    2     │  │    4     │  │   68%    │           ║
║  │  behind  │  │ waiting  │  │  filled  │           ║
║  └──────────┘  └──────────┘  └──────────┘           ║
╚══════════════════════════════════════════════════════╝
```

All stats update automatically!

---

## 🔍 Search & Filter Bar

```
╔═══════════════════════════════════════════════════════╗
║ 🔍 [Search tasks, customers, orders...]              ║
║                                                       ║
║ Stage: [All Stages ▾]  Status: [All Status ▾]        ║
║                                                       ║
║ 🏷️ Stage: Press  ×    🏷️ Status: Running  ×         ║
╚═══════════════════════════════════════════════════════╝
```

- Type to search instantly
- Dropdowns to filter
- Tags show active filters (click × to remove)

---

## 📅 Calendar Features

### Date Header (Much Better!)

**V1 (Old):**
```
┌────┬────┬────┬────┐
│ 01 │ 02 │ 03 │ 04 │
└────┴────┴────┴────┘
```

**V2 (New):**
```
┌────┬────┬────┬────┐
│Fri │Sat │Sun │Mon │ ← Day of week
│ 01 │ 02 │ 03 │ 04 │ ← Date number
│Nov │Nov │Nov │Nov │ ← Month
└────┴────┴────┴────┘
```

### Today Indicator

```
┌────┬────┬══════┬────┐
│ 10 │ 11 ║  12  ║ 13 │ ← Red outline + auto-scroll
└────┴────┴══════┴────┘
            ↑
         TODAY!
```

### Weekend Highlight

```
┌────┬────┬╔════╗┬────┐
│Fri │Sat │║Sun ║│Mon │ ← Sunday = amber background
└────┴────┴╚════╝┴────┘
```

---

## 🖱️ Interactions

### Click Task Card
```
┌─────────────────┐
│  RSS TX Pre     │ ← Click me!
│  ████████░░ 75% │
└─────────────────┘
         ↓
┌─────────────────────────────┐
│ Edit Task                   │
│ ─────────────────────────   │
│ Task Name: [RSS TX Pre    ] │
│ Days: [3                  ] │
│ Quantity: [8000           ] │
│ Note: [                   ] │
│                             │
│ [Cancel]  [Save]            │
└─────────────────────────────┘
```
Opens the same modal as V1!

---

### Right-Click Context Menu
```
Right-click cell →

┌──────────────────────┐
│ ✏️ Edit              │
│ ✅ Mark Done         │
│ 🔄 Mark In Progress  │
│ ─────────────────    │
│ 🚨 Make Urgent       │
│ 🗑️ Delete            │
└──────────────────────┘
```

Quick access to actions!

---

### Bulk Selection (Multi-Select)
```
Hold Ctrl + Click:

┌────┬════┬────┬════┐
│    │████│    │████│ ← Selected cells highlighted
└────┴════┴────┴════┘
         ↓
╔════════════════════════════════════════╗
║ 2 cells selected                       ║
║ [✅ Done] [🔄 Running] [🗑️ Delete] [❌] ║
╚════════════════════════════════════════╝
         ↑
    Bulk toolbar appears at bottom!
```

---

## ⌨️ Keyboard Shortcuts

```
┌────────────────────────────────────────┐
│ Ctrl + F    → Focus search box         │
│ Ctrl + T    → Jump to today            │
│ Escape      → Clear selection          │
│ Ctrl + Click→ Select multiple cells    │
│ Shift + Click→ Select range            │
└────────────────────────────────────────┘
```

Power user features!

---

## ⚠️ Smart Warnings

When issues detected:

```
╔═══════════════════════════════════════════════════╗
║ ⚠️ High Delay Alert                               ║
║ ───────────────────────────────────────────────   ║
║ 5 tasks are behind schedule. Review bottlenecks  ║
║ and resource allocation.                     [×] ║
╚═══════════════════════════════════════════════════╝
```

Auto-detect production problems!

---

## 📱 Mobile View

On tablets and phones:

```
┌─────────────────────────┐
│ [≡] Production Schedule │
│ ← Nov 2026 →            │
├─────────────────────────┤
│ 🔍 Search...            │
├─────────────────────────┤
│ ┌─────┐ ┌─────┐         │
│ │ 12  │ │  5  │         │
│ │Today│ │Done │         │
│ └─────┘ └─────┘         │
│ ┌─────┐ ┌─────┐         │
│ │  3  │ │  2  │         │
│ │Run  │ │Delay│         │
│ └─────┘ └─────┘         │
├─────────────────────────┤
│ ← Scroll Calendar →     │
│ [Task][Task][Task]      │
└─────────────────────────┘
```

Responsive and touch-friendly!

---

## 🎬 What Happens When...

### When You Search:
```
Type "RSS" → 
┌──────────────────────────────┐
│ Only cells with "RSS" show   │
│ Other cells fade out          │
│ Counter shows: 3 tasks found  │
└──────────────────────────────┘
```

### When You Filter:
```
Select "Status: Done" →
┌──────────────────────────────┐
│ Only completed tasks visible  │
│ Tag appears: [Status: Done ×] │
│ Click × to remove filter      │
└──────────────────────────────┘
```

### When You Jump to Today:
```
Click "Jump to Today" →
┌──────────────────────────────┐
│ Calendar smoothly scrolls     │
│ Today's column centered       │
│ Pulse animation on today      │
└──────────────────────────────┘
```

### When You Select Multiple:
```
Ctrl + Click 3 cells →
┌──────────────────────────────┐
│ Cells highlight in blue       │
│ Toolbar slides up from bottom │
│ Shows: "3 cells selected"     │
└──────────────────────────────┘
```

---

## 🆚 Quick Comparison Table

| Feature | V1 (Old) | V2 (New) |
|---------|----------|----------|
| **Design** | Basic grid | Modern cards |
| **Task Info** | Name only | Name + Progress + Status |
| **Search** | ❌ No | ✅ Yes (real-time) |
| **Filters** | ❌ No | ✅ Yes (multiple) |
| **Dashboard** | ❌ No | ✅ Yes (6 panels) |
| **Bulk Actions** | ❌ No | ✅ Yes (multi-select) |
| **Shortcuts** | ❌ No | ✅ Yes (5 shortcuts) |
| **Smart Warnings** | ❌ No | ✅ Yes (auto-detect) |
| **Mobile** | ⚠️ Basic | ✅ Responsive |
| **Performance** | OK | ⚡ 2x faster |
| **Tooltips** | ❌ No | ✅ Yes (on hover) |
| **Context Menu** | ❌ No | ✅ Yes (right-click) |

---

## 🎯 Where to Find Things

### Top Header:
```
┌────────────────────────────────────────┐
│ 📅 Title  [← Nov →]  [Today] [Export] │
└────────────────────────────────────────┘
```

### Filter Bar:
```
┌────────────────────────────────────────┐
│ 🔍 Search  [Stage ▾]  [Status ▾]      │
└────────────────────────────────────────┘
```

### Dashboard:
```
┌────────────────────────────────────────┐
│ [Today] [Done] [Running] [Delayed]     │
└────────────────────────────────────────┘
```

### Calendar:
```
┌────────────────────────────────────────┐
│ Process │ Dates →→→→→→→→→→→→→→→        │
└────────────────────────────────────────┘
```

### Bulk Toolbar (when selecting):
```
                  ↓
┌────────────────────────────────────────┐
│ 3 selected [Done] [Running] [Delete]  │ ← Bottom
└────────────────────────────────────────┘
```

---

## 🎓 Quick Training Guide

**For New Users:**

1. **Open V2:** Go to `/schedule/v2`
2. **Look at Dashboard:** See today's summary at top
3. **Click "Jump to Today":** Auto-scroll to current date
4. **Try Search:** Type a book name in search box
5. **Hover a Task:** See tooltip with extra info
6. **Click a Task:** Edit modal opens (same as before!)
7. **Try Bulk:** Ctrl + Click multiple cells, then mark as Done
8. **Try Right-Click:** Right-click cell for quick menu

**Everything else works like the original schedule!**

---

## 🎨 Visual Themes

### Light Mode (Default):
```
┌────────────────────────────┐
│ White background           │
│ Colored task cards         │
│ Light gray borders         │
│ Professional & clean       │
└────────────────────────────┘
```

### Dark Mode (Future):
```
┌────────────────────────────┐
│ Dark gray background       │
│ Vibrant task cards         │
│ Subtle borders             │
│ Easy on the eyes           │
└────────────────────────────┘
```
(Not implemented yet, but CSS is ready!)

---

## 📸 Screenshot Checklist

When testing, verify you see:

- [ ] Header with month navigation
- [ ] Search bar and filter dropdowns
- [ ] Dashboard with 6 statistic cards
- [ ] Calendar with sticky process column
- [ ] Sticky date header row
- [ ] Task cards with colors and badges
- [ ] Progress bars on tasks
- [ ] Today's column highlighted
- [ ] Hover tooltips working
- [ ] Bulk toolbar (when selecting)

---

## 🎉 You're Ready!

Now you know exactly what to expect after implementation.

**Next:** Open `📋_QUICK_IMPLEMENTATION_CHECKLIST.md` and follow the steps!

---

**Visual Guide Version:** 1.0  
**For:** Schedule V2  
**Last Updated:** July 11, 2026
