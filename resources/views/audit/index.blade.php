@extends('layouts.app')
@section('title', 'Enterprise Audit Trail — កំណត់ត្រាសកម្មភាព')
@section('page-title', 'Enterprise Audit Trail')

@section('content')
<style>
/* Audit Explorer Styling */
.audit-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.audit-kpi {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: transform .2s ease, box-shadow .2s ease;
}
.audit-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
.audit-kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.audit-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
}
.audit-filter-bar {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: rgba(var(--primary-rgb, 79,70,229), 0.02);
}
.module-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.module-tab {
    padding: 0.35rem 0.85rem;
    border-radius: 99px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    color: var(--text-secondary);
    border: 1px solid var(--border);
    background: var(--surface);
    transition: all .15s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.module-tab:hover {
    background: var(--surface-2, #f8fafc);
    color: var(--text-primary);
}
.module-tab.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 4px 12px var(--primary-glow);
}
.table-audit tbody tr {
    transition: background-color .15s ease;
}
.table-audit tbody tr:hover {
    background-color: rgba(var(--primary-rgb, 79,70,229), 0.03);
}
.log-badge {
    padding: 0.25rem 0.65rem;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.action-pill {
    font-weight: 600;
    font-size: 0.84rem;
    color: var(--text-primary);
}
.action-pill.failed {
    color: var(--danger);
}
</style>

<!-- KPI Summary Header -->
<div class="audit-kpi-grid">
    <div class="audit-kpi">
        <div class="audit-kpi-icon" style="background: rgba(79,70,229,0.12); color: #4f46e5;">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin); color: var(--text-primary); line-height: 1.1;">
                {{ number_format($stats['total_today']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Today's Activities / សកម្មភាពថ្ងៃនេះ</div>
        </div>
    </div>

    <div class="audit-kpi">
        <div class="audit-kpi-icon" style="background: rgba(14,165,233,0.12); color: #0ea5e9;">
            <i class="bi bi-printer-fill"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin); color: var(--text-primary); line-height: 1.1;">
                {{ number_format($stats['production_today']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Production Events / ការបោះពុម្ព</div>
        </div>
    </div>

    <div class="audit-kpi">
        <div class="audit-kpi-icon" style="background: rgba(16,185,129,0.12); color: #10b981;">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin); color: var(--text-primary); line-height: 1.1;">
                {{ number_format($stats['inventory_today']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Stock Movements / ចលនាស្តុក</div>
        </div>
    </div>

    <div class="audit-kpi">
        <div class="audit-kpi-icon" style="background: {{ $stats['security_alerts'] > 0 ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }}; color: {{ $stats['security_alerts'] > 0 ? '#ef4444' : '#f59e0b' }};">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        <div>
            <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-latin); color: {{ $stats['security_alerts'] > 0 ? '#ef4444' : 'var(--text-primary)' }}; line-height: 1.1;">
                {{ number_format($stats['security_alerts']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Security Flags / បម្រាមសុវត្ថិភាព</div>
        </div>
    </div>
</div>

<!-- Main Audit Card -->
<div class="audit-card">
    <!-- Filter & Search Toolbar -->
    <div class="audit-filter-bar">
        <div class="module-tabs">
            @php
                $modules = [
                    'all'         => ['label' => 'All Modules', 'icon' => 'bi-grid-fill'],
                    'production'  => ['label' => 'Production', 'icon' => 'bi-printer-fill'],
                    'inventory'   => ['label' => 'Inventory', 'icon' => 'bi-box-seam-fill'],
                    'procurement' => ['label' => 'Procurement', 'icon' => 'bi-cart-check-fill'],
                    'auth'        => ['label' => 'Security/Auth', 'icon' => 'bi-shield-lock-fill'],
                    'telegram'    => ['label' => 'Telegram', 'icon' => 'bi-telegram'],
                    'settings'    => ['label' => 'Settings', 'icon' => 'bi-gear-fill'],
                ];
            @endphp
            @foreach($modules as $mKey => $mCfg)
                <a href="{{ request()->fullUrlWithQuery(['module' => $mKey, 'page' => 1]) }}"
                   class="module-tab {{ $module === $mKey ? 'active' : '' }}">
                    <i class="bi {{ $mCfg['icon'] }}"></i>
                    <span>{{ $mCfg['label'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Action tools (Export & Search) -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('audit.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm" title="Download CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Secondary Filter Bar -->
    <form method="GET" action="{{ route('audit.index') }}" class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center bg-light">
        <input type="hidden" name="module" value="{{ $module }}">
        
        <div class="flex-grow-1" style="min-width: 220px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search action, details, user, IP...">
            </div>
        </div>

        <div style="min-width: 150px;">
            <select name="user" class="form-select form-select-sm">
                <option value="">All Users / គ្រប់បុគ្គលិក</option>
                @foreach($users as $u)
                    <option value="{{ $u }}" {{ $user === $u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 130px;">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" title="From Date">
        </div>

        <div style="min-width: 130px;">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" title="To Date">
        </div>

        <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-funnel"></i> Filter
        </button>

        @if($search || $user || $dateFrom || $dateTo || $module !== 'all')
            <a href="{{ route('audit.index') }}" class="btn btn-ghost btn-sm text-muted">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        @endif
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover table-audit align-middle mb-0">
            <thead class="table-light" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                <tr>
                    <th style="padding-left: 1.5rem; width: 180px;">Timestamp / កាលបរិច្ឆេទ</th>
                    <th style="width: 160px;">User / អ្នកប្រើប្រាស់</th>
                    <th style="width: 130px;">Module / ផ្នែក</th>
                    <th>Action & Details / សកម្មភាព និងព័ត៌មាន</th>
                    <th style="width: 130px;">IP Address</th>
                    <th style="width: 70px; text-align: right; padding-right: 1.5rem;"></th>
                </tr>
            </thead>
            <tbody style="font-size: 0.88rem;">
                @forelse($logs as $log)
                @php
                    $isSecurityAlert = str_contains(strtolower($log->action), 'failed') || str_contains(strtolower($log->action), 'unauthorized');
                    $badge = $log->module_badge;
                @endphp
                <tr>
                    <td style="padding-left: 1.5rem; font-family: var(--font-latin); font-size: 0.8rem; color: var(--text-secondary);">
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $log->created_at->diffForHumans() }}</div>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size: 0.75rem; flex-shrink: 0;">
                                {{ strtoupper(substr($log->user_name, 0, 1)) }}
                            </div>
                            <div style="overflow: hidden;">
                                <div style="font-weight: 700; color: var(--text-primary); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">{{ $log->user_name }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $log->position)) }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="log-badge" style="background: {{ $badge['bg'] }}18; color: {{ $badge['bg'] }}; border: 1px solid {{ $badge['bg'] }}33;">
                            <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                        </span>
                    </td>

                    <td>
                        <div class="action-pill {{ $isSecurityAlert ? 'failed' : '' }}">
                            @if($isSecurityAlert)
                                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                            @endif
                            {{ $log->action }}
                        </div>
                        @if($log->details)
                            <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 500px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $log->details }}
                            </div>
                        @endif
                    </td>

                    <td style="font-family: var(--font-latin); font-size: 0.8rem; color: var(--text-muted);">
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-geo-alt"></i> {{ $log->ip_address ?: '127.0.0.1' }}
                        </span>
                    </td>

                    <td style="padding-right: 1.5rem; text-align: right;">
                        <button type="button" class="btn btn-sm btn-ghost" 
                                onclick="viewLogModal({{ json_encode([
                                    'id' => $log->id,
                                    'time' => $log->created_at->format('Y-m-d H:i:s') . ' (' . $log->created_at->diffForHumans() . ')',
                                    'user' => $log->user_name,
                                    'position' => ucfirst(str_replace('_', ' ', $log->position)),
                                    'module' => $badge['label'],
                                    'action' => $log->action,
                                    'details' => $log->details ?: 'None',
                                    'ip' => $log->ip_address ?: '127.0.0.1'
                                ]) }})"
                                title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 4rem 1rem; text-align: center; color: var(--text-muted);">
                        <i class="bi bi-journal-x" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem;"></i>
                        <h6 style="font-weight: 700;">គ្មានកំណត់ត្រាសកម្មភាពទេ / No Audit Logs Found</h6>
                        <p style="font-size: 0.85rem; margin-bottom: 0;">Try adjusting your search terms or filter range.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
        </small>
        <div>
            {{ $logs->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="bi bi-shield-check text-primary me-1"></i> Audit Event Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">Date & Time</label>
                        <div class="fw-bold small" id="mTime">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">Module</label>
                        <div><span class="badge bg-primary" id="mModule">—</span></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">User Name</label>
                        <div class="fw-bold" id="mUser">—</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">Position</label>
                        <div class="small text-muted" id="mPos">—</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Action</label>
                        <div class="p-2 bg-light rounded small fw-bold" id="mAction">—</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Event Payload / Details</label>
                        <div class="p-3 bg-light rounded small text-break font-monospace" style="max-height: 180px; overflow-y: auto;" id="mDetails">—</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Network IP</label>
                        <div class="small text-muted font-monospace" id="mIp">—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewLogModal(log) {
    document.getElementById('mTime').textContent = log.time;
    document.getElementById('mModule').textContent = log.module;
    document.getElementById('mUser').textContent = log.user;
    document.getElementById('mPos').textContent = log.position;
    document.getElementById('mAction').textContent = log.action;
    document.getElementById('mDetails').textContent = log.details;
    document.getElementById('mIp').textContent = log.ip;

    const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
    modal.show();
}
</script>
@endpush

@endsection
