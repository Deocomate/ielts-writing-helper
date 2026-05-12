<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Phòng thi — {{ $lesson->title }} — IELTS Type & Learn</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'app-bg':'#F9F9FA','surface':'#FFFFFF','border-light':'#E1E4E8',
            'text-primary':'#0E101A','text-secondary':'#6D758D','text-disabled':'#B9BDC5',
            'brand':'#11A683','brand-dark':'#0E8A6D','brand-light':'#E8F8F3',
            'semantic-red':'#FF5E5E','semantic-blue':'#007AFF','semantic-green':'#11A683',
            'semantic-purple':'#8F00FF','semantic-yellow':'#FFD500',
          },
          fontFamily: {
            sans: ['Inter','-apple-system','BlinkMacSystemFont','Segoe UI','Roboto','sans-serif'],
            mono: ['JetBrains Mono','Fira Code','Consolas','monospace'],
          },
          boxShadow: {
            'card':'0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03)',
            'float':'0 4px 6px rgba(0,0,0,0.05), 0 10px 15px rgba(0,0,0,0.1)',
          },
        },
      },
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <style>
    html{scroll-behavior:smooth;}
    body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#0E101A;-webkit-font-smoothing:antialiased;}
    #essayInput{resize:none;}#essayInput:focus{outline:none;}
    .timer-warn{animation:pulse 1s ease-in-out infinite;}
    @keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.6;}}
    .modal-overlay{display:none;}.modal-overlay.show{display:flex;}
    @media(prefers-reduced-motion:reduce){.timer-warn{animation:none;}}
  </style>
