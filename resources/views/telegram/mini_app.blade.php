@extends('layouts.app')
@section('title', 'Stock Out Consumable — PrintTracker Pro')
@section('page-title', 'Stock Out Consumable')

@section('content')
<!-- Include Telegram WebApp JS SDK, FontAwesome 6.5 & Inter/Kantumruy Fonts -->
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  /* ── Modern Ergonomic Warehouse Design System ── */
  :root {
    --primary-blue: #2563eb;
    --primary-blue-hover: #1d4ed8;
    --primary-subtle: #eff6ff;
    --border-light: #f1f5f9;
    --border-card: #e2e8f0;
    --text-main: #0f172a;
    --text-muted: #64748b;
  }

  .stock-outline-wrapper {
    max-width: 1080px;
    margin: 0 auto;
    padding-bottom: 140px !important;
    font-family: 'Inter', 'Kantumruy Pro', var(--font-kh), sans-serif;
    color: var(--text-main);
  }

  /* Professional Header Card */
  .outline-header-card {
    background: #ffffff;
    border: 1px solid var(--border-card);
    border-radius: 16px;
    padding: 0.85rem 1.15rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    margin-bottom: 1rem;
  }

  /* Mode Switcher Outline Pills */
  .outline-mode-bar {
    display: flex;
    gap: 0.25rem;
    background: #f8fafc;
    padding: 0.25rem;
    border-radius: 12px;
    border: 1px solid var(--border-card);
  }

  .outline-mode-bar .btn-mode-outline {
    padding: 0.4rem 0.85rem;
    border-radius: 9px;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s ease;
    cursor: pointer;
  }

  .outline-mode-bar .btn-mode-outline:hover {
    color: var(--primary-blue);
  }

  .outline-mode-bar .btn-mode-outline.active {
    background: #ffffff;
    border-color: #cbd5e1;
    color: var(--primary-blue);
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
  }

  /* Category Filter Chips Outline */
  .chips-outline-scroll {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.4rem;
    margin-bottom: 0.85rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }
  .chips-outline-scroll::-webkit-scrollbar { display: none; }

  .outline-chip {
    background: #ffffff;
    border: 1.5px solid var(--border-card);
    color: #475569;
    padding: 0.4rem 0.95rem;
    border-radius: 24px;
    font-weight: 600;
    font-size: 0.82rem;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 0.4rem;
    user-select: none;
  }

  .outline-chip:hover {
    border-color: var(--primary-blue);
    color: var(--primary-blue);
    background: #f8fafc;
  }

  .outline-chip.active {
    background: var(--primary-subtle);
    border-color: var(--primary-blue);
    color: var(--primary-blue);
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
  }

  .chip-count-badge {
    background: #e2e8f0;
    color: #475569;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 12px;
    margin-left: 0.2rem;
    transition: all 0.15s ease;
  }

  .outline-chip.active .chip-count-badge {
    background: var(--primary-blue);
    color: #ffffff;
  }

  /* Search Bar & Controls */
  .search-wrap {
    position: relative;
    width: 100%;
    max-width: 320px;
  }

  .search-input-field {
    border: 1.5px solid var(--border-card);
    border-radius: 24px;
    padding: 0.45rem 2.2rem 0.45rem 2.2rem;
    font-size: 0.85rem;
    width: 100%;
    outline: none;
    transition: all 0.15s ease;
    background: #ffffff;
  }

  .search-input-field:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  }

  .search-icon-left {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
    font-size: 0.85rem;
  }

  .search-clear-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 0;
    display: none;
    font-size: 0.95rem;
  }
  .search-clear-btn:hover { color: #475569; }

  /* Outline Material Cards Grid */
  .materials-outline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(235px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.5rem;
  }

  @media (max-width: 576px) {
    .materials-outline-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 0.6rem;
    }
  }

  .outline-item-card {
    position: relative;
    background: #ffffff;
    border: 1.5px solid var(--border-card);
    border-radius: 16px;
    padding: 0.85rem 0.95rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    user-select: none;
  }

  .outline-item-card:hover {
    border-color: var(--primary-blue);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.12);
    transform: translateY(-2px);
  }

  .outline-item-card.selected {
    border-color: var(--primary-blue);
    background: #f8faff;
    box-shadow: 0 0 0 2px var(--primary-blue), 0 4px 14px rgba(37, 99, 235, 0.15);
  }

  /* Out of Stock Card Treatment */
  .outline-item-card.out-of-stock-card {
    opacity: 0.55;
    background: #f8fafc;
    border-style: dashed;
    cursor: not-allowed;
  }
  .outline-item-card.out-of-stock-card:hover {
    transform: none;
    box-shadow: none;
    border-color: var(--border-card);
  }

  /* Cart Selected Top Badge */
  .cart-badge {
    position: absolute;
    top: -8px;
    right: -6px;
    background: var(--primary-blue);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 999px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
    z-index: 3;
    display: none;
    animation: popBadge 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  @keyframes popBadge {
    0% { transform: scale(0.6); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }

  /* On-Card Interactive Stepper */
  .card-stepper-wrap {
    display: none;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1.5px solid var(--primary-blue);
    border-radius: 10px;
    padding: 0.2rem 0.35rem;
    margin-top: 0.65rem;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
  }

  .outline-item-card.selected .card-stepper-wrap {
    display: flex;
  }

  .card-step-btn {
    width: 32px;
    height: 30px;
    border-radius: 7px;
    border: none;
    background: var(--primary-subtle);
    color: var(--primary-blue);
    font-size: 1.1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.12s ease;
    line-height: 1;
  }

  .card-step-btn:hover {
    background: var(--primary-blue);
    color: #ffffff;
  }

  .card-step-btn:active {
    transform: scale(0.9);
  }

  .card-step-qty {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--primary-blue);
    padding: 0 0.5rem;
  }

  /* Outline Icon Boxes */
  .outline-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    border: 1px solid var(--border-card);
  }

  .icon-cyan    { border-color: #bae6fd; background: #f0f9ff; color: #0284c7; }
  .icon-magenta { border-color: #f5d0fe; background: #fdf4ff; color: #d946ef; }
  .icon-yellow  { border-color: #fde68a; background: #fffbeb; color: #d97706; }
  .icon-black   { border-color: #cbd5e1; background: #f8fafc; color: #334155; }
  .icon-sponge  { border-color: #fed7aa; background: #fff7ed; color: #ea580c; }
  .icon-liquid  { border-color: #99f6e4; background: #f0fdf4; color: #0d9488; }
  .icon-paper   { border-color: #bfdbfe; background: #eff6ff; color: #2563eb; }
  .icon-film    { border-color: #ddd6fe; background: #f5f3ff; color: #7c3aed; }

  /* Outline Status Pills */
  .outline-status-pill {
    padding: 0.25rem 0.6rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    border: 1px solid var(--border-card);
  }

  .pill-ok-green   { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
  .pill-warn-amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
  .pill-empty-red  { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

  /* ── SECTION 2: PROFESSIONAL FORM SHEET ── */
  .outline-form-sheet {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    padding: 1.35rem;
    box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.04);
    margin-top: 1.25rem;
    position: relative;
    overflow: hidden;
    animation: slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .outline-form-sheet::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2563eb 0%, #3b82f6 35%, #06b6d4 70%, #10b981 100%);
  }

  @keyframes slideDown {
    0% { opacity: 0; transform: translateY(-10px); }
    100% { opacity: 1; transform: translateY(0); }
  }

  /* Form Header */
  .form-sheet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: nowrap;
  }

  .step-pill {
    background: #eff6ff;
    color: var(--primary-blue);
    border: 1px solid #bfdbfe;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: 8px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }

  .badge-cart-count {
    background: var(--primary-blue);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
  }

  .btn-clear-trash {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid #fee2e2;
    background: #fef2f2;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.15s ease;
  }
  .btn-clear-trash:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
  }

  /* ── CART ITEM CARDS ── */
  .cart-item-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 0.85rem 1rem;
    transition: all 0.15s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  .cart-item-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  }

  .btn-item-delete {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #fee2e2;
    background: #fef2f2;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.15s ease;
  }
  .btn-item-delete:hover {
    background: #ef4444;
    color: #ffffff;
  }

  /* Modern Pill Stepper */
  .stepper-pill {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 2px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
  }

  .step-arrow-btn {
    width: 32px;
    height: 30px;
    border-radius: 7px;
    border: none;
    background: #ffffff;
    color: var(--primary-blue);
    font-weight: 800;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: all 0.12s ease;
    line-height: 1;
  }
  .step-arrow-btn:hover { background: var(--primary-blue); color: #ffffff; }
  .step-arrow-btn:active { transform: scale(0.9); }

  .step-num-input {
    width: 44px;
    border: none;
    background: transparent;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    outline: none;
    padding: 0;
  }

  .stock-remaining-pill {
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.35rem 0.7rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
  }
  .stock-remaining-pill.green {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
  }
  .stock-remaining-pill.orange {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
  }

  /* ── QUICK OPERATOR PILLS WITH AVATARS ── */
  .taker-chips-scroll {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    padding: 0.2rem 0 0.35rem 0;
  }

  .taker-pill {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    color: #334155;
    padding: 0.28rem 0.75rem 0.28rem 0.35rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    user-select: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
  }

  .taker-pill:hover {
    border-color: #93c5fd;
    background: #f8faff;
    transform: translateY(-1px);
  }

  .taker-pill.active {
    background: #eff6ff;
    border-color: var(--primary-blue);
    color: var(--primary-blue);
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.18);
  }

  .taker-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    flex-shrink: 0;
  }

  .taker-pill .taker-check {
    font-size: 0.7rem;
    display: none;
    margin-left: 0.1rem;
  }
  .taker-pill.active .taker-check {
    display: inline-block;
  }

  /* Enhanced Input Field */
  .operator-input-wrap {
    position: relative;
    width: 100%;
  }

  .operator-input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
  }

  .operator-input-field {
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    padding: 0.55rem 1rem 0.55rem 2.3rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    width: 100%;
    outline: none;
    transition: all 0.15s ease;
    background: #ffffff;
  }

  .operator-input-field:focus {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  }

  /* ── MODERN 5 REASONS CARDS ── */
  .reasons-modern-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.55rem;
    margin-bottom: 0.85rem;
  }

  @media (max-width: 576px) {
    .reasons-modern-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .reason-modern-card.span-mobile-2 {
      grid-column: span 2;
    }
  }

  .reason-modern-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 0.65rem 0.55rem;
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    gap: 0.6rem;
    position: relative;
    user-select: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
  }

  .reason-modern-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateY(-1px);
  }

  .reason-icon-circle {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
  }

  .reason-title-text {
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    line-height: 1.2;
  }

  /* Theme color sets for reasons */
  .theme-prod .reason-icon-circle { background: #eff6ff; color: #2563eb; }
  .theme-maint .reason-icon-circle { background: #fffbeb; color: #d97706; }
  .theme-clean .reason-icon-circle { background: #f0fdf4; color: #0d9488; }
  .theme-damage .reason-icon-circle { background: #fef2f2; color: #e11d48; }
  .theme-other .reason-icon-circle { background: #f5f3ff; color: #7c3aed; }

  /* Selected Reason State */
  .reason-modern-card.selected {
    border-color: var(--primary-blue);
    background: #f8faff;
    box-shadow: 0 0 0 2px var(--primary-blue), 0 3px 10px rgba(37, 99, 235, 0.12);
  }
  .reason-modern-card.selected .reason-title-text {
    color: var(--primary-blue);
  }

  /* ── BIG PRIMARY CONFIRM BUTTON ── */
  .btn-confirm-prof {
    width: 100%;
    padding: 0.95rem 1.2rem;
    border-radius: 14px;
    font-size: 1.05rem;
    font-weight: 700;
    border: none;
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
    color: #ffffff;
    box-shadow: 0 8px 22px rgba(37, 99, 235, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .btn-confirm-prof:hover {
    background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 60%, #2563eb 100%);
    box-shadow: 0 10px 26px rgba(37, 99, 235, 0.45);
    transform: translateY(-1px);
  }

  .btn-confirm-prof:active { transform: scale(0.98); }

  /* Sticky Floating Cart Summary Bar */
  .floating-cart-bar {
    position: fixed;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%) translateY(120%);
    width: calc(100% - 32px);
    max-width: 640px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #ffffff;
    border-radius: 20px;
    padding: 0.65rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(255, 255, 255, 0.12);
    z-index: 1050;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    opacity: 0;
    pointer-events: none;
  }

  .floating-cart-bar.visible {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
    pointer-events: auto;
  }

  .cart-bubble-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(37, 99, 235, 0.35);
    border: 1px solid rgba(37, 99, 235, 0.5);
    color: #60a5fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    position: relative;
    flex-shrink: 0;
  }

  .cart-bubble-badge {
    position: absolute;
    top: -5px;
    right: -6px;
    background: #ef4444;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 999px;
    border: 2px solid #0f172a;
  }

  .btn-review-cart {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    padding: 0.55rem 1.1rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    transition: all 0.15s ease;
  }

  .btn-review-cart:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
  }

  .btn-clear-float {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #cbd5e1;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .btn-clear-float:hover {
    background: rgba(239, 68, 68, 0.25);
    border-color: #ef4444;
    color: #fca5a5;
  }
</style>

<div class="stock-outline-wrapper">

  <!-- Professional Header Card -->
  <div class="outline-header-card d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2.5">
      <div style="width: 38px; height: 38px; border-radius: 12px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 1.15rem; border: 1.5px solid #bfdbfe;">
        <i class="fa-solid fa-boxes-packing"></i>
      </div>
      <div>
        <h6 class="fw-bold text-dark m-0" style="font-size: 1rem; line-height: 1.2;">ដកស្តុកទំនិញ (Stock Out)</h6>
        <span class="text-muted" style="font-size: 0.75rem;">PrintTracker Pro • Warehouse Check-Out</span>
      </div>
    </div>

    <!-- User Identity Badge & View Mode Switcher -->
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-light text-dark border border-secondary-subtle px-2.5 py-1.5 rounded-pill fw-bold text-xs" id="headerUserBadge">
        👤 {{ session('user_name', 'User') }}
      </span>

      <div class="outline-mode-bar">
        <button type="button" class="btn-mode-outline active" onclick="switchViewMode('visual', this)" title="រូបភាព (Visual Cards)">
          <i class="fa-solid fa-border-all"></i>
          <span>រូបភាព</span>
        </button>
        <button type="button" class="btn-mode-outline" onclick="switchViewMode('table', this)" title="តារាង (Table View)">
          <i class="fa-solid fa-table-list"></i>
          <span>តារាង</span>
        </button>
        <button type="button" class="btn-mode-outline" onclick="switchViewMode('history', this)" title="ប្រវត្តិ (Recent Activity)">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span>ប្រវត្តិ</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ── MODE 1: VISUAL OUTLINE TOUCH VIEW ── -->
  <div id="modeVisual">

    @php
      $allCount = $materials->count();
      $consumableCount = $materials->where('category', 'consumable')->count();
      $paperCount = $materials->where('category', 'paper')->count();
      $filmCount = $materials->where('category', 'film')->count();
      $offsetCount = $materials->where('category', 'offset')->count();
    @endphp

    <!-- Category Filter Chips with Live Counter Badges -->
    <div class="chips-outline-scroll">
      <div class="outline-chip" onclick="filterCat('all', this)">
        <i class="fa-solid fa-layer-group text-warning"></i>
        <span>ទាំងអស់ (All)</span>
        <span class="chip-count-badge">{{ $allCount }}</span>
      </div>
      <div class="outline-chip active" id="chipConsumable" onclick="filterCat('consumable', this)">
        <i class="fa-solid fa-droplet text-info"></i>
        <span>Consumables</span>
        <span class="chip-count-badge">{{ $consumableCount }}</span>
      </div>
      <div class="outline-chip" onclick="filterCat('paper', this)">
        <i class="fa-solid fa-file-lines text-primary"></i>
        <span>ក្រដាស (Paper)</span>
        <span class="chip-count-badge">{{ $paperCount }}</span>
      </div>
      <div class="outline-chip" onclick="filterCat('film', this)">
        <i class="fa-solid fa-film text-warning"></i>
        <span>Film / ស្គុត</span>
        <span class="chip-count-badge">{{ $filmCount }}</span>
      </div>
      @if($offsetCount > 0)
        <div class="outline-chip" onclick="filterCat('offset', this)">
          <i class="fa-solid fa-print text-success"></i>
          <span>Offset</span>
          <span class="chip-count-badge">{{ $offsetCount }}</span>
        </div>
      @endif
    </div>

    <!-- Search Input & Results Status -->
    <div class="d-flex justify-content-between align-items-center mb-2.5 flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold text-secondary text-sm">
          <i class="bi bi-1-circle-fill text-primary me-1"></i> ជ្រើសរើសទំនិញ (Select Item):
        </span>
        <span id="searchResultsCount" class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">
          បង្ហាញ {{ $consumableCount }} មុខ
        </span>
      </div>

      <div class="search-wrap">
        <i class="fa-solid fa-magnifying-glass search-icon-left"></i>
        <input type="text" id="searchInput" class="search-input-field" placeholder="ស្វែងរកទំនិញ (Search material)..." oninput="searchItems()">
        <button type="button" id="searchClearBtn" class="search-clear-btn" onclick="clearSearch()" title="Clear search">
          <i class="fa-solid fa-circle-xmark"></i>
        </button>
      </div>
    </div>

    <!-- Outline Material Cards Grid -->
    <div class="materials-outline-grid" id="itemsContainer">
      @foreach($materials as $m)
        @php
          $stock = $m->calculated_stock;
          $isOutOfStock = $stock <= 0;
          $badgeClass = $isOutOfStock ? 'pill-empty-red' : ($m->isLowStock($stock) ? 'pill-warn-amber' : 'pill-ok-green');
          $statusDot  = $isOutOfStock ? '⚫' : ($m->isLowStock($stock) ? '🟡' : '🟢');
          $statusText = $isOutOfStock ? 'អស់ស្តុក' : ($m->isLowStock($stock) ? 'ស្តុកទាប' : 'មានស្តុក');

          $iconStr = trim($m->icon ?? '');
          $nameLower = strtolower($m->name . ' ' . ($m->name_km ?? ''));
          $iconClass = 'icon-paper';
          $iconMarkup = '';

          if (str_contains($nameLower, 'cyan')) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'magenta')) {
              $iconClass = 'icon-magenta';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'yellow')) {
              $iconClass = 'icon-yellow';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'black')) {
              $iconClass = 'icon-black';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'sponge') || str_contains($nameLower, 'ប្រឡះ') || str_contains($nameLower, 'ស្បៃ') || str_contains($nameLower, 'អេប៉ុង')) {
              $iconClass = 'icon-sponge';
              $iconMarkup = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 11c0-2.5-2.5-4-5-2-2.5-2-6-1.5-7 1-1 2.5 0 5.5 2.5 6.5s5.5.5 6.5-1.5c1-2 3-1.5 3-4z"/><circle cx="9" cy="11" r="0.9" fill="currentColor"/><circle cx="14" cy="11" r="1.2" fill="currentColor"/><circle cx="12" cy="14" r="0.9" fill="currentColor"/><circle cx="8" cy="14" r="0.7" fill="currentColor"/><circle cx="16" cy="13" r="0.7" fill="currentColor"/></svg>';
          } elseif (str_contains($nameLower, 'liquid') || str_contains($nameLower, 'cleaner') || str_contains($nameLower, 'ទឹក')) {
              $iconClass = 'icon-liquid';
              $iconMarkup = '<i class="fa-solid fa-spray-can-sparkles"></i>';
          } elseif ($m->category === 'paper') {
              $iconClass = 'icon-paper';
              $iconMarkup = '<i class="fa-solid fa-file-lines"></i>';
          } elseif ($m->category === 'film') {
              $iconClass = 'icon-film';
              $iconMarkup = '<i class="fa-solid fa-film"></i>';
          } elseif (!empty($iconStr) && (str_contains($iconStr, 'fa-') || str_contains($iconStr, 'fa '))) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<i class="' . e($iconStr) . '"></i>';
          } elseif (!empty($iconStr)) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<span>' . e($iconStr) . '</span>';
          } else {
              $iconClass = 'icon-paper';
              $iconMarkup = '<i class="fa-solid fa-box-archive"></i>';
          }
        @endphp

        <div class="outline-item-card item-row {{ $isOutOfStock ? 'out-of-stock-card' : '' }}"
             data-category="{{ $m->category }}"
             data-id="{{ $m->id }}"
             data-name="{{ $m->name_km ?: $m->name }}"
             data-rawname="{{ strtolower($m->name . ' ' . $m->name_km) }}"
             data-unit="{{ $m->unit }}"
             data-stock="{{ $stock }}"
             data-icon-class="{{ $iconClass }}"
             onclick="handleCardClick(this, {{ $isOutOfStock ? 'true' : 'false' }})">
          
          <span class="cart-badge"><i class="fa-solid fa-check me-0.5"></i> <span class="cart-badge-qty">1</span></span>
          
          <div>
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="outline-icon-box {{ $iconClass }}">{!! $iconMarkup !!}</div>
              <div class="outline-status-pill {{ $badgeClass }}" title="{{ $statusText }}">
                <span>{{ $statusDot }}</span>
                <span>{{ $stock + 0 }} {{ $m->unit }}</span>
              </div>
            </div>

            <div>
              <div class="fw-bold text-dark" style="font-size:0.86rem;line-height:1.25">{{ $m->name }}</div>
              @if($m->name_km)
                <div class="text-muted mt-0.5" style="font-size:0.73rem">{{ $m->name_km }}</div>
              @endif
            </div>
          </div>

          <!-- On-Card Stepper: Appears when card is selected -->
          <div class="card-stepper-wrap" onclick="event.stopPropagation()">
            <button type="button" class="card-step-btn" onclick="adjustCartQty({{ $m->id }}, -1, event)" title="Decrease">−</button>
            <span class="card-step-qty" id="cardStepQty-{{ $m->id }}">1</span>
            <button type="button" class="card-step-btn" onclick="adjustCartQty({{ $m->id }}, 1, event)" title="Increase">+</button>
          </div>
        </div>
      @endforeach
    </div>

    <!-- ── SECTION 2 & 3: MULTI-ITEM PROFESSIONAL FORM SHEET ── -->
    <div id="formSection" style="display: none;" class="outline-form-sheet">
      <!-- Clean Single-Row Form Header -->
      <div class="form-sheet-header mb-3">
        <div class="d-flex align-items-center gap-2 text-truncate">
          <span class="step-pill flex-shrink-0"><i class="fa-solid fa-list-check me-1"></i> ជំហាន ២</span>
          <h6 class="fw-bold text-dark m-0" style="font-size: 0.92rem; white-space: nowrap;">ព័ត៌មានដកស្តុក</h6>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <span id="cartCountBadge" class="badge-cart-count" style="white-space: nowrap;">0 មុខ (0 ចំនួន)</span>
          <button type="button" class="btn-clear-trash flex-shrink-0" onclick="clearCart()" title="លុបទាំងអស់ (Clear All)">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </div>
      </div>

      <!-- Selected Items Cart Container -->
      <div class="mb-3.5">
        <label class="form-label fw-bold text-dark mb-2" style="font-size:0.84rem">
          <i class="fa-solid fa-boxes-stacked text-primary me-1.5"></i> មុខទំនិញដែលបានជ្រើសរើស (Selected Items):
        </label>
        <div id="cartItemsList" class="d-flex flex-column gap-2.5">
          <!-- Rendered dynamically via renderCart() -->
        </div>
      </div>

      <!-- DIRECT TAKEN BY NAME INPUT WITH AVATAR CHIPS -->
      <div class="mb-3.5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1.5">
          <label class="form-label fw-bold text-dark mb-0" style="font-size:0.84rem">
            <i class="fa-solid fa-user-check text-primary me-1.5"></i> ឈ្មោះអ្នកយកស្តុក (Taken By):
          </label>
          <span class="text-muted" style="font-size:0.72rem;">
            <i class="fa-solid fa-hand-pointer text-primary me-0.5"></i> ជ្រើសរើសរហ័ស (Quick Pick):
          </span>
        </div>

        <!-- Quick Operator Avatar Pills -->
        <div class="taker-chips-scroll mb-2" id="frequentTakersContainer">
          @foreach($frequentTakers as $taker)
            @php
              $tName = is_array($taker) ? ($taker['name'] ?? '') : (is_object($taker) ? ($taker->name ?? '') : (string) $taker);
              $tInit = is_array($taker) ? ($taker['initial'] ?? '') : (is_object($taker) ? ($taker->initial ?? '') : mb_substr($tName, 0, 1, 'UTF-8'));
              $tColor = is_array($taker) ? ($taker['color'] ?? '#2563eb') : (is_object($taker) ? ($taker->color ?? '#2563eb') : '#2563eb');
            @endphp
            <button type="button" class="taker-pill" onclick="setQuickName('{{ e($tName) }}', this)">
              <span class="taker-avatar" style="background-color: {{ $tColor }};">{{ $tInit }}</span>
              <span class="taker-label">{{ $tName }}</span>
              <i class="fa-solid fa-check taker-check"></i>
            </button>
          @endforeach
        </div>

        <!-- Name Input with clear / edit -->
        <div class="operator-input-wrap">
          <i class="fa-solid fa-signature operator-input-icon"></i>
          <input type="text" id="inputTakenByName" class="operator-input-field" placeholder="ឬវាយបញ្ចូលឈ្មោះអ្នកយកផ្សេងទៀត... (Or enter other name)" oninput="onCustomNameInput(this.value)">
        </div>

        <div id="recorderInfoBadge" class="small text-muted mt-1.5 d-none" style="font-size:0.74rem;">
          <i class="fa-solid fa-user-shield text-success me-1"></i> <strong>អ្នកកត់ត្រា (Telegram):</strong> <span id="recorderNameDisplay" class="fw-bold text-dark"></span>
        </div>
      </div>

      <!-- Modern 5-Reason Cards Grid -->
      <label class="form-label text-dark fw-bold mb-2" style="font-size:0.84rem">
        <i class="fa-solid fa-bullseye text-primary me-1.5"></i> គោលបំណង (Reason):
      </label>
      <div class="reasons-modern-grid">
        <div class="reason-modern-card theme-prod selected" onclick="selectReason('Production', this)">
          <div class="reason-icon-circle"><i class="fa-solid fa-print"></i></div>
          <div class="reason-title-text">ការបោះពុម្ព</div>
        </div>
        <div class="reason-modern-card theme-maint" onclick="selectReason('Machine maintenance', this)">
          <div class="reason-icon-circle"><i class="fa-solid fa-wrench"></i></div>
          <div class="reason-title-text">ថែទាំម៉ាស៊ីន</div>
        </div>
        <div class="reason-modern-card theme-clean" onclick="selectReason('Cleaning', this)">
          <div class="reason-icon-circle"><i class="fa-solid fa-broom"></i></div>
          <div class="reason-title-text">សម្អាត</div>
        </div>
        <div class="reason-modern-card theme-damage" onclick="selectReason('Damaged', this)">
          <div class="reason-icon-circle"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="reason-title-text">ខូចខាត</div>
        </div>
        <div class="reason-modern-card theme-other span-mobile-2" onclick="selectReason('Other', this)">
          <div class="reason-icon-circle"><i class="fa-solid fa-pen-to-square"></i></div>
          <div class="reason-title-text">ផ្សេងៗ (Other)</div>
        </div>
      </div>

      <div id="otherNoteBox" style="display: none;" class="mb-3">
        <input type="text" id="inputOtherNote" class="form-control form-control-sm bg-light text-dark border-secondary p-2.5 rounded-3" placeholder="បញ្ជាក់មូលហេតុបន្ថែម (Specify note)...">
      </div>

      <!-- Premium Big Confirm Button -->
      <button class="btn-confirm-prof mt-3" type="button" onclick="openConfirmationModal()">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <span id="btnConfirmText">បញ្ជាក់ដកស្តុក (Confirm Stock Out)</span>
      </button>
    </div>

  </div>

  <!-- ── MODE 2: INVENTORY TABLE VIEW ── -->
  <div id="modeTable" style="display: none;">
    <div class="panel bg-white border rounded-4 p-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
          <div style="width: 32px; height: 32px; border-radius: 8px; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-box-seam"></i>
          </div>
          <span class="fs-6 fw-bold">បញ្ជីវត្ថុធាតុដើម (Consumables List)</span>
          <span class="badge bg-primary rounded-pill">{{ $materials->count() }}</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr style="font-size: 0.82rem;">
              <th>Code</th>
              <th>Item Name</th>
              <th>Category</th>
              <th class="text-end">Current Stock</th>
              <th class="text-center">Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($materials as $m)
              @php $stk = $m->calculated_stock; @endphp
              <tr style="font-size: 0.85rem;">
                <td class="fw-bold text-primary">{{ $m->code }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ $m->name }}</div>
                  @if($m->name_km)<div class="small text-muted">{{ $m->name_km }}</div>@endif
                </td>
                <td><span class="badge bg-light text-secondary border">{{ $m->categoryLabelShort() }}</span></td>
                <td class="text-end fw-bold fs-6">{{ $stk + 0 }} {{ $m->unit }}</td>
                <td class="text-center">{!! $m->stockStatusBadge($stk) !!}</td>
                <td class="text-center">
                  <button type="button" class="btn btn-primary btn-sm fw-bold px-2.5 py-1 rounded-pill table-cart-btn"
                          id="tblBtn-{{ $m->id }}"
                          style="font-size:0.78rem"
                          onclick="toggleCartFromTable({{ json_encode(['id'=>$m->id, 'name'=>$m->name_km ?: $m->name, 'stock'=>$stk, 'unit'=>$m->unit, 'iconClass'=>'icon-paper', 'iconHtml'=>'<i class=\"fa-solid fa-box-archive\"></i>']) }})">
                    <i class="bi bi-cart-plus me-1"></i> ជ្រើសរើស
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── MODE 3: RECENT HISTORY LOG ── -->
  <div id="modeHistory" style="display: none;">
    <div class="panel bg-white border rounded-4 p-3.5 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold m-0 text-dark">
          <i class="fa-solid fa-clock-rotate-left text-warning me-1.5"></i> ប្រវត្តិ/បញ្ជូន (Activity Log)
        </h6>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="loadHistoryLog()">
          <i class="fa-solid fa-rotate me-1"></i> Refresh
        </button>
      </div>
      <!-- Filter Pills -->
      <div class="d-flex gap-1.5 mb-3 flex-wrap" id="historyFilterPills">
        <button type="button" id="hfPillOut" class="btn btn-sm rounded-pill fw-semibold px-3 btn-danger active-hf" onclick="setHistoryFilter('out', this)">
          <i class="fa-solid fa-arrow-up-from-bracket me-1"></i>ដកស្តុក
        </button>
        <button type="button" id="hfPillAll" class="btn btn-sm rounded-pill fw-semibold px-3 btn-outline-secondary" onclick="setHistoryFilter('all', this)">
          <i class="fa-solid fa-list me-1"></i>ទាំងអស់
        </button>
        <button type="button" id="hfPillAdjust" class="btn btn-sm rounded-pill fw-semibold px-3 btn-outline-primary" onclick="setHistoryFilter('adjust', this)">
          <i class="fa-solid fa-sliders me-1"></i>កែតម្រូវ
        </button>
      </div>
      <div id="historyListContainer">
        <div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading...</div>
      </div>
    </div>
  </div>

