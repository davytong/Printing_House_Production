@extends('layouts.app')
@section('title','ការជូនដំណឹង')
@section('page-title','Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ការជូនដំណឹង</h1>
    <p class="section-sub">
      @if($unread > 0)
        <span style="color:var(--danger);font-weight:600">{{ $unread }} ការជូនដំណឹងដែលមិនទាន់អាន</span>
      @else
        ការជូនដំណឹងទាំងអស់បានអាន
      @endif
    </p>
  </div>
  @if($unread > 0)
    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
      @csrf
      <button class="btn btn-outline-primary btn-sm"><i class="bi bi-check-all"></i> Mark All Read</button>
    </form>
  @endif
</div>

<div class="panel">
  @forelse($notifications as $n)
    @php
      $nColors = ['danger'=>'#ef4444','warning'=>'#f59e0b','success'=>'#10b981','info'=>'#6366f1'];
      $nBgs    = ['danger'=>'#fee2e2','warning'=>'#fffbeb','success'=>'#f0fdf4','info'=>'#eff6ff'];
      $nIcons  = ['danger'=>'bi-exclamation-octagon-fill','warning'=>'bi-exclamation-triangle-fill','success'=>'bi-check-circle-fill','info'=>'bi-info-circle-fill'];
      $color   = $nColors[$n->type] ?? '#6366f1';
      $bg      = $nBgs[$n->type]    ?? '#eff6ff';
      $icon    = $nIcons[$n->type]  ?? 'bi-info-circle-fill';
    @endphp
    <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid var(--border);
      {{ !$n->is_read ? 'background:'.$bg : '' }};transition:background var(--ease)">
      <div style="width:36px;height:36px;border-radius:50%;background:{{ $color }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="bi {{ $icon }}" style="color:{{ $color }}"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;flex-wrap:wrap">
          <div>
            <div style="font-weight:700;font-size:.88rem">{{ $n->title }}</div>
            <div style="font-size:.82rem;color:var(--text-secondary);margin-top:.15rem">{{ $n->message }}</div>
          </div>
          <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0">
            <span style="font-family:var(--font-latin);font-size:.72rem;color:var(--text-muted)">
              {{ $n->created_at->diffForHumans() }}
            </span>
            @if(!$n->is_read)
              <form action="{{ route('notifications.read',$n) }}" method="POST">
                @csrf
                <button class="btn btn-ghost btn-sm" title="Mark read" style="font-size:.75rem">
                  <i class="bi bi-check2"></i>
                </button>
              </form>
            @endif
            <form action="{{ route('notifications.destroy',$n) }}" method="POST">
              @csrf @method('DELETE')
              <button class="btn btn-ghost btn-sm" style="color:var(--text-muted);font-size:.75rem"><i class="bi bi-x-lg"></i></button>
            </form>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.35rem;flex-wrap:wrap">
          <span style="font-family:var(--font-latin);font-size:.68rem;background:{{ $color }}18;color:{{ $color }};padding:.1em .55em;border-radius:999px;font-weight:600">
            {{ strtoupper($n->module) }}
          </span>
          @if(!$n->is_read)
            <span style="width:6px;height:6px;border-radius:50%;background:var(--primary);display:inline-block"></span>
          @endif
          @if($n->action_url)
            <a href="{{ $n->action_url }}" style="font-size:.75rem;color:var(--primary)">
              View <i class="bi bi-arrow-right"></i>
            </a>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="empty-state">
      <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
      <p style="font-weight:600;margin:0">គ្មានការជូនដំណឹង</p>
    </div>
  @endforelse
  @if($notifications->hasPages())
    <div class="panel-body">{{ $notifications->links() }}</div>
  @endif
</div>
@endsection
