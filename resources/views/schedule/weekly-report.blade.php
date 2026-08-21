@extends('layouts.app')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
@endpush

@section('title', 'របាយការណ៍កាលវិភាគសប្តាហ៍')
@section('page-title', 'របាយការណ៍កាលវិភាគ')

@section('content')

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">កាលវិភាគផលិតកម្មប្រចាំសប្តាហ៍</h1>
    <p class="section-sub">
      <i class="bi bi-calendar3 me-1"></i>
      <span class="latin">{{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</span>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#telegramModal">
      <i class="bi bi-telegram"></i> Send Telegram
    </button>
    <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> ត្រឡប់ទៅកាលវិភាគ
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-7">
    <div id="reportCaptureArea" style="background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
      {{-- Premium Header --}}
      <div style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); padding: 1.5rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <i class="bi bi-calendar2-week"></i> កាលវិភាគផលិតកម្មប្រចាំសប្តាហ៍
          </h2>
          <div style="font-size: 0.95rem; opacity: 0.9; margin-top: 0.25rem;">
            {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
          </div>
        </div>
        <div style="text-align: right;">
          <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8;">សរុប (Total)</div>
          <div style="font-size: 2.25rem; font-weight: 800; line-height: 1;">{{ $stats['total'] }}</div>
        </div>
      </div>

      {{-- Stats Row --}}
      <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
        <div style="flex: 1; padding: 1rem; text-align: center; border-right: 1px solid #e2e8f0;">
          <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Done ✅</div>
          <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">{{ $stats['done'] }}</div>
        </div>
        <div style="flex: 1; padding: 1rem; text-align: center; border-right: 1px solid #e2e8f0;">
          <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">In Progress ⏳</div>
          <div style="font-size: 1.5rem; font-weight: 700; color: #f59e0b;">{{ $stats['in_progress'] }}</div>
        </div>
        <div style="flex: 1; padding: 1rem; text-align: center; border-right: 1px solid #e2e8f0;">
          <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Delayed ⚠️</div>
          <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">{{ $stats['delayed'] }}</div>
        </div>
        <div style="flex: 1; padding: 1rem; text-align: center;">
          <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Progress %</div>
          <div style="font-size: 1.5rem; font-weight: 700; color: #6366f1;">{{ $stats['done_percent'] }}%</div>
        </div>
      </div>

      {{-- Schedule List --}}
      <div style="padding: 1.5rem;">
        @forelse($groupedEntries as $dateStr => $dayData)
          @if(!empty($dayData['processes']))
            <div style="margin-bottom: 2.5rem;">
              <h5 style="color: #4338ca; font-weight: 700; border-bottom: 2px solid #e0e7ff; padding-bottom: 0.75rem; margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-calendar-check"></i> {{ $dayData['label'] }}
              </h5>
              
              <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem; margin-top: -1px;">
                <thead>
                  <tr style="background-color: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; text-align: left;">
                    <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; width: 60px; text-align: center;">ស.ទ</th>
                    <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; width: 160px;">ដំណើរការ (Process)</th>
                    <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0; width: 35%;">ឈ្មោះសៀវភៅ (Book Name)</th>
                    <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0;">កម្រិត (Level / Details)</th>
                    <th style="padding: 1rem; border-bottom: 2px solid #e2e8f0;">ចំណាំ (Notes)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dayData['processes'] as $process => $entries)
                    @foreach($entries as $index => $entry)
                      @php
                        $parsed = \App\Http\Controllers\ScheduleController::parseTaskShortcuts($entry->task);
                      @endphp
                      <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.85rem 1rem; text-align: center; vertical-align: middle;">
                          @if($entry->status === 'done')
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.15rem;"></i>
                          @elseif($entry->status === 'in-progress')
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 1.15rem;"></i>
                          @elseif($entry->status === 'delayed')
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.15rem;"></i>
                          @else
                            <i class="bi bi-circle text-muted" style="font-size: 1.15rem;"></i>
                          @endif
                        </td>
                        
                        @if($index === 0)
                        <td rowspan="{{ count($entries) }}" style="padding: 0.85rem 1rem; font-weight: 700; color: #475569; vertical-align: middle; border-right: 1px dashed #e2e8f0; border-left: 1px dashed #e2e8f0; background: #fafafa;">
                          {{ $process }}
                        </td>
                        @endif
                        
                        <td style="padding: 0.85rem 1rem; vertical-align: middle; font-weight: 700; color: #1e293b;">
                          @if($parsed['bookName'])
                            <i class="bi bi-book text-primary me-2" style="opacity: 0.6;"></i>{{ $parsed['bookName'] }}
                          @else
                            <span class="text-muted" style="font-weight: 400;">(មិនមានឈ្មោះ / N/A)</span>
                          @endif
                        </td>
                        
                        <td style="padding: 0.85rem 1rem; vertical-align: middle; color: #334155;">
                          @if($parsed['rest'])
                            <span style="background-color: #f1f5f9; color: #475569; padding: 0.35rem 0.6rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid #e2e8f0; display: inline-block;">
                              {{ $parsed['rest'] }}
                            </span>
                          @else
                            -
                          @endif
                        </td>
                        
                        <td style="padding: 0.85rem 1rem; vertical-align: middle; color: #64748b; font-size: 0.85rem;">
                          {{ $entry->note ?: '-' }}
                        </td>
                      </tr>
                    @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        @empty
          <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
            <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
            <h5 style="font-weight: 600;">មិនមានការងារកំណត់ក្នុងកាលវិភាគសម្រាប់សប្តាហ៍នេះទេ</h5>
            <p>មិនមានទិន្នន័យ (No Data)</p>
          </div>
        @endforelse
      </div>
      
      {{-- Bottom Progress Bar --}}
      @if($stats['total'] > 0)
      <div style="padding: 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">
          <span>ស្ថានភាពជោគជ័យសរុប (Overall Progress)</span>
          <span style="color: #10b981;">{{ $stats['done_percent'] }}%</span>
        </div>
        <div style="background: #e2e8f0; height: 10px; border-radius: 10px; overflow: hidden;">
          <div style="background: linear-gradient(90deg, #10b981 0%, #34d399 100%); height: 100%; width: {{ $stats['done_percent'] }}%; transition: width 1s ease;"></div>
        </div>
      </div>
      @endif
    </div>
  </div>

  <div class="col-lg-5">
    {{-- Preview / Mobile text --}}
    <div class="card shadow-sm" style="border-radius: 12px; border: none; position: sticky; top: 20px;">
      <div class="card-header bg-light" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
        <h5 class="mb-0 text-secondary" style="font-weight: 600;"><i class="bi bi-eye me-2"></i>Text Preview</h5>
      </div>
      <div class="card-body">
        <div id="previewLoading" class="text-center py-4">
          <div class="spinner-border text-primary spinner-border-sm mb-2"></div>
          <div class="text-muted small">កំពុងទាញយក...</div>
        </div>
        <textarea id="previewContent" class="form-control" readonly style="font-family: monospace; font-size: 0.85rem; height: 500px; white-space: pre; border: 1px solid #e2e8f0; border-radius: 8px; display: none;"></textarea>
      </div>
      <div class="card-footer bg-white border-0 py-3" style="border-radius: 0 0 12px 12px;">
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="copyPreview()">
            <i class="bi bi-copy"></i> Copy Text
          </button>
          <button type="button" class="btn btn-outline-success btn-sm flex-grow-1" onclick="downloadReport()">
            <i class="bi bi-download"></i> Download .txt
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- TELEGRAM SEND MODAL --}}
<div class="modal fade" id="telegramModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
      <div class="modal-header" style="background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #a7f3d0; padding: 1.25rem 1.5rem;">
        <h5 class="modal-title" style="font-weight: 800; color: #065f46; letter-spacing: -0.01em;"><i class="bi bi-telegram text-success me-2" style="font-size: 1.2rem;"></i> ផ្ញើរបាយការណ៍ទៅ Telegram</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="telegramForm" action="{{ route('schedule.send-weekly-telegram') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.75rem;">
          <div class="alert alert-info" style="font-size: 0.9rem; border-radius: 10px;">
            <i class="bi bi-info-circle-fill me-1"></i> ប្រព័ន្ធនឹងផ្ញើរបាយការណ៍កាលវិភាគសម្រាប់សប្តាហ៍នេះទៅ Telegram Groups របស់អ្នក។
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight: 700; color: #334155;">ទម្រង់ផ្ញើ (Format)</label>
            <select name="send_format" id="sendFormat" class="form-select" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;">
              <option value="text">អត្ថបទ / Text (Compact with Link)</option>
              <option value="image">រូបភាព / Image (Screenshot)</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label" style="font-weight: 700; color: #334155;">ផ្ញើទៅកាន់ Group</label>
            <select name="group_id" id="groupIdSelect" class="form-select" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;" required>
              <option value="">(ជ្រើសរើស Group / Select Group)</option>
              @foreach($telegramGroups as $group)
                <option value="{{ $group->chat_id }}">{{ $group->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.05); padding: 1.25rem 1.5rem;">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600; padding: 0.5rem 1rem;">បោះបង់</button>
          <button type="submit" class="btn btn-success" id="btnSendTelegram" style="border-radius: 8px; font-weight: 600; padding: 0.5rem 1.25rem; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2), 0 2px 4px -1px rgba(16, 185, 129, 0.1);">
            <i class="bi bi-send-fill me-2"></i> ផ្ញើឥឡូវនេះ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPreview();

    const telegramForm = document.getElementById('telegramForm');
    telegramForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const groupId = document.getElementById('groupIdSelect').value;
        if (!groupId) {
            if (typeof showToast === 'function') showToast('warning', 'សូមជ្រើសរើស Group ជាមុនសិន។');
            return;
        }

        const btn = document.getElementById('btnSendTelegram');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងផ្ញើ...';
        btn.disabled = true;

        const format = document.getElementById('sendFormat').value;
        if (format === 'image') {
            sendAsImage(btn, originalText, groupId);
            return;
        }

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('success', data.message);
                } else {
                    alert(data.message);
                }
                bootstrap.Modal.getInstance(document.getElementById('telegramModal')).hide();
            } else {
                if (typeof showToast === 'function') {
                    showToast('error', data.message || 'មានបញ្ហាក្នុងការផ្ញើ។');
                } else {
                    alert(data.message || 'មានបញ្ហាក្នុងការផ្ញើ។');
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof showToast === 'function') {
                showToast('error', 'មានបញ្ហាភ្ជាប់ទៅកាន់ម៉ាស៊ីនមេ។');
            } else {
                alert('មានបញ្ហាភ្ជាប់ទៅកាន់ម៉ាស៊ីនមេ។');
            }
        })
        .finally(() => {
            if (typeof showLoading === 'function') showLoading(false);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
});

