<div class="task-card {{ $task->priority === 'urgent' ? 'urgent' : '' }}" data-id="{{ $task->id }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="task-title">{{ $task->name }}</div>
        @if($task->priority === 'urgent')
            <div class="urgent-indicator" title="Urgent Task">
                <span class="pulse-ring"></span>
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            </div>
        @endif
    </div>
    
    <div class="task-meta">
        <span class="process-tag"><i class="bi bi-gear-fill text-secondary"></i> {{ $task->process }}</span>
        @if($task->assigned_machine_id)
            <span class="machine-tag"><i class="bi bi-cpu text-secondary"></i> {{ $task->machine->name ?? 'Unknown Machine' }}</span>
        @endif
    </div>
    
    <div class="d-flex justify-content-between align-items-end mt-3">
        <div class="task-badges">
            @if($task->duration_days)
                <span class="t-badge"><i class="bi bi-hourglass-split"></i> {{ $task->duration_days }}d</span>
            @endif
        </div>
        @if($task->assigned_to)
            <div class="assignee-avatar" title="{{ $task->assigned_to }}">
                {{ strtoupper(substr($task->assigned_to, 0, 1)) }}
            </div>
        @endif
    </div>
</div>