</div>

<!-- ── STICKY FLOATING BOTTOM CART BAR ── -->
<div id="floatingCartBar" class="floating-cart-bar">
  <div class="d-flex align-items-center gap-2.5">
    <div class="cart-bubble-icon">
      <i class="fa-solid fa-cart-shopping"></i>
      <span class="cart-bubble-badge" id="floatCartBadge">0</span>
    </div>
    <div>
      <div class="fw-bold text-white text-sm" id="floatCartTitle">0 មុខទំនិញ</div>
      <div class="text-muted text-xs" style="color: #94a3b8 !important;" id="floatCartSub">ចុចដើម្បីពិនិត្យ & បញ្ជាក់</div>
    </div>
  </div>
  <div class="d-flex align-items-center gap-2">
    <button type="button" class="btn-clear-float" onclick="clearCart()" title="លុបទាំងអស់ (Clear all)">
      <i class="fa-solid fa-trash-can"></i>
    </button>
    <button type="button" class="btn-review-cart" onclick="scrollToFormOrReview()">
      <span>ពិនិត្យ & បញ្ជាក់</span>
      <i class="fa-solid fa-arrow-right"></i>
    </button>
  </div>
</div>

<!-- CONFIRMATION MODAL SHEET -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-2.5 px-3">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-shield-halved text-warning fs-5"></i>
          <h6 class="modal-title fw-bold text-white m-0">ផ្ទៀងផ្ទាត់ការដកស្តុក (Confirm Stock Out)</h6>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">
        <div class="mb-2 fw-bold text-dark small d-flex justify-content-between align-items-center">
          <span><i class="fa-solid fa-boxes-stacked text-primary me-1"></i> បញ្ជីទំនិញដែលត្រូវដកចេញ:</span>
          <span class="badge bg-primary text-white rounded-pill" id="mTotalItemsBadge">0 មុខ</span>
        </div>
        <div class="table-responsive mb-3 border rounded-3 bg-white" style="max-height: 220px; overflow-y: auto;">
          <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.85rem">
            <thead class="table-light sticky-top" style="font-size: 0.75rem;">
              <tr>
                <th style="padding-left:0.75rem;">មុខទំនិញ (Item)</th>
                <th class="text-center" style="width:110px;">ចំនួនដក (Qty)</th>
                <th class="text-end" style="width:100px; padding-right:0.75rem;">នៅសល់</th>
              </tr>
            </thead>
            <tbody id="mItemsTableBody">
              <!-- Dynamically populated -->
            </tbody>
          </table>
        </div>
        
        <table class="table table-bordered bg-white m-0 rounded-3 overflow-hidden shadow-sm" style="font-size:0.85rem">
          <tbody>
            <tr>
              <td class="text-muted fw-semibold" style="width: 140px;">គោលបំណង (Reason):</td>
              <td class="fw-bold text-dark" id="mReason">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">អ្នកដក (Taken by):</td>
              <td class="fw-bold text-primary" id="mTakenBy">Davy</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">កាលបរិច្ឆេទ (Date):</td>
              <td class="text-muted" id="mDateTime">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">លេខប្រតិបត្តិការ:</td>
              <td class="fw-bold text-primary font-monospace" id="mRefCode">SO-{{ date('Ymd') }}-...</td>
            </tr>
          </tbody>
        </table>

      </div>
      <div class="modal-footer bg-white border-0 p-2.5 d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary flex-grow-1 py-2 fw-bold rounded-3" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark me-1"></i> បោះបង់
        </button>
        <button type="button" class="btn btn-primary flex-grow-2 py-2 fw-bold fs-6 rounded-3" id="btnFinalConfirm" onclick="submitStockOut()">
          <i class="fa-solid fa-circle-check me-1"></i> បញ្ជាក់ដកស្តុក
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  let selectedReason = 'Production';
  let stockOutCart = [];

  // Telegram Haptic Feedback trigger
  function triggerHaptic(type = 'light') {
    try {
      const haptic = window.Telegram?.WebApp?.HapticFeedback;
      if (!haptic) return;
      if (type === 'selection') {
        haptic.selectionChanged();
      } else if (type === 'success' || type === 'error' || type === 'warning') {
        haptic.notificationOccurred(type);
      } else {
        haptic.impactOccurred(type);
      }
    } catch (e) {}
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Restore memory for Taken By name
    const savedName = localStorage.getItem('stock_out_taken_by') || "{{ session('user_name', 'User') }}";
    const nameInput = document.getElementById('inputTakenByName');
    const headerBadge = document.getElementById('headerUserBadge');
    
    if (nameInput) {
      nameInput.value = savedName;
      highlightMatchingTakerPill(savedName);
    }

    // Pin Consumables category by default
    const chipC = document.getElementById('chipConsumable');
    if (chipC) {
      filterCat('consumable', chipC);
    }

    const tg = window.Telegram?.WebApp;
    if (tg && tg.initDataUnsafe?.user) {
      tg.ready();
      tg.expand();
      const user = tg.initDataUnsafe.user;
      const firstName = user.first_name || '';
      const lastName  = user.last_name || '';
      const fullName  = (firstName + ' ' + lastName).trim() || user.username || 'Telegram User';
      const tgIdentity = user.username ? `${fullName} (@${user.username})` : fullName;

      if (nameInput && !localStorage.getItem('stock_out_taken_by')) {
        nameInput.value = firstName || fullName;
        highlightMatchingTakerPill(firstName || fullName);
      }
      if (headerBadge) {
        headerBadge.innerHTML = `<i class="fa-solid fa-shield-check text-success me-1"></i> ${fullName}`;
        headerBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fw-bold text-xs';
      }
      const recBadge = document.getElementById('recorderInfoBadge');
      const recNameDisp = document.getElementById('recorderNameDisplay');
      if (recBadge && recNameDisp) {
        recNameDisp.innerText = tgIdentity;
        recBadge.classList.remove('d-none');
      }
    } else {
      @if(auth()->check())
        const webName = "{{ auth()->user()->name }}";
        if (nameInput && !localStorage.getItem('stock_out_taken_by')) {
          nameInput.value = webName;
          highlightMatchingTakerPill(webName);
        }
        if (headerBadge) {
          headerBadge.innerHTML = `<i class="fa-solid fa-user-check text-primary me-1"></i> ${webName}`;
        }
      @endif
    }
  });

  function saveTakenByName(val) {
    if (val && val.trim()) {
      localStorage.setItem('stock_out_taken_by', val.trim());
    }
  }

  function setQuickName(name, el) {
    triggerHaptic('selection');
    const nameInput = document.getElementById('inputTakenByName');
    if (nameInput) {
      nameInput.value = name;
    }
    saveTakenByName(name);

    document.querySelectorAll('.taker-pill').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
  }

  function onCustomNameInput(val) {
    saveTakenByName(val);
    highlightMatchingTakerPill(val);
  }

  function highlightMatchingTakerPill(name) {
    const trimmed = (name || '').trim().toLowerCase();
    document.querySelectorAll('.taker-pill').forEach(c => {
      const pillText = c.querySelector('.taker-label')?.innerText.trim().toLowerCase() || '';
      if (pillText && pillText === trimmed) {
        c.classList.add('active');
      } else {
        c.classList.remove('active');
      }
    });
  }

  function switchViewMode(mode, el) {
    triggerHaptic('selection');
    document.querySelectorAll('.btn-mode-outline').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    document.getElementById('modeVisual').style.display = 'none';
    document.getElementById('modeTable').style.display = 'none';
    document.getElementById('modeHistory').style.display = 'none';

    if (mode === 'visual') {
      document.getElementById('modeVisual').style.display = 'block';
    } else if (mode === 'table') {
      document.getElementById('modeTable').style.display = 'block';
    } else if (mode === 'history') {
      document.getElementById('modeHistory').style.display = 'block';
      loadHistoryLog();
    }
  }

  function filterCat(cat, el) {
    triggerHaptic('selection');
    document.querySelectorAll('.outline-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    let visibleCount = 0;
    const query = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';

    document.querySelectorAll('.item-row').forEach(row => {
      const matchesCat = (cat === 'all' || row.dataset.category === cat);
      const matchesSearch = !query || (row.dataset.rawname || '').includes(query);

      if (matchesCat && matchesSearch) {
        row.style.display = 'flex';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    const resBadge = document.getElementById('searchResultsCount');
    if (resBadge) {
      resBadge.innerText = `បង្ហាញ ${visibleCount} មុខ`;
    }
  }

  function searchItems() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const clearBtn = document.getElementById('searchClearBtn');
    if (clearBtn) {
      clearBtn.style.display = q ? 'block' : 'none';
    }

    const activeChip = document.querySelector('.outline-chip.active');
    const currentCat = activeChip?.getAttribute('onclick')?.match(/'([^']+)'/)?.[1] || 'all';

    let visibleCount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
      const text = row.dataset.rawname || '';
      const matchesCat = (currentCat === 'all' || row.dataset.category === currentCat);
      const matchesSearch = !q || text.includes(q);

      if (matchesCat && matchesSearch) {
        row.style.display = 'flex';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    const resBadge = document.getElementById('searchResultsCount');
    if (resBadge) {
      resBadge.innerText = `បង្ហាញ ${visibleCount} មុខ`;
    }
  }

  function clearSearch() {
    const input = document.getElementById('searchInput');
    if (input) {
      input.value = '';
      searchItems();
      input.focus();
    }
  }

  function handleCardClick(el, isOutOfStock) {
    if (isOutOfStock) {
      triggerHaptic('warning');
      Swal.fire({
        icon: 'warning',
        title: 'អស់ស្តុក (Out of Stock)',
        text: `មុខទំនិញ ${el.dataset.name} មិនមានស្តុកនៅសល់ទេ!`,
        confirmButtonColor: '#2563eb',
        timer: 1800,
        showConfirmButton: false
      });
      return;
    }

    toggleCartItem(el);
  }

  function toggleCartItem(el) {
    triggerHaptic('light');
    const id = parseInt(el.dataset.id);
    const stock = parseFloat(el.dataset.stock) || 0;
    const name = el.dataset.name;
    const unit = el.dataset.unit;
    const iconClass = el.dataset.iconClass || 'icon-paper';
    const iconHtml = el.querySelector('.outline-icon-box')?.innerHTML || '<i class="fa-solid fa-box-archive"></i>';

    const existingIdx = stockOutCart.findIndex(item => item.id === id);

    if (existingIdx >= 0) {
      // Item already in cart -> increment if stock allows
      const item = stockOutCart[existingIdx];
      if (item.qty < item.stock) {
        item.qty += 1;
      } else {
        triggerHaptic('warning');
        Swal.fire({
          icon: 'info',
          title: 'ដល់ចំនួនស្តុកអតិបរមា',
          text: `${name} មានស្តុកសរុបត្រឹម ${item.stock} ${item.unit} ប៉ុណ្ណោះ។`,
          timer: 1500,
          showConfirmButton: false
        });
      }
    } else {
      // Adding new item
      stockOutCart.push({
        id: id,
        name: name,
        stock: stock,
        unit: unit,
        qty: 1,
        iconClass: iconClass,
        iconHtml: iconHtml
      });
    }

    renderCart();
  }

  function adjustCartQty(id, delta, evt) {
    if (evt) evt.stopPropagation();
    triggerHaptic('light');

    const item = stockOutCart.find(it => it.id === id);
    if (!item) return;

    const newQty = item.qty + delta;
    if (newQty <= 0) {
      removeFromCart(id);
      return;
    }
    if (newQty > item.stock) {
      triggerHaptic('warning');
      Swal.fire({
        icon: 'warning',
        title: 'លើសស្តុក',
        text: `ចំនួនដកមិនអាចលើសពីស្តុកដែលមាន (${item.stock} ${item.unit})`,
        confirmButtonColor: '#2563eb',
        timer: 1500,
        showConfirmButton: false
      });
      return;
    }

    item.qty = newQty;
    renderCart();
  }

  function setCartQty(id, val) {
    const item = stockOutCart.find(it => it.id === id);
    if (!item) return;

    let num = parseFloat(val) || 1;
    if (num < 1) num = 1;
    if (num > item.stock) {
      num = item.stock;
      triggerHaptic('warning');
      Swal.fire({
        icon: 'warning',
        title: 'កែតម្រូវស្វ័យប្រវត្តិ',
        text: `បានកែត្រឹមស្តុកអតិបរមា (${item.stock} ${item.unit})`,
        timer: 1500,
        showConfirmButton: false
      });
    }

    item.qty = num;
    renderCart();
  }

  function removeFromCart(id) {
    triggerHaptic('light');
    stockOutCart = stockOutCart.filter(it => it.id !== id);
    renderCart();
  }

  function clearCart() {
    triggerHaptic('medium');
    stockOutCart = [];
    renderCart();
  }

  function toggleCartFromTable(mat) {
    triggerHaptic('light');
    const existingIdx = stockOutCart.findIndex(it => it.id === mat.id);
    if (existingIdx >= 0) {
      removeFromCart(mat.id);
    } else {
      if (mat.stock <= 0) {
        triggerHaptic('warning');
        Swal.fire({
          icon: 'warning',
          title: 'អស់ស្តុក',
          text: `មុខទំនិញ ${mat.name} មិនមានស្តុកនៅសល់ទេ!`,
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      stockOutCart.push({
        id: mat.id,
        name: mat.name,
        stock: mat.stock,
        unit: mat.unit,
        qty: 1,
        iconClass: mat.iconClass || 'icon-paper',
        iconHtml: mat.iconHtml || '<i class="fa-solid fa-box-archive"></i>'
      });
      renderCart();
    }
  }

  function scrollToFormOrReview() {
    triggerHaptic('selection');
    const formSection = document.getElementById('formSection');
    if (formSection) {
      formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function renderCart() {
    const formSection = document.getElementById('formSection');
    const container = document.getElementById('cartItemsList');
    const countBadge = document.getElementById('cartCountBadge');
    const btnText = document.getElementById('btnConfirmText');
    const floatBar = document.getElementById('floatingCartBar');
    const floatBadge = document.getElementById('floatCartBadge');
    const floatTitle = document.getElementById('floatCartTitle');
    const floatSub = document.getElementById('floatCartSub');

    // 1. Update all visual grid cards
    document.querySelectorAll('.outline-item-card').forEach(card => {
      const id = parseInt(card.dataset.id);
      const cartItem = stockOutCart.find(it => it.id === id);
      const badge = card.querySelector('.cart-badge');
      const badgeQty = card.querySelector('.cart-badge-qty');
      const stepQty = document.getElementById(`cardStepQty-${id}`);

      if (cartItem) {
        card.classList.add('selected');
        if (badge) badge.style.display = 'inline-block';
        if (badgeQty) badgeQty.innerText = cartItem.qty;
        if (stepQty) stepQty.innerText = cartItem.qty;
      } else {
        card.classList.remove('selected');
        if (badge) badge.style.display = 'none';
      }
    });

    // 2. Update table view buttons
    document.querySelectorAll('.table-cart-btn').forEach(btn => {
      const id = parseInt(btn.id.replace('tblBtn-', ''));
      const cartItem = stockOutCart.find(it => it.id === id);
      if (cartItem) {
        btn.className = 'btn btn-success btn-sm fw-bold px-2.5 py-1 rounded-pill table-cart-btn';
        btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> បានជ្រើស (${cartItem.qty})`;
      } else {
        btn.className = 'btn btn-primary btn-sm fw-bold px-2.5 py-1 rounded-pill table-cart-btn';
        btn.innerHTML = `<i class="bi bi-cart-plus me-1"></i> ជ្រើសរើស`;
      }
    });

    // 3. Handle Empty Cart
    if (stockOutCart.length === 0) {
      if (formSection) formSection.style.display = 'none';
      if (floatBar) floatBar.classList.remove('visible');
      return;
    }

    // 4. Cart has items: Show form sheet & floating bar
    if (formSection) formSection.style.display = 'block';
    if (floatBar) floatBar.classList.add('visible');

    const totalQty = stockOutCart.reduce((sum, it) => sum + it.qty, 0);

    if (countBadge) {
      countBadge.innerText = `${stockOutCart.length} មុខ (${totalQty} ចំនួន)`;
    }
    if (btnText) {
      btnText.innerText = `បញ្ជាក់ដកស្តុក (${stockOutCart.length} មុខ • ${totalQty} ចំនួន)`;
    }

    if (floatBadge) floatBadge.innerText = stockOutCart.length;
    if (floatTitle) floatTitle.innerText = `${stockOutCart.length} មុខទំនិញ (${totalQty} ចំនួន)`;
    if (floatSub) floatSub.innerText = `ចុចដើម្បីពិនិត្យ & បញ្ជាក់ (Review & Confirm)`;

    // 5. Render cart items inside Form Sheet
    if (container) {
      container.innerHTML = stockOutCart.map(item => {
        const remaining = item.stock - item.qty;
        const remBadgeClass = remaining <= 0 ? 'orange' : 'green';
        const remIcon = remaining <= 0 ? 'fa-triangle-exclamation' : 'fa-circle-check';
        const remText = remaining <= 0 ? `អស់ពីស្តុក (0 ${item.unit})` : `នៅសល់: ${remaining} ${item.unit}`;

        return `
          <div class="cart-item-card">
            <!-- Top Row: Icon + Names + Delete Button -->
            <div class="d-flex justify-content-between align-items-center mb-2.5">
              <div class="d-flex align-items-center gap-2.5">
                <div class="outline-icon-box ${item.iconClass}" style="width:38px; height:38px; font-size:1.05rem;">
                  ${item.iconHtml}
                </div>
                <div>
                  <div class="fw-bold text-dark" style="font-size:0.9rem; line-height:1.25">${item.name}</div>
                  <div class="text-muted" style="font-size:0.73rem">
                    ស្តុកដើម: <strong class="text-dark">${item.stock}</strong> ${item.unit}
                  </div>
                </div>
              </div>

              <!-- Delete Button -->
              <button type="button" class="btn-item-delete" onclick="removeFromCart(${item.id})" title="Remove item">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>

            <!-- Bottom Row: Touch Stepper + Real-time Balance Gauge -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
              <div class="stepper-pill">
                <button type="button" class="step-arrow-btn" onclick="adjustCartQty(${item.id}, -1)">−</button>
                <input type="number" class="step-num-input" value="${item.qty}" min="1" max="${item.stock}" onchange="setCartQty(${item.id}, this.value)">
                <button type="button" class="step-arrow-btn" onclick="adjustCartQty(${item.id}, 1)">+</button>
              </div>

              <div class="stock-remaining-pill ${remBadgeClass}">
                <i class="fa-solid ${remIcon} me-1"></i> ${remText}
              </div>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  function selectReason(reason, el) {
    triggerHaptic('selection');
    document.querySelectorAll('.reason-modern-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedReason = reason;

    const noteBox = document.getElementById('otherNoteBox');
    if (noteBox) {
      noteBox.style.display = reason === 'Other' ? 'block' : 'none';
      if (reason === 'Other') {
        document.getElementById('inputOtherNote')?.focus();
      }
    }
  }

  function openConfirmationModal() {
    triggerHaptic('light');

    if (stockOutCart.length === 0) {
      triggerHaptic('warning');
      Swal.fire({
        icon: 'warning',
        title: 'មិនទាន់ជ្រើសរើសទំនិញ',
        text: 'សូមជ្រើសរើសមុខទំនិញយ៉ាងហោចណាស់មួយ (Please select at least 1 item)',
        confirmButtonColor: '#2563eb'
      });
      return;
    }

    const takenBy = document.getElementById('inputTakenByName').value.trim() || 'Davy';
    if (!takenBy) {
      triggerHaptic('warning');
      Swal.fire({
        icon: 'warning',
        title: 'សូមបញ្ចូលឈ្មោះ',
        text: 'សូមបញ្ចូលឈ្មោះអ្នកដកស្តុក (Please enter Taken By name)',
        confirmButtonColor: '#2563eb'
      }).then(() => document.getElementById('inputTakenByName').focus());
      return;
    }

    // Validate quantities against stock
    for (const item of stockOutCart) {
      if (item.qty <= 0) {
        triggerHaptic('warning');
        Swal.fire({
          icon: 'warning',
          title: 'ចំនួនមិនត្រឹមត្រូវ',
          text: `ចំនួនដកសម្រាប់ ${item.name} ត្រូវតែធំជាង 0`,
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      if (item.qty > item.stock) {
        triggerHaptic('error');
        Swal.fire({
          icon: 'error',
          title: 'ស្តុកមិនគ្រប់គ្រាន់',
          text: `ចំនួនដកសម្រាប់ ${item.name} លើសពីស្តុកដែលមាន (${item.stock} ${item.unit})!`,
          confirmButtonColor: '#ef4444'
        });
        return;
      }
    }

    let note = '';
    if (selectedReason === 'Other') {
      note = document.getElementById('inputOtherNote').value.trim();
    }

    const reasonMap = {
      'Production': 'ប្រើប្រាស់ក្នុងការបោះពុម្ព (Printing)',
      'Machine maintenance': 'ថែទាំម៉ាស៊ីន (Maintenance)',
      'Cleaning': 'សម្អាត (Cleaning)',
      'Damaged': 'ខូចខាត (Damaged)',
      'Other': 'ផ្សេងៗ (Other)'
    };
    let reasonKh = reasonMap[selectedReason] || selectedReason;
    if (note && selectedReason === 'Other') reasonKh += ` (${note})`;
    else if (note && selectedReason !== 'Other') reasonKh += ` - ${note}`;

    const now = new Date();
    const monthsKh = ['មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
    const dateKhmer = `${now.getDate()} ${monthsKh[now.getMonth()]} ${now.getFullYear()}`;
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const timeStr = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
    const refCode = `SO-${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${Math.floor(Math.random()*9000)+1000}`;

    // Populate Modal Table
    const tbody = document.getElementById('mItemsTableBody');
    tbody.innerHTML = stockOutCart.map(it => `
      <tr>
        <td style="padding-left:0.75rem;" class="fw-semibold text-dark">${it.name}</td>
        <td class="text-center fw-bold text-danger">${it.qty} ${it.unit}</td>
        <td class="text-end fw-semibold text-success" style="padding-right:0.75rem;">${Math.max(0, it.stock - it.qty)} ${it.unit}</td>
      </tr>
    `).join('');

    const totalBadge = document.getElementById('mTotalItemsBadge');
    if (totalBadge) totalBadge.innerText = `${stockOutCart.length} មុខទំនិញ`;

    document.getElementById('mTakenBy').innerText = takenBy;
    document.getElementById('mDateTime').innerText = `${dateKhmer} (${timeStr})`;
    document.getElementById('mReason').innerText = reasonKh;
    document.getElementById('mRefCode').innerText = refCode;

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
  }

  async function submitStockOut() {
    const btn = document.getElementById('btnFinalConfirm');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងដំណើរការ...';

    const note = selectedReason === 'Other' ? document.getElementById('inputOtherNote').value.trim() : '';
    const takenBy = document.getElementById('inputTakenByName').value.trim() || 'Davy';

    saveTakenByName(takenBy);

    const tgInitData = window.Telegram?.WebApp?.initData || '';
    const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
    let recorderName = '';
    if (tgUser) {
      const full = `${tgUser.first_name || ''} ${tgUser.last_name || ''}`.trim();
      recorderName = full || (tgUser.username ? `@${tgUser.username}` : '');
      if (tgUser.username && full && !full.includes(tgUser.username)) {
        recorderName = `${full} (@${tgUser.username})`;
      }
    } else {
      @if(auth()->check())
        recorderName = "{{ auth()->user()->name }}";
      @endif
    }

    try {
      const res = await fetch('/api/telegram/app/stock-out', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          items: stockOutCart.map(it => ({
            material_id: it.id,
            quantity: it.qty
          })),
          reason: selectedReason,
          notes: note,
          performed_by: takenBy,
          recorder_name: recorderName,
          tg_init_data: tgInitData
        })
      });

      const contentType = res.headers.get('content-type');
      let data;
      if (contentType && contentType.includes('application/json')) {
        data = await res.json();
      } else {
        throw new Error(`Server error (${res.status})`);
      }

      if (data.ok) {
        triggerHaptic('success');
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        
        Swal.fire({
          icon: 'success',
          title: 'បានដកស្តុកជោគជ័យ!',
          text: data.message || 'Stock Out Completed Successfully',
          confirmButtonColor: '#2563eb',
          timer: 2500,
          showConfirmButton: true
        });

        // Update local stock display for all deducted items
        if (data.items && Array.isArray(data.items)) {
          data.items.forEach(it => {
            const card = document.querySelector(`.outline-item-card[data-id="${it.id}"]`);
            if (card) {
              card.dataset.stock = it.stock_remaining;
              const isOut = it.stock_remaining <= 0;
              if (isOut) {
                card.classList.add('out-of-stock-card');
                card.setAttribute('onclick', `handleCardClick(this, true)`);
              }
              const badge = card.querySelector('.outline-status-pill');
              if (badge) {
                const dot = isOut ? '⚫' : '🟢';
                const cls = isOut ? 'pill-empty-red' : 'pill-ok-green';
                badge.className = `outline-status-pill ${cls}`;
                badge.innerHTML = `<span>${dot}</span> <span>${it.stock_remaining + 0} ${card.dataset.unit}</span>`;
              }
            }
          });
        }

        clearCart();
      } else {
        triggerHaptic('error');
        Swal.fire({
          icon: 'error',
          title: 'មានបញ្ហា!',
          text: data.message || 'Stock Out failed',
          confirmButtonColor: '#ef4444'
        });
      }
    } catch (err) {
      triggerHaptic('error');
      Swal.fire({
        icon: 'error',
        title: 'មានបញ្ហា!',
        text: err.message,
        confirmButtonColor: '#ef4444'
      });
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> បញ្ជាក់ដកស្តុក';
    }
  }

  let currentHistoryFilter = 'out';

  function setHistoryFilter(type, btn) {
    triggerHaptic('selection');
    currentHistoryFilter = type;
    document.querySelectorAll('#historyFilterPills button').forEach(b => {
      b.classList.remove('btn-danger', 'btn-secondary', 'btn-primary', 'active-hf');
      if (b.id === 'hfPillOut')    b.classList.add('btn-outline-danger');
      else if (b.id === 'hfPillAll')    b.classList.add('btn-outline-secondary');
      else if (b.id === 'hfPillAdjust') b.classList.add('btn-outline-primary');
    });
    if (btn) {
      btn.classList.add('active-hf');
      if (btn.id === 'hfPillOut')    { btn.classList.remove('btn-outline-danger');    btn.classList.add('btn-danger'); }
      else if (btn.id === 'hfPillAll')    { btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-secondary'); }
      else if (btn.id === 'hfPillAdjust') { btn.classList.remove('btn-outline-primary');   btn.classList.add('btn-primary'); }
    }
    loadHistoryLog();
  }

  async function loadHistoryLog() {
    const container = document.getElementById('historyListContainer');
    container.innerHTML = '<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading...</div>';
    try {
      const url = currentHistoryFilter === 'all'
        ? '/api/telegram/app/data'
        : `/api/telegram/app/data?type=${currentHistoryFilter}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.ok && data.recent) {
        if (data.recent.length === 0) {
          container.innerHTML = '<div class="text-center text-muted py-5"><i class="fa-regular fa-folder-open fs-2 d-block mb-2 text-secondary"></i><div class="small">មិនទាន់មានប្រតិបត្តិការណ៍នៅឡើយទេ</div></div>';
          return;
        }
        let html = '<div class="list-group list-group-flush" style="font-size:0.875rem">';
        data.recent.forEach(r => {
          const resendBtn = r.can_resend
            ? `<button type="button" class="btn btn-sm rounded-2 fw-semibold mt-1"
                 style="font-size:0.72rem; padding:0.2rem 0.55rem; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;"
                 onclick="resendTelegramNotification(${r.id}, this)">
                 <i class="fa-solid fa-paper-plane me-1"></i>ផ្ញើម្តងទៀត
               </button>`
            : '';

          html += `
            <div class="list-group-item px-0 py-2 border-bottom">
              <div class="d-flex justify-content-between align-items-start">
                <div style="flex:1; min-width:0;">
                  <div class="fw-bold text-dark text-truncate">${r.material}</div>
                  <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                    <span class="badge border rounded-pill ${r.type_badge_class}" style="font-size:0.68rem; padding:0.2em 0.6em;">${r.type_label}</span>
                    <span class="text-muted" style="font-size:0.75rem;"><i class="fa-regular fa-circle-user me-0.5"></i>${r.performed_by}</span>
                  </div>
                  <div class="text-muted mt-0.5" style="font-size:0.75rem;">
                    <i class="fa-solid fa-bullseye me-1 text-primary" style="font-size:0.65rem;"></i>${r.reason}
                  </div>
                  ${resendBtn}
                </div>
                <div class="text-end ms-2" style="min-width:80px;">
                  <div class="fw-bold ${r.qty_class}" style="font-size:0.95rem;">${r.qty_display}</div>
                  <div class="text-muted" style="font-size:0.72rem; white-space:nowrap;">🕒 ${r.time}</div>
                  <div class="font-monospace text-muted" style="font-size:0.66rem; opacity:0.65;">${r.ref_code}</div>
                </div>
              </div>
            </div>
          `;
        });
        html += '</div>';
        container.innerHTML = html;
      }
    } catch (e) {
      container.innerHTML = '<div class="text-danger text-center py-3"><i class="fa-solid fa-triangle-exclamation me-1"></i>មិនអាចទាញយកប្រវត្តិបានទេ (Failed to load history)</div>';
    }
  }

  async function resendTelegramNotification(movementId, btn) {
    triggerHaptic('light');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
      const res = await fetch(`/api/telegram/app/resend/${movementId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });
      const data = await res.json();

      if (data.ok) {
        triggerHaptic('success');
        Swal.fire({
          icon: 'success',
          title: 'បានផ្ញើជោគជ័យ!',
          text: data.message || 'Telegram notification resent successfully.',
          confirmButtonColor: '#2563eb',
          timer: 2800,
          showConfirmButton: true
        });
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>បានផ្ញើ';
        btn.style.background = '#f0fdf4';
        btn.style.color = '#16a34a';
        btn.style.borderColor = '#bbf7d0';
        setTimeout(() => {
          btn.innerHTML = originalHtml;
          btn.style.cssText = '';
          btn.style.cssText = 'font-size:0.72rem; padding:0.2rem 0.55rem; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;';
          btn.disabled = false;
        }, 4000);
      } else {
        triggerHaptic('error');
        Swal.fire({
          icon: 'warning',
          title: 'មិនបានផ្ញើទេ',
          text: data.message || 'Failed to resend notification.',
          confirmButtonColor: '#ef4444'
        });
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    } catch (err) {
      triggerHaptic('error');
      Swal.fire({
        icon: 'error',
        title: 'មានបញ្ហា!',
        text: err.message,
        confirmButtonColor: '#ef4444'
      });
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    }
  }
</script>
@endsection