async function sendAsImage(btn, originalText, chatId) {
    const reportEl = document.getElementById('reportCaptureArea');
    if (typeof showLoading === 'function') showLoading(true, 'កំពុងបង្កើតរូបភាព...');
    
    try {
        // Temporarily style for capture
        const oldBg = reportEl.style.backgroundColor;
        const oldPadding = reportEl.style.padding;
        const oldWidth = reportEl.style.width;
        const oldMaxWidth = reportEl.style.maxWidth;
        
        reportEl.style.backgroundColor = '#f8fafc';
        reportEl.style.padding = '15px';
        // Force an optimal width (850px) for mobile phone readability instead of an ultra-wide 1200px
        reportEl.style.width = '850px';
        reportEl.style.maxWidth = '850px';
        
        await new Promise(r => setTimeout(r, 200)); // wait for styles to apply
        
        const canvas = await html2canvas(reportEl, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#f8fafc'
        });
        
        reportEl.style.backgroundColor = oldBg;
        reportEl.style.padding = oldPadding;
        reportEl.style.width = oldWidth;
        reportEl.style.maxWidth = oldMaxWidth;

        await new Promise((resolve, reject) => {
            canvas.toBlob(async blob => {
                if (!blob) { reject(new Error('Capture failed')); return; }
                
                const fd = new FormData();
                fd.append('chat_id', chatId);
                fd.append('photo', blob, 'schedule-report.png');
                fd.append('caption', '📅 កាលវិភាគផលិតកម្មប្រចាំសប្តាហ៍');
                fd.append('_token', document.querySelector('input[name="_token"]').value);
                
                const res = await fetch('{{ route("telegram.send.image") }}', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (res.ok && data.ok) {
                    resolve();
                } else {
                    reject(new Error(data.message || 'Server error'));
                }
            }, 'image/png');
        });
        
        if (typeof showToast === 'function') showToast('success', 'ផ្ញើរបាយការណ៍រូបភាពទៅ Telegram បានដោយជោគជ័យ 🎉');
        bootstrap.Modal.getInstance(document.getElementById('telegramModal')).hide();
    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') showToast('error', 'ផ្ញើមិនបាន: ' + err.message);
    } finally {
        if (typeof showLoading === 'function') showLoading(false);
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function loadPreview() {
    const previewContent = document.getElementById('previewContent');
    const loading = document.getElementById('previewLoading');
    
    loading.style.display = 'block';
    previewContent.style.display = 'none';

    fetch(`{{ route('schedule.weekly-report.json') }}`)
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            previewContent.value = data.report;
            previewContent.style.display = 'block';
        } else {
            previewContent.value = 'មានបញ្ហាក្នុងការទាញយក។';
            previewContent.style.display = 'block';
        }
    })
    .catch(err => {
        console.error(err);
        previewContent.value = 'មានបញ្ហាភ្ជាប់។';
        previewContent.style.display = 'block';
    })
    .finally(() => {
        loading.style.display = 'none';
    });
}

function copyPreview() {
    const el = document.getElementById('previewContent');
    el.select();
    el.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        if (typeof showToast === 'function') {
            showToast('success', 'បានចម្លងរួចរាល់ / Copied');
        } else {
            alert('Copied');
        }
    } catch(err) {
        if (typeof showToast === 'function') {
            showToast('error', 'មិនអាចចម្លងបានទេ');
        } else {
            alert('Failed to copy');
        }
    }
}

function downloadReport() {
    const text = document.getElementById('previewContent').value;
    if (!text || text.trim() === '') {
        if (typeof showToast === 'function') {
            showToast('error', 'គ្មានទិន្នន័យសម្រាប់ទាញយកទេ');
        } else {
            alert('No data to download');
        }
        return;
    }
    
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'schedule-weekly-report-' + new Date().toISOString().split('T')[0] + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

@endsection