</head>
<body class="h-screen flex flex-col overflow-hidden bg-white">

  @php
    $timeMinutes = (int) request()->query('time', 40);
    $minWords = $lesson->task_type === 'task_1' ? 150 : 250;
    $taskLabel = $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2';
  @endphp

  {{-- Exam top bar --}}
  <header class="border-b border-border-light flex-shrink-0">
    <div class="px-4 sm:px-6 flex items-center justify-between h-14">
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </div>
        <div>
          <p class="text-xs text-text-disabled">IELTS Writing {{ $taskLabel }}</p>
          <p class="text-sm font-semibold text-text-primary">{{ $lesson->title }}</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <div class="text-center">
          <p class="text-base font-bold text-text-primary" id="wordCount">0</p>
          <p class="text-xs text-text-disabled">Từ</p>
        </div>
        <div id="timerBlock" class="flex items-center gap-1.5 px-3 py-1.5 bg-app-bg border border-border-light rounded-xl">
          <svg class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span id="timerDisplay" class="font-mono font-bold text-text-primary text-sm">{{ str_pad($timeMinutes, 2, '0', STR_PAD_LEFT) }}:00</span>
        </div>
        <button onclick="openSubmitModal()" class="px-4 py-2 bg-brand text-white text-sm font-bold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer">Nộp bài</button>
      </div>
    </div>
  </header>

  {{-- Split-screen content --}}
  <div class="flex flex-1 overflow-hidden">
    {{-- Left: Prompt --}}
    <div class="w-2/5 xl:w-1/3 border-r border-border-light overflow-y-auto flex-shrink-0 bg-white">
      <div class="p-6">
        <div class="flex items-center gap-2 mb-4">
          <span class="px-2 py-0.5 bg-brand-light text-brand text-xs font-semibold rounded">{{ $taskLabel }}</span>
          <span class="px-2 py-0.5 bg-app-bg text-text-secondary text-xs rounded">{{ $timeMinutes }} phút • {{ $minWords }}+ từ</span>
        </div>
        <div class="text-sm text-text-primary mb-4 leading-relaxed whitespace-pre-line">{{ $lesson->prompt_text }}</div>

        @if($lesson->image_path)
          <div class="w-full mb-4">
            <img src="{{ asset('storage/' . $lesson->image_path) }}" alt="Chart" class="w-full rounded-xl border border-border-light" />
          </div>
        @endif

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3">
          <p class="text-xs text-yellow-800">
            <svg class="w-3.5 h-3.5 inline-block text-yellow-600 mr-1 -mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            Viết ít nhất <strong>{{ $minWords }} từ</strong>. Bài dưới {{ $minWords }} từ sẽ bị trừ điểm.
          </p>
        </div>
      </div>
    </div>

    {{-- Right: Essay editor --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-white">
      <div class="px-6 pt-5 flex items-center justify-between border-b border-border-light pb-3">
        <p class="text-sm font-semibold text-text-primary">Bài viết của bạn</p>
        <span id="wordHint" class="text-xs text-text-disabled">0 / {{ $minWords }}+ từ</span>
      </div>
      <textarea
        id="essayInput"
        class="flex-1 w-full px-6 pt-4 pb-6 text-sm leading-8 text-text-primary border-0 focus:ring-0"
        placeholder="Bắt đầu viết bài luận của bạn tại đây..."
        spellcheck="true"
      ></textarea>
      <div class="px-6 py-3 border-t border-border-light flex items-center gap-3">
        <div class="flex-1 bg-app-bg rounded-full h-1.5">
          <div id="wordCountBar" class="h-full bg-brand/40 rounded-full transition-all" style="width:0%"></div>
        </div>
        <span class="text-xs text-text-disabled">Tối thiểu {{ $minWords }} từ</span>
      </div>
    </div>
  </div>

  {{-- Submit confirm modal --}}
  <div id="submitModal" class="modal-overlay fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-float p-6">
      <div class="text-center mb-5">
        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="font-bold text-text-primary">Nộp bài?</h2>
        <p class="text-sm text-text-secondary mt-1">Bước này không thể hoàn tác. Bài sẽ được chấm bằng AI.</p>
      </div>
      <div class="flex gap-2">
        <button onclick="closeSubmitModal()" class="flex-1 py-2.5 border border-border-light text-sm font-medium text-text-secondary rounded-xl hover:bg-app-bg transition-colors cursor-pointer">Huỷ</button>
        <button onclick="submitExam()" class="flex-1 py-2.5 bg-brand text-white text-sm font-bold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer">Xác nhận nộp</button>
      </div>
    </div>
  </div>

  {{-- Hidden form for submission --}}
  <form id="examForm" method="POST" action="{{ route('client.learning.mock-exam.submit') }}" class="hidden">
    @csrf
    <input type="hidden" name="lesson_id" value="{{ $lesson->id }}" />
    <input type="hidden" name="user_essay" id="formEssay" />
    <input type="hidden" name="time_taken_seconds" id="formTimeTaken" />
  </form>

  <script>
    const essay = document.getElementById('essayInput');
    const wordCountEl = document.getElementById('wordCount');
    const wordHint = document.getElementById('wordHint');
    const wordCountBar = document.getElementById('wordCountBar');
    const timerDisplay = document.getElementById('timerDisplay');
    const timerBlock = document.getElementById('timerBlock');
    const MIN_WORDS = {{ $minWords }};
    const TOTAL_TIME = {{ $timeMinutes }} * 60;
    const storageKey = 'exam_draft_{{ $lesson->id }}';
    let totalSeconds = TOTAL_TIME;
    let interval;

    function updateTimerDisplay() {
      const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
      const s = (totalSeconds % 60).toString().padStart(2, '0');
      timerDisplay.textContent = m + ':' + s;
      if (totalSeconds <= 300) timerBlock.classList.add('timer-warn');
    }

    function loadDraft() {
      try {
        const rawDraft = localStorage.getItem(storageKey);
        if (!rawDraft) {
          return;
        }

        const parsedDraft = JSON.parse(rawDraft);
        if (typeof parsedDraft.essay === 'string') {
          essay.value = parsedDraft.essay;
          essay.dispatchEvent(new Event('input'));
        }

        if (Number.isInteger(parsedDraft.remaining_seconds)
          && parsedDraft.remaining_seconds > 0
          && parsedDraft.remaining_seconds <= TOTAL_TIME) {
          totalSeconds = parsedDraft.remaining_seconds;
        }
      } catch (error) {
        console.error('Không thể khôi phục bản nháp bài thi', error);
      }
    }

    function saveDraft() {
      try {
        localStorage.setItem(storageKey, JSON.stringify({
          essay: essay.value,
          remaining_seconds: totalSeconds,
        }));
      } catch (error) {
        console.error('Không thể lưu bản nháp bài thi', error);
      }
    }

    loadDraft();
    updateTimerDisplay();

    function startTimer() {
      interval = setInterval(() => {
        totalSeconds--;
        if (totalSeconds <= 0) {
          clearInterval(interval);
          submitExam();
          return;
        }
        updateTimerDisplay();
        saveDraft();
      }, 1000);
    }
    startTimer();

    essay.addEventListener('input', function () {
      const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
      const wc = words.length;
      wordCountEl.textContent = wc;
      wordHint.textContent = wc + ' / ' + MIN_WORDS + '+ từ';
      wordHint.className = wc >= MIN_WORDS ? 'text-xs text-brand font-semibold' : 'text-xs text-text-disabled';
      wordCountBar.style.width = Math.min(100, Math.round(wc / MIN_WORDS * 100)) + '%';
      wordCountBar.className = wc >= MIN_WORDS
        ? 'h-full bg-brand rounded-full transition-all'
        : 'h-full bg-brand/40 rounded-full transition-all';

      saveDraft();
    });

    function openSubmitModal() { document.getElementById('submitModal').classList.add('show'); }
    function closeSubmitModal() { document.getElementById('submitModal').classList.remove('show'); }
    document.getElementById('submitModal').addEventListener('click', function (e) {
      if (e.target === this) closeSubmitModal();
    });

    function submitExam() {
      clearInterval(interval);
      const timeTaken = TOTAL_TIME - totalSeconds;
      localStorage.removeItem(storageKey);
      document.getElementById('formEssay').value = essay.value;
      document.getElementById('formTimeTaken').value = timeTaken;
      document.getElementById('examForm').submit();
    }
  </script>
  <x-client.layout.ai-chat-widget />
</body>
</html>
