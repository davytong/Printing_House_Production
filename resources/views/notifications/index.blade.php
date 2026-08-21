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
  <div class="d-flex align-items-center gap-2">
    @if($unread > 0)
      <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="margin:0;">
        @csrf
        <button class="btn btn-outline-primary btn-sm" style="border-radius: 9px;"><i class="bi bi-check-all"></i> Read All</button>
      </form>
    @endif
    
    @if($notifications->count() > 0)
      <form action="{{ route('notifications.clear-all') }}" method="POST" style="margin:0;" data-confirm="⚠️ Are you sure you want to delete ALL notifications permanently?">
        @csrf
        <button class="btn btn-outline-danger btn-sm" style="border-radius: 9px;"><i class="bi bi-trash3"></i> Clear All</button>
      </form>
    @endif
  </div>
</div>

<style>
  .notif-item {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    transition: all var(--ease);
    position: relative;
    overflow: hidden;
  }
  .notif-item:last-child { border-bottom: none; }
  
  .notif-item:hover {
    background: var(--surface-2);
  }
  
  .notif-icon-box {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
    box-shadow: inset 0 2px 4px rgba(255,255,255,0.4), 0 4px 8px rgba(0,0,0,0.05);
  }
  
  .notif-actions {
    display: flex; align-items: center; gap: .25rem;
    opacity: 0;
    transform: translateX(10px);
    transition: all var(--ease);
  }
  .notif-item:hover .notif-actions {
    opacity: 1;
    transform: translateX(0);
  }
  
  .notif-unread-dot {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--primary);
  }
  
  /* Dark mode overrides for hover */
  html[data-theme="dark"] .notif-item:hover {
    background: rgba(255,255,255,0.03);
  }
  html[data-theme="dark"] .notif-icon-box {
    box-shadow: inset 0 1px 1px rgba(255,255,255,0.1), 0 4px 8px rgba(0,0,0,0.2);
  }
</style>

<div class="panel p-0" style="overflow: hidden;">
  @forelse($notifications as $n)
    @php
      $nColors = ['danger'=>'#ef4444','warning'=>'#f59e0b','success'=>'#10b981','info'=>'#6366f1'];
      $nBgs    = ['danger'=>'rgba(239, 68, 68, 0.1)','warning'=>'rgba(245, 158, 11, 0.1)','success'=>'rgba(16, 185, 129, 0.1)','info'=>'rgba(99, 102, 241, 0.1)'];
      $nIcons  = ['danger'=>'bi-exclamation-octagon-fill','warning'=>'bi-exclamation-triangle-fill','success'=>'bi-check-circle-fill','info'=>'bi-info-circle-fill'];
      $color   = $nColors[$n->type] ?? '#6366f1';
      $bg      = $nBgs[$n->type]    ?? 'rgba(99, 102, 241, 0.1)';
      $icon    = $nIcons[$n->type]  ?? 'bi-info-circle-fill';
    @endphp
    <div class="notif-item" style="{{ !$n->is_read ? 'background: ' . str_replace('0.1)', '0.03)', $bg) : '' }}">
      @if(!$n->is_read)
        <div class="notif-unread-dot" style="background: {{ $color }}"></div>
      @endif
      
      <div class="notif-icon-box" style="background: {{ $bg }}; color: {{ $color }}">
        <i class="bi {{ $icon }}"></i>
      </div>
      
      <div style="flex:1; min-width:0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
          <div style="flex: 1;">
            <div style="font-weight:700; font-size:.95rem; color: var(--text-primary); margin-bottom:.2rem;">
              {{ $n->title }}
            </div>
            <div style="font-size:.85rem; color: var(--text-secondary); line-height: 1.4;">
              {{ $n->message }}
            </div>
            
            <div style="display:flex; align-items:center; gap:.75rem; margin-top:.6rem; flex-wrap:wrap;">
              <span style="font-family:var(--font-latin); font-size:.68rem; background:{{ $bg }}; color:{{ $color }}; padding:.2em .6em; border-radius:6px; font-weight:700; letter-spacing:.05em;">
                {{ strtoupper($n->module) }}
              </span>
              <span style="font-family:var(--font-latin); font-size:.75rem; color:var(--text-muted); display:flex; align-items:center; gap:.3rem;">
                <i class="bi bi-clock"></i> {{ $n->created_at->diffForHumans() }}
              </span>
              @if($n->action_url)
                <a href="{{ $n->action_url }}" style="font-size:.75rem; font-weight:600; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:.2rem;">
                  View Details <i class="bi bi-arrow-right-short"></i>
                </a>
              @endif
            </div>
          </div>
          
          <div class="notif-actions">
            @if(!$n->is_read)
              <form action="{{ route('notifications.read', $n) }}" method="POST" style="margin:0;">
                @csrf
                <button class="btn btn-ghost btn-icon" title="Mark as Read" style="color: var(--success);">
                  <i class="bi bi-check2-all" style="font-size: 1.1rem;"></i>
                </button>
              </form>
            @endif
            <form action="{{ route('notifications.destroy', $n) }}" method="POST" style="margin:0;">
              @csrf @method('DELETE')
              <button class="btn btn-ghost btn-icon" title="Delete" style="color: var(--danger);">
                <i class="bi bi-trash3" style="font-size: 1rem;"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="empty-state" style="padding: 4rem 1rem;">
      <div class="empty-icon" style="font-size:3rem; color:var(--text-muted); margin-bottom:1rem;">
        <i class="bi bi-bell-slash"></i>
      </div>
      <p style="font-weight:600; font-size: 1.1rem; color:var(--text-primary); margin:0;">គ្មានការជូនដំណឹងទេ / No Notifications</p>
      <p style="font-size:.85rem; color:var(--text-muted); margin-top:.5rem;">អ្នកបានអានរួចរាល់អស់ហើយ! / You're all caught up!</p>
    </div>
  @endforelse
  
  @if($notifications->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
      {{ $notifications->links() }}
    </div>
  @endif
</div>
@endsection
