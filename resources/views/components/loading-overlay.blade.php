{{-- Loading Overlay with Alpine.js --}}
<div 
    x-data="{ show: false }" 
    x-show="show"
    x-on:loading-start.window="show = true"
    x-on:loading-stop.window="show = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    class="loading-overlay-alpine"
>
    <div class="lo-card" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    >
        <div class="spinner"></div>
        <p>{{ $message ?? 'កំពុងដំណើរការ...' }}</p>
    </div>
</div>

<style>
.loading-overlay-alpine {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.45);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.1rem;
    padding: 2rem 2.5rem;
    background: rgba(255,255,255,.98);
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(15,23,42,.35);
}

.spinner {
    position: relative;
    width: 56px;
    height: 56px;
}

.spinner::before, .spinner::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 4px solid transparent;
}

.spinner::before {
    border-top-color: var(--primary, #4f46e5);
    border-right-color: var(--primary, #4f46e5);
    animation: spin .8s linear infinite;
}

.spinner::after {
    border-bottom-color: #c7d2fe;
    border-left-color: #c7d2fe;
    animation: spin 1.2s linear infinite reverse;
}

@keyframes spin { 
    to { transform: rotate(360deg); } 
}
</style>