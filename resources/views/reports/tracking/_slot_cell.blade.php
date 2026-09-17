@php
    $status = $slot['status'] ?? 'pending';
    $submittedAt = $slot['submitted_at'] ?? '—';
    $lateMinutes = $slot['late_minutes'] ?? 0;
    $sub = $slot['submission'] ?? null;
    $hasContent = $sub && !empty($sub->message_text);
@endphp

<div class="d-inline-block text-center">
    @if($status === 'submitted')
        <div class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 {{ $hasContent ? 'cursor-pointer shadow-sm' : '' }}"
             @if($hasContent) onclick="showReportModal({{ $sub->id }})" title="ចុចដើម្បីមើលរបាយការណ៍ (Click to view)" style="cursor: pointer;" @endif>
            <i class="bi bi-check-circle-fill me-1"></i>
            <span>{{ $submittedAt }}</span>
            <span class="d-block small text-success fw-normal">ទាន់ពេល (On Time)</span>
        </div>
    @elseif($status === 'late')
        <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 {{ $hasContent ? 'cursor-pointer shadow-sm' : '' }}"
             @if($hasContent) onclick="showReportModal({{ $sub->id }})" title="ចុចដើម្បីមើលរបាយការណ៍ (Click to view)" style="cursor: pointer;" @endif>
            <i class="bi bi-clock-history me-1"></i>
            <span>{{ $submittedAt }}</span>
            <span class="d-block small text-warning-emphasis fw-bold">យឺត +{{ $lateMinutes }}m</span>
        </div>
    @elseif($status === 'missed')
        <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
            <i class="bi bi-x-circle-fill me-1"></i>
            <span>Missed</span>
            <span class="d-block small text-danger fw-normal">មិនបានផ្ញើ</span>
        </div>
    @else
        <div class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
            <i class="bi bi-hourglass-split me-1"></i>
            <span>Pending</span>
            <span class="d-block small text-muted fw-normal">កំពុងរង់ចាំ</span>
        </div>
    @endif
</div>