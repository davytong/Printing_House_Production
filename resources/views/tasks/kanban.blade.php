@extends('layouts.app')
@section('title', 'Kanban Board - Shop Floor')
@section('page-title', 'Shop Floor Live View')

@section('content')
<style>
/* Custom Scrollbar for a premium feel */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.7);
}

/* Kanban Specific Styles */
.kanban-board {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    padding-bottom: 1.5rem;
    min-height: 75vh;
}
@media (max-width: 991px) {
    .kanban-board {
        scroll-snap-type: x mandatory;
        scroll-padding: 1rem;
        gap: 1rem;
    }
    .kanban-column {
        scroll-snap-align: center;
        min-width: 85vw !important;
    }
}
.kanban-column {
    flex: 1;
    min-width: 340px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    transition: transform 0.3s ease;
}
.kanban-header {
    padding: 1.25rem 1.5rem;
    font-weight: 800;
    font-size: 1.15rem;
    color: #0f172a;
    border-bottom: 1px solid rgba(226, 232, 240, 0.7);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
    position: relative;
    overflow: hidden;
}
.kanban-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 5px;
}
.kanban-header .badge {
    font-size: 0.85rem;
    padding: 0.5em 0.9em;
    border-radius: 99px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.kanban-items {
    padding: 1.25rem;
    flex-grow: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    min-height: 200px;
}
/* Gamification: Empty State */
.kanban-items:empty {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="%23e2e8f0" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>');
    background-repeat: no-repeat;
    background-position: center 30%;
    position: relative;
}
.kanban-items:empty::after {
    content: "All caught up! 🎉";
    position: absolute;
    top: 65%;
    left: 0;
    right: 0;
    text-align: center;
    color: #94a3b8;
    font-size: 1rem;
    font-weight: 600;
}
/* Sortable active styles */
.kanban-items.sortable-ghost {
    background: rgba(241, 245, 249, 0.6);
    border-radius: 16px;
    opacity: 0.5;
}

/* Task Cards */
.task-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.02);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-left: 5px solid #3b82f6; /* Default Blue */
    cursor: grab;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    position: relative;
    overflow: hidden;
    will-change: transform, box-shadow;
}
.task-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}
.task-card:active {
    cursor: grabbing;
    transform: scale(0.96);
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.task-card:hover {
    transform: translateY(-5px) scale(1.01);
    box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.15);
    border-color: #cbd5e1;
    z-index: 10;
}
.task-card.urgent {
    border-left-color: #ef4444;
    background: linear-gradient(to right, #ffffff, #fef2f2);
}
.task-card.urgent:hover {
    box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.2);
}
.task-title {
    font-weight: 800;
    font-size: 1.05rem;
    color: #1e293b;
    line-height: 1.3;
}
.task-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.75rem;
    font-weight: 600;
    font-size: 0.8rem;
}
.process-tag {
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.machine-tag {
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.task-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.t-badge {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.assignee-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.8rem;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
}

/* Urgent Pulse Animation */
.urgent-indicator {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
}
.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: rgba(239, 68, 68, 0.4);
    animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulse-ring {
    0% { transform: scale(0.8); opacity: 0.5; }
    100% { transform: scale(2.5); opacity: 0; }
}

/* Column Colors */
.col-pending .kanban-header { background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%); }
.col-pending .kanban-header::before { background: #94a3b8; }
.col-pending .kanban-header .badge { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

.col-inprogress .kanban-header { background: linear-gradient(135deg, rgba(239, 246, 255, 0.9) 0%, rgba(219, 234, 254, 0.9) 100%); }
.col-inprogress .kanban-header::before { background: #3b82f6; }
.col-inprogress .kanban-header .badge { background: #3b82f6; color: white; }

.col-paused .kanban-header { background: linear-gradient(135deg, rgba(254, 242, 242, 0.9) 0%, rgba(254, 226, 226, 0.9) 100%); }
.col-paused .kanban-header::before { background: #ef4444; }
.col-paused .kanban-header .badge { background: #ef4444; color: white; }

.col-completed .kanban-header { background: linear-gradient(135deg, rgba(240, 253, 244, 0.9) 0%, rgba(220, 252, 231, 0.9) 100%); }
.col-completed .kanban-header::before { background: #10b981; }
.col-completed .kanban-header .badge { background: #10b981; color: white; }

/* Dark Mode Column Colors */
[data-theme="dark"] .kanban-header { color: #f1f5f9; }
[data-theme="dark"] .col-pending .kanban-header { background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%); }
[data-theme="dark"] .col-pending .kanban-header .badge { background: #334155; color: #cbd5e1; border: 1px solid #475569; }

[data-theme="dark"] .col-inprogress .kanban-header { background: linear-gradient(135deg, rgba(30, 58, 138, 0.25) 0%, rgba(23, 37, 84, 0.25) 100%); }
[data-theme="dark"] .col-inprogress .kanban-header::before { background: #3b82f6; }
[data-theme="dark"] .col-inprogress .kanban-header .badge { background: rgba(37, 99, 235, 0.3); color: #93c5fd; }

[data-theme="dark"] .col-paused .kanban-header { background: linear-gradient(135deg, rgba(153, 27, 27, 0.25) 0%, rgba(127, 29, 29, 0.25) 100%); }
[data-theme="dark"] .col-paused .kanban-header::before { background: #ef4444; }
[data-theme="dark"] .col-paused .kanban-header .badge { background: rgba(220, 38, 38, 0.3); color: #fca5a5; }

[data-theme="dark"] .col-completed .kanban-header { background: linear-gradient(135deg, rgba(6, 78, 59, 0.25) 0%, rgba(2, 44, 34, 0.25) 100%); }
[data-theme="dark"] .col-completed .kanban-header::before { background: #10b981; }
[data-theme="dark"] .col-completed .kanban-header .badge { background: rgba(5, 150, 105, 0.3); color: #6ee7b7; }

/* Dark Mode Overrides for Kanban Cards */
[data-theme="dark"] .task-card { background: var(--surface); border-color: var(--border); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
[data-theme="dark"] .task-card::before { background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%); }
[data-theme="dark"] .task-card.urgent { background: linear-gradient(to right, var(--surface), rgba(239, 68, 68, 0.1)); }
[data-theme="dark"] .task-title { color: var(--text-primary); }
[data-theme="dark"] .process-tag, [data-theme="dark"] .machine-tag { color: var(--text-secondary); }
[data-theme="dark"] .t-badge { background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border); }
[data-theme="dark"] .task-card:hover { border-color: #4f46e5; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.3); }
[data-theme="dark"] .kanban-board { background: transparent; }
[data-theme="dark"] .page-title { color: var(--text-primary) !important; }
[data-theme="dark"] .page-title + div { color: var(--text-muted) !important; }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-weight: 800; letter-spacing: -0.5px;">
            <i class="bi bi-kanban-fill text-primary me-2"></i>Live Shop Floor
        </h1>
        <div style="font-size:0.95rem;color:#64748b;font-weight:500;">
            Drag and drop tasks across stages to instantly update their status.
        </div>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" action="{{ route('tasks.kanban') }}" class="d-flex gap-2" id="filterForm">
            <select name="process" class="form-select shadow-sm" style="border-radius:12px; font-size:.9rem; font-weight:600; padding:0.6rem 2.5rem 0.6rem 1rem;" onchange="document.getElementById('filterForm').submit()">
                <option value="">⚙️ All Processes</option>
                @foreach(['Design','Press','Digital','Folding','Gathering','Staple','Binding','Cutting','Packaging','Delivery','Other'] as $p)
                    <option value="{{ $p }}" @selected(request('process') === $p)>{{ $p }}</option>
                @endforeach
            </select>
            <a href="{{ route('tasks.index') }}" class="btn btn-light shadow-sm d-flex align-items-center" style="border-radius:12px; font-weight:700; transition: all 0.2s;">
                <i class="bi bi-list-ul me-2"></i> List View
            </a>
        </form>
    </div>
</div>

<div class="kanban-board">
    
    <!-- PENDING -->
    <div class="kanban-column col-pending">
        <div class="kanban-header">
            <span><i class="bi bi-hourglass-split text-warning me-2"></i>រង់ចាំ (Pending)</span>
            <span class="badge bg-secondary">{{ $columns['pending']->count() }}</span>
        </div>
        <div class="kanban-items" id="pending" data-status="pending">
            @foreach($columns['pending'] as $task)
                @include('tasks._kanban_card', ['task' => $task])
            @endforeach
        </div>
    </div>

    <!-- IN PROGRESS -->
    <div class="kanban-column col-inprogress">
        <div class="kanban-header">
            <span><i class="bi bi-play-circle-fill text-primary me-2"></i>កំពុងដំណើរការ (In Progress)</span>
            <span class="badge bg-primary">{{ $columns['in_progress']->count() }}</span>
        </div>
        <div class="kanban-items" id="in_progress" data-status="in_progress">
            @foreach($columns['in_progress'] as $task)
                @include('tasks._kanban_card', ['task' => $task])
            @endforeach
        </div>
    </div>

    <!-- PAUSED -->
    <div class="kanban-column col-paused">
        <div class="kanban-header">
            <span><i class="bi bi-pause-circle-fill text-danger me-2"></i>ផ្អាក (Paused)</span>
            <span class="badge bg-danger">{{ $columns['paused']->count() }}</span>
        </div>
        <div class="kanban-items" id="paused" data-status="paused">
            @foreach($columns['paused'] as $task)
                @include('tasks._kanban_card', ['task' => $task])
            @endforeach
        </div>
    </div>

    <!-- COMPLETED -->
    <div class="kanban-column col-completed">
        <div class="kanban-header">
            <span><i class="bi bi-check-circle-fill text-success me-2"></i>បានបញ្ចប់ (Completed)</span>
            <span class="badge bg-success">{{ $columns['completed']->count() }}</span>
        </div>
        <div class="kanban-items" id="completed" data-status="completed">
            @foreach($columns['completed'] as $task)
                @include('tasks._kanban_card', ['task' => $task])
            @endforeach
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const columns = ['pending', 'in_progress', 'paused', 'completed'];
        
        columns.forEach(col => {
            const el = document.getElementById(col);
            new Sortable(el, {
                group: 'kanban', // set both lists to same group
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item;  // dragged HTMLElement
                    const newStatus = evt.to.dataset.status; // target list
                    const oldStatus = evt.from.dataset.status; // previous list
                    const taskId = itemEl.dataset.id;

                    if (newStatus !== oldStatus) {
                        // Update badge counts (optimistic UI)
                        updateCounts();

                        // Send AJAX request to update status
                        updateTaskStatus(taskId, newStatus, itemEl, evt.from);
                    }
                },
            });
        });

        function updateCounts() {
            columns.forEach(col => {
                const el = document.getElementById(col);
                const count = el.children.length;
                el.parentElement.querySelector('.badge').innerText = count;
            });
        }

        function updateTaskStatus(taskId, newStatus, itemEl, oldListEl) {
            // Determine if we need to call the special complete endpoint or just normal update
            let url = \`/api/tasks/\${taskId}\`;
            let method = 'PUT';
            let body = { status: newStatus };

            if (newStatus === 'completed') {
                url = \`/api/tasks/\${taskId}/complete\`;
                method = 'POST';
                body = {};
            }

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(data => {
                if (!data.ok) {
                    throw new Error(data.message || 'Failed to update');
                }
                
                // Gamification: Trigger confetti on completion
                if (newStatus === 'completed' && typeof confetti === 'function') {
                    confetti({
                        particleCount: 150,
                        spread: 80,
                        origin: { y: 0.6 },
                        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'],
                        disableForReducedMotion: true
                    });
                }
                
                console.log('Status updated successfully');
            })
            .catch(err => {
                console.error('Update failed:', err);
                alert('Failed to update task status. Reverting...');
                // Revert DOM change
                oldListEl.appendChild(itemEl);
                updateCounts();
            });
        }
    });
</script>
@endpush
