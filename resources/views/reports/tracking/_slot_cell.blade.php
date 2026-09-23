@php
    $status = $slot['status'] ?? 'pending';
    $submittedAt = $slot['submitted_at'] ?? '—';
    $lateMinutes = $slot['late_minutes'] ?? 0;
    $sub = $slot['submission'] ?? null;
    $hasContent = $sub && !empty($sub->message_text);
    $isKm = app()->getLocale() === 'km';
@endphp

<div class="slot-pill-wrapper d-inline-flex justify-content-center">
    @if($status === 'submitted')
        <div class="slot-pill slot-on-time {{ $hasContent ? 'is-clickable' : '' }}"
             @if($hasContent) onclick="showReportModal({{ $sub->id }})" role="button" tabindex="0" title="{{ $isKm ? 'ចុចដើម្បីមើលរបាយការណ៍' : 'Click to view report' }}" @endif>
            <div class="slot-pill-top">
                <i class="bi bi-check-circle-fill text-success slot-icon"></i>
                <span class="slot-time">{{ $submittedAt }}</span>
            </div>
            <div class="slot-pill-badge badge-on-time">
                {{ __('reports.on_time') }}
                @if($hasContent)
                    <i class="bi bi-file-text-fill ms-1 opacity-75" style="font-size: 0.75rem;"></i>
                @endif
            </div>
        </div>
    @elseif($status === 'late')
        <div class="slot-pill slot-late {{ $hasContent ? 'is-clickable' : '' }}"
             @if($hasContent) onclick="showReportModal({{ $sub->id }})" role="button" tabindex="0" title="{{ $isKm ? 'ចុចដើម្បីមើលរបាយការណ៍' : 'Click to view report' }}" @endif>
            <div class="slot-pill-top">
                <i class="bi bi-clock-history text-warning-emphasis slot-icon"></i>
                <span class="slot-time">{{ $submittedAt }}</span>
            </div>
            <div class="slot-pill-badge badge-late">
                {{ $isKm ? "+{$lateMinutes} នាទី" : "+{$lateMinutes}m" }}
                @if($hasContent)
                    <i class="bi bi-file-text-fill ms-1 opacity-75" style="font-size: 0.75rem;"></i>
                @endif
            </div>
        </div>
    @elseif($status === 'missed')
        <div class="slot-pill slot-missed">
            <div class="slot-pill-top">
                <i class="bi bi-x-circle-fill text-danger slot-icon"></i>
                <span class="slot-label">{{ __('reports.missed') }}</span>
            </div>
            <div class="slot-pill-badge badge-missed">
                {{ $isKm ? 'ខកខាន' : 'Missed' }}
            </div>
        </div>
    @else
        <div class="slot-pill slot-pending">
            <div class="slot-pill-top">
                <span class="slot-pulse-dot"></span>
                <span class="slot-label">{{ __('reports.pending') }}</span>
            </div>
            <div class="slot-pill-badge badge-pending">
                <i class="bi bi-hourglass-split me-1" style="font-size: 0.75rem;"></i>
                {{ $isKm ? 'រង់ចាំ' : 'Awaiting' }}
            </div>
        </div>
    @endif
</div>