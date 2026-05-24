<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chép chính tả — {{ $lesson->title }} — IELTS Type & Learn</title>
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
            'tooltip':'0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08)',
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
    body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#0E101A;background:#F9F9FA;-webkit-font-smoothing:antialiased;}
    #typing-engine{position:relative;font-family:'JetBrains Mono','Fira Code',monospace;font-size:15px;line-height:2;letter-spacing:0.01em;cursor:text;min-height:300px;user-select:none;-webkit-user-select:none;}
    #typing-engine:focus{outline:none;}
    .char-ghost{color:#D1D5DB;transition:color 0.05s ease;}
    .char-correct{color:#11A683;animation:char-correct-flash 0.4s ease forwards;}
    .char-wrong{color:#FF5E5E;background:rgba(255,94,94,0.1);border-bottom:2px solid rgba(255,94,94,0.6);border-radius:2px;animation:char-wrong-flash 0.3s ease;}
    .char-cursor{position:relative;}
    .char-cursor::before{content:'';position:absolute;left:-1px;top:2px;width:2px;height:1.35em;background-color:#11A683;animation:cursor-blink 1s step-end infinite;border-radius:1px;}
    @keyframes cursor-blink{0%,100%{opacity:1;}50%{opacity:0;}}
    @keyframes char-correct-flash{0%{background:rgba(17,166,131,0.25);color:#11A683;}100%{background:transparent;color:#11A683;}}
    @keyframes char-wrong-flash{0%{background:rgba(255,94,94,0.35);}100%{background:rgba(255,94,94,0.1);}}
    .streak-active .char-cursor::before{background-color:#FFD500;box-shadow:0 0 10px rgba(255,213,0,0.6),0 0 20px rgba(255,213,0,0.2);}
    @keyframes milestone-flash{0%{opacity:0;transform:translateX(-50%) translateY(10px) scale(0.9);}15%{opacity:1;transform:translateX(-50%) translateY(0) scale(1.02);}85%{opacity:1;transform:translateX(-50%) translateY(0) scale(1);}100%{opacity:0;transform:translateX(-50%) translateY(-8px) scale(0.95);}}
    .milestone-toast{animation:milestone-flash 1.8s cubic-bezier(0.4,0,0.2,1) forwards;}
    @keyframes streak-pop{0%{transform:scale(0.5);opacity:0;}60%{transform:scale(1.15);opacity:1;}100%{transform:scale(1);opacity:1;}}
    .streak-badge{animation:streak-pop 0.25s cubic-bezier(0.34,1.56,0.64,1);}
    .annotation-tooltip{position:absolute;z-index:50;opacity:0;transform:translateY(8px);transition:opacity 0.25s ease,transform 0.25s ease;pointer-events:none;}
    .annotation-tooltip.visible{opacity:1;transform:translateY(0);pointer-events:auto;}
    .annotation-tooltip .tooltip-arrow{position:absolute;top:-6px;left:24px;width:12px;height:6px;overflow:hidden;}
    .annotation-tooltip .tooltip-arrow::before{content:'';position:absolute;top:3px;left:1px;width:10px;height:10px;background:white;border:1px solid #E1E4E8;transform:rotate(45deg);}
    .progress-bar-fill{transition:width 0.4s cubic-bezier(0.4,0,0.2,1);}
    .modal-overlay{display:none;opacity:0;transition:opacity 0.3s ease;}
    .modal-overlay.show{display:flex;opacity:1;}
    .modal-content{transform:scale(0.95) translateY(10px);transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);}
    .modal-overlay.show .modal-content{transform:scale(1) translateY(0);}
    ::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:#D1D5DB;border-radius:3px;}::-webkit-scrollbar-thumb:hover{background:#9CA3AF;}
    @media(prefers-reduced-motion:reduce){.char-cursor::before{animation:none;opacity:1;}.char-correct,.char-wrong{animation:none;}.annotation-tooltip{transition:none;}.progress-bar-fill{transition:none;}.modal-overlay,.modal-content{transition:none;}.milestone-toast{animation:none;}.streak-badge{animation:none;}}
  </style>
</head>
<body class="bg-app-bg min-h-screen flex flex-col">

  {{-- TOP BAR --}}
  <header class="bg-white border-b border-border-light flex-shrink-0 sticky top-0 z-40" style="backdrop-filter:blur(12px);background:rgba(255,255,255,0.92);">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14 gap-4">
      <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('client.lessons.library') }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-text-disabled hover:text-text-primary hover:bg-app-bg transition-all" title="Quay lại thư viện">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-light text-brand text-[10px] font-bold uppercase tracking-wider rounded-full">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              Chép chính tả
            </span>
            <span class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-semantic-blue text-[10px] font-semibold rounded-full">{{ $lesson->task_type }}</span>
          </div>
          <p class="text-sm font-bold text-text-primary leading-tight truncate mt-0.5" id="lessonTitle">{{ $lesson->title }}</p>
        </div>
      </div>
      <div class="hidden sm:flex items-center gap-1">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-app-bg transition-colors">
          <svg class="w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
          <div class="text-center"><p class="text-base font-black text-text-primary leading-none" id="wpmDisplay">0</p><p class="text-[10px] text-text-disabled leading-tight mt-0.5">WPM</p></div>
        </div>
        <div class="w-px h-6 bg-border-light"></div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-app-bg transition-colors">
          <svg class="w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div class="text-center"><p class="text-base font-black text-brand leading-none" id="accDisplay">100%</p><p class="text-[10px] text-text-disabled leading-tight mt-0.5">Chính xác</p></div>
        </div>
        <div class="w-px h-6 bg-border-light"></div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-app-bg transition-colors">
          <svg class="w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
          <div class="text-center"><p class="text-base font-black text-text-primary leading-none" id="progressDisplay">0%</p><p class="text-[10px] text-text-disabled leading-tight mt-0.5">Tiến độ</p></div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="resetSession()" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-border-light text-xs font-medium text-text-secondary rounded-lg hover:bg-app-bg hover:border-brand hover:text-brand transition-all cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
          Đặt lại
        </button>
      </div>
    </div>
    <div class="h-1 bg-app-bg"><div class="progress-bar-fill h-full bg-gradient-to-r from-brand to-emerald-400 rounded-r-full" id="progressBar" style="width:0%"></div></div>
  </header>

  {{-- MAIN --}}
  <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col gap-5">
    {{-- Prompt --}}
    <div class="bg-white rounded-2xl border border-border-light shadow-card p-5 sm:p-6">
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center mt-0.5">
          <svg class="w-4 h-4 text-semantic-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
          <h2 class="text-xs font-bold text-semantic-blue uppercase tracking-wider mb-1">Đề bài — {{ $lesson->task_type }}</h2>
          <p class="text-sm text-text-secondary leading-relaxed whitespace-pre-line" id="promptText">{{ $lesson->prompt_text }}</p>
          @if($lesson->image_path)
            <div class="mt-4 mb-2 rounded-xl border border-border-light overflow-hidden bg-white p-2">
              <img src="{{ Storage::disk('public')->url($lesson->image_path) }}" alt="Diagram/Map" class="w-full max-h-[400px] object-contain mx-auto" />
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Typing Engine Card --}}
    <div class="bg-white rounded-2xl border border-border-light shadow-card flex-1 flex flex-col overflow-hidden">
      <div class="flex items-center justify-between px-5 sm:px-6 py-3 border-b border-border-light bg-app-bg/50">
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-1.5" aria-hidden="true">
            <span class="w-2.5 h-2.5 rounded-full bg-semantic-red/50"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-semantic-yellow/50"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-brand/50"></span>
          </div>
          <span class="text-xs font-medium text-text-disabled">Dictation Mode — Gõ đè lên chữ mờ</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs text-text-disabled"><span id="charCount">0</span> / <span id="totalChars">0</span> ký tự</span>
          <div class="flex sm:hidden items-center gap-2 text-xs">
            <span class="font-bold text-text-primary"><span id="wpmMobile">0</span> WPM</span>
            <span class="font-bold text-brand"><span id="accMobile">100</span>%</span>
          </div>
        </div>
      </div>
      <div class="flex-1 px-5 sm:px-8 py-6 sm:py-8 overflow-y-auto relative" id="typing-wrapper">
        <div id="typing-engine" tabindex="0" autofocus></div>
        <div id="annotation-tooltip" class="annotation-tooltip">
          <div class="tooltip-arrow"></div>
          <div class="bg-white rounded-xl shadow-tooltip border border-border-light p-4 w-72 sm:w-80">
            <div class="flex items-center gap-2 mb-2">
              <span class="w-2.5 h-2.5 rounded-full" id="tooltip-dot"></span>
              <span class="text-[11px] font-bold uppercase tracking-wider" id="tooltip-category"></span>
            </div>
            <p class="text-sm text-text-primary leading-relaxed" id="tooltip-content"></p>
            <p class="text-xs text-text-secondary mt-2" id="tooltip-meaning"></p>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between px-5 sm:px-6 py-3 border-t border-border-light bg-app-bg/50">
        <div class="flex items-center gap-4">
          <span class="flex items-center gap-1.5 text-xs text-text-secondary"><span class="w-2 h-2 rounded-full bg-brand"></span> Đúng</span>
          <span class="flex items-center gap-1.5 text-xs text-text-secondary"><span class="w-2 h-2 rounded-full bg-semantic-red"></span> Sai</span>
          <span class="flex items-center gap-1.5 text-xs text-text-secondary"><span class="w-2 h-2 rounded-full bg-text-disabled"></span> Chưa gõ</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-[11px] text-text-disabled hidden sm:inline">Gõ sai sẽ hiện <span class="text-semantic-red font-medium">đỏ</span> — dùng <kbd class="px-1.5 py-0.5 bg-white border border-border-light rounded text-[10px] font-mono">Backspace</kbd> để quay lại sửa</span>
          <span id="streakBadge" class="hidden items-center gap-1 px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-600 text-[10px] font-bold rounded-full streak-badge">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <span id="streakCount">0</span> streak
          </span>
        </div>
      </div>
    </div>
  </main>

  {{-- COMPLETION MODAL --}}
  <div id="completionModal" class="modal-overlay fixed inset-0 z-50 bg-black/40 items-center justify-center p-4">
    <div class="modal-content bg-white rounded-2xl w-full max-w-md shadow-float overflow-hidden">
      <div class="relative bg-gradient-to-br from-brand to-emerald-500 px-6 py-8 text-center overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
        </div>
        <div class="relative">
          <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <h2 class="text-xl font-bold text-white">Tuyệt vời! Hoàn thành!</h2>
          <p class="text-sm text-white/80 mt-1">Bài chép chính tả đã được ghi lại</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-3 gap-4 border-b border-border-light">
        <div class="text-center"><p class="text-2xl font-black text-text-primary" id="modalWpm">0</p><p class="text-xs text-text-disabled mt-0.5">WPM</p></div>
        <div class="text-center"><p class="text-2xl font-black text-brand" id="modalAcc">100%</p><p class="text-xs text-text-disabled mt-0.5">Chính xác</p></div>
        <div class="text-center"><p class="text-2xl font-black text-text-primary" id="modalTime">0:00</p><p class="text-xs text-text-disabled mt-0.5">Thời gian</p></div>
      </div>
      <div class="px-6 py-5 space-y-2.5">
        <p class="text-xs text-text-secondary text-center mb-3">Hãy xem phân tích chi tiết bài mẫu này</p>
        <a href="{{ route('client.learning.analyze', $lesson->id) }}" class="flex items-center justify-center gap-2 w-full py-3 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
          Phân tích bài mẫu — Band {{ $lesson->band_score }}
        </a>
        <button onclick="resetSession()" class="w-full py-2.5 border border-border-light text-sm font-medium text-text-secondary rounded-xl hover:bg-app-bg hover:border-brand hover:text-brand transition-all cursor-pointer">Chép lại từ đầu</button>
      </div>
    </div>
  </div>

  @php
    $dictationAnnotations = $lesson->annotations
      ->map(function ($annotation) {
        return [
          'text' => $annotation->highlighted_text,
          'type' => $annotation->tag_type,
          'category' => $annotation->tag_label,
          'content' => $annotation->tooltip_content,
          'meaning' => $annotation->tooltip_meaning ?? '',
          'color' => match ($annotation->tag_type) {
            'vocabulary' => '#11A683',
            'grammar' => '#FF5E5E',
            'coherence' => '#007AFF',
            'logic', 'gra' => '#8F00FF',
            default => '#11A683',
          },
        ];
      })
      ->values();
  @endphp

  <script>
    const SOURCE_TEXT = @json($lesson->sample_essay);
    const ANNOTATIONS = @json($dictationAnnotations);
    const LESSON_ID = {{ $lesson->id }};
    const SAVE_URL = @json(route('client.learning.dictation.save'));

    const CHAR_EQUIVALENCE = {
      '\u2080':'0','\u2081':'1','\u2082':'2','\u2083':'3','\u2084':'4','\u2085':'5','\u2086':'6','\u2087':'7','\u2088':'8','\u2089':'9',
      '\u2070':'0','\u00B9':'1','\u00B2':'2','\u00B3':'3','\u2074':'4','\u2075':'5','\u2076':'6','\u2077':'7','\u2078':'8','\u2079':'9',
      '\u2014':'-','\u2013':'-','\u2018':"'",'\u2019':"'",'\u201C':'"','\u201D':'"','\u2026':'.',
    };

    function matchesExpected(typed, expected) {
      if (typed === expected) return true;
      const equiv = CHAR_EQUIVALENCE[expected];
      return equiv !== undefined && typed === equiv;
    }

    let cursorPos=0, totalCorrect=0, totalKeystrokes=0, startTime=null, isCompleted=false;
    let activeTooltipTimeout=null, annotationMap=[], charStates=[], streak=0, shownAnnotations=new Set(), lastMilestone=0;

    function init() { buildAnnotationMap(); renderChars(); bindEvents(); document.getElementById('totalChars').textContent=SOURCE_TEXT.length; setTimeout(()=>document.getElementById('typing-engine').focus(),100); }

    function buildAnnotationMap() {
      annotationMap = [];
      let searchStart = 0;

      for (const ann of ANNOTATIONS) {
        if (!ann.text) {
          continue;
        }

        let idx = SOURCE_TEXT.indexOf(ann.text, searchStart);

        // Fallback for out-of-order annotations: still try global search.
        if (idx === -1) {
          idx = SOURCE_TEXT.indexOf(ann.text);
        }

        if (idx !== -1) {
          annotationMap.push({
            startIdx: idx,
            endIdx: idx + ann.text.length,
            annotation: ann,
          });
          searchStart = idx + ann.text.length;
        }
      }

      annotationMap.sort((a, b) => a.startIdx - b.startIdx);
    }
    function getAnnotationEndingAt(ci){for(const a of annotationMap){if(a.endIdx===ci)return a;}return null;}

    function renderChars() {
      const engine=document.getElementById('typing-engine');let html='';
      for(let i=0;i<SOURCE_TEXT.length;i++){
        const ch=SOURCE_TEXT[i];let css='char-ghost',extra='';
        if(i<cursorPos)css=charStates[i]==='wrong'?'char-wrong':'char-correct';
        else if(i===cursorPos)extra=' char-cursor';
        if(ch==='\n'){html+=i===cursorPos?'<span class="char-ghost'+extra+'" data-idx="'+i+'">\u21B5</span><br>':i<cursorPos?'<br data-idx="'+i+'">':'<span class="char-ghost" data-idx="'+i+'">\u21B5</span><br>';continue;}
        html+='<span class="'+css+extra+'" data-idx="'+i+'">'+escapeHtml(ch)+'</span>';
      }
      engine.innerHTML=html;
      const cur=engine.querySelector('.char-cursor');if(cur)cur.scrollIntoView({block:'nearest',behavior:'smooth'});
    }

    function markCharSpan(idx,state){
      charStates[idx]=state;const span=document.getElementById('typing-engine').querySelector('[data-idx="'+idx+'"]');
      if(!span)return;span.classList.remove('char-correct','char-wrong','char-ghost','char-cursor');
      if(state==='correct')span.classList.add('char-correct');else if(state==='wrong')span.classList.add('char-wrong');else span.classList.add('char-ghost');
    }
    function moveCursor(ni){
      const engine=document.getElementById('typing-engine'),old=engine.querySelector('.char-cursor');if(old)old.classList.remove('char-cursor');
      if(ni<SOURCE_TEXT.length){const span=engine.querySelector('[data-idx="'+ni+'"]');if(span){span.classList.add('char-cursor');span.scrollIntoView({block:'nearest',behavior:'smooth'});}}
    }

    function handleKeydown(e){
      if(isCompleted)return;const key=e.key;
      if(!startTime&&key.length===1)startTime=Date.now();
      if(key==='Backspace'){e.preventDefault();if(cursorPos>0){const engine=document.getElementById('typing-engine'),oc=engine.querySelector('.char-cursor');if(oc)oc.classList.remove('char-cursor');if(charStates[cursorPos])markCharSpan(cursorPos,null);cursorPos--;charStates[cursorPos]=null;markCharSpan(cursorPos,null);moveCursor(cursorPos);streak=0;updateStreakUI();updateStats();}return;}
      if(key==='Enter'||(key.length===1&&!e.ctrlKey&&!e.metaKey&&!e.altKey)){
        e.preventDefault();if(cursorPos>=SOURCE_TEXT.length)return;
        const expected=SOURCE_TEXT[cursorPos],typed=key==='Enter'?'\n':key;totalKeystrokes++;
        if(matchesExpected(typed,expected)){totalCorrect++;markCharSpan(cursorPos,'correct');streak++;updateStreakUI();}
        else{markCharSpan(cursorPos,'wrong');streak=0;updateStreakUI();}
        cursorPos++;
        const ended=getAnnotationEndingAt(cursorPos);if(ended&&!shownAnnotations.has(ended.startIdx)){shownAnnotations.add(ended.startIdx);showAnnotationTooltip(ended);}
        checkMilestone();
        if(cursorPos<SOURCE_TEXT.length)moveCursor(cursorPos);else{const o=document.getElementById('typing-engine').querySelector('.char-cursor');if(o)o.classList.remove('char-cursor');}
        if(cursorPos>=SOURCE_TEXT.length)completeSession();updateStats();
      }
    }

    function showAnnotationTooltip(annData){
      const ann=annData.annotation,tooltip=document.getElementById('annotation-tooltip');
      document.getElementById('tooltip-dot').style.backgroundColor=ann.color;
      const cat=document.getElementById('tooltip-category');cat.style.color=ann.color;cat.textContent=ann.category;
      document.getElementById('tooltip-content').textContent=ann.content;
      document.getElementById('tooltip-meaning').textContent=ann.meaning;
      const wrapper=document.getElementById('typing-wrapper'),engine=document.getElementById('typing-engine'),charSpan=engine.querySelector('[data-idx="'+annData.startIdx+'"]');
      if(!charSpan)return;
      const wr=wrapper.getBoundingClientRect(),cr=charSpan.getBoundingClientRect();
      let tl=cr.left-wr.left;const tt=cr.bottom-wr.top+wrapper.scrollTop+12;
      tl=Math.max(8,Math.min(tl,wr.width-330));
      tooltip.style.left=tl+'px';tooltip.style.top=tt+'px';
      for(let i=annData.startIdx;i<annData.endIdx;i++){const s=engine.querySelector('[data-idx="'+i+'"]');if(s){s.style.borderBottom='2px solid '+ann.color;s.style.paddingBottom='1px';}}
      tooltip.classList.add('visible');
      if(activeTooltipTimeout)clearTimeout(activeTooltipTimeout);
      activeTooltipTimeout=setTimeout(()=>tooltip.classList.remove('visible'),4000);
    }

    function updateStats(){
      if(startTime){const el=(Date.now()-startTime)/60000,txt=SOURCE_TEXT.substring(0,cursorPos),w=txt.trim().split(/\s+/).filter(w=>w.length>0).length,wpm=el>0?Math.round(w/el):0;document.getElementById('wpmDisplay').textContent=wpm;document.getElementById('wpmMobile').textContent=wpm;}
      const acc=totalKeystrokes>0?Math.round((totalCorrect/totalKeystrokes)*100):100;
      document.getElementById('accDisplay').textContent=acc+'%';document.getElementById('accMobile').textContent=acc;
      const pct=Math.round((cursorPos/SOURCE_TEXT.length)*100);
      document.getElementById('progressDisplay').textContent=pct+'%';document.getElementById('progressBar').style.width=pct+'%';document.getElementById('charCount').textContent=cursorPos;
    }

    function updateStreakUI(){
      const engine=document.getElementById('typing-engine'),badge=document.getElementById('streakBadge'),count=document.getElementById('streakCount');
      if(streak>=10){engine.classList.add('streak-active');badge.classList.remove('hidden');badge.classList.add('inline-flex');count.textContent=streak;badge.style.animation='none';void badge.offsetHeight;badge.style.animation='';}
      else{engine.classList.remove('streak-active');badge.classList.add('hidden');badge.classList.remove('inline-flex');}
    }
    function checkMilestone(){const pct=Math.round((cursorPos/SOURCE_TEXT.length)*100);for(const m of[25,50,75]){if(pct>=m&&lastMilestone<m){lastMilestone=m;showMilestoneToast(m);break;}}}
    function showMilestoneToast(pct){const t=document.createElement('div');t.className='milestone-toast fixed top-20 left-1/2 z-50 flex items-center gap-2 px-5 py-2.5 bg-white border border-brand rounded-full shadow-float';t.innerHTML='<svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-bold text-brand">'+pct+'% hoàn thành!</span>';document.body.appendChild(t);setTimeout(()=>t.remove(),1900);}

    function completeSession(){
      isCompleted=true;const elapsed=startTime?(Date.now()-startTime):0;
      const m=Math.floor(elapsed/60000),s=Math.floor((elapsed%60000)/1000),ts=m+':'+s.toString().padStart(2,'0');
      const acc=totalKeystrokes>0?Math.round((totalCorrect/totalKeystrokes)*100):100;
      const words=SOURCE_TEXT.trim().split(/\s+/).filter(w=>w.length>0).length;
      const wpm=(elapsed/60000)>0?Math.round(words/(elapsed/60000)):0;
      document.getElementById('modalWpm').textContent=wpm;document.getElementById('modalAcc').textContent=acc+'%';document.getElementById('modalTime').textContent=ts;
      // Save result
      fetch(SAVE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({lesson_id:LESSON_ID,wpm:wpm,accuracy:acc})}).catch(()=>{});
      setTimeout(()=>document.getElementById('completionModal').classList.add('show'),600);
    }

    function resetSession(){
      cursorPos=0;totalCorrect=0;totalKeystrokes=0;startTime=null;isCompleted=false;charStates=[];streak=0;shownAnnotations.clear();lastMilestone=0;
      document.getElementById('completionModal').classList.remove('show');document.getElementById('annotation-tooltip').classList.remove('visible');
      updateStreakUI();renderChars();updateStats();document.getElementById('typing-engine').focus();
    }

    function bindEvents(){
      const engine=document.getElementById('typing-engine');
      engine.addEventListener('keydown',handleKeydown);engine.addEventListener('click',()=>engine.focus());
      document.getElementById('typing-wrapper').addEventListener('click',()=>engine.focus());
      document.getElementById('completionModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
      engine.addEventListener('paste',e=>e.preventDefault());engine.addEventListener('keydown',e=>{if(e.key==='Tab')e.preventDefault();});
    }
    function escapeHtml(str){const map={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};return str.replace(/[&<>"']/g,c=>map[c]);}

    document.addEventListener('DOMContentLoaded', init);
  </script>
  <x-client.layout.ai-chat-widget />
</body>
</html>
