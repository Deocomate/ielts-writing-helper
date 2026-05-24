<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Phân tích — {{ $lesson->title }} — IELTS Type & Learn</title>
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
    ::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:#D1D5DB;border-radius:3px;}::-webkit-scrollbar-thumb:hover{background:#9CA3AF;}
    .underline-grammar{border-bottom:2.5px solid #FF5E5E;cursor:pointer;position:relative;}
    .underline-coherence{border-bottom:2.5px solid #007AFF;cursor:pointer;position:relative;}
    .underline-lexical,.underline-vocabulary{border-bottom:2.5px solid #11A683;cursor:pointer;position:relative;}
    .underline-gra,.underline-logic{border-bottom:2.5px solid #8F00FF;cursor:pointer;position:relative;}
    .tooltip-card{display:none;position:absolute;z-index:30;bottom:calc(100% + 10px);left:0;width:260px;background:#FFF;color:#0E101A;border-radius:12px;padding:14px;box-shadow:0 8px 24px rgba(0,0,0,0.12),0 2px 8px rgba(0,0,0,0.08);border:1px solid #E1E4E8;font-size:12px;line-height:1.6;opacity:0;transform:translateY(4px);transition:opacity 0.2s ease,transform 0.2s ease;pointer-events:none;}
    .tooltip-card::after{content:'';position:absolute;top:100%;left:16px;border:6px solid transparent;border-top-color:#FFF;filter:drop-shadow(0 1px 1px rgba(0,0,0,0.05));}
    [class*="underline-"]:hover .tooltip-card{display:block;opacity:1;transform:translateY(0);pointer-events:auto;}
    [class*="underline-"]:focus-within .tooltip-card,[class*="underline-"].mobile-open .tooltip-card{display:block;opacity:1;transform:translateY(0);pointer-events:auto;}
    [class*="underline-"]:focus-visible{outline:2px solid #9ca3af;outline-offset:2px;border-radius:3px;}
    .legend-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;border:1.5px solid transparent;}
    .legend-pill.active{border-color:currentColor;}
    .score-bar-fill{transition:width 1.2s cubic-bezier(0.16,1,0.3,1);border-radius:99px;height:6px;}
    @media(prefers-reduced-motion:reduce){.tooltip-card{transition:none;}.score-bar-fill{transition:none;}.legend-pill{transition:none;}}
  </style>
</head>
<body class="bg-app-bg min-h-screen lg:h-screen flex flex-col lg:overflow-hidden">

  {{-- Navbar --}}
  <nav class="bg-white border-b border-border-light flex-shrink-0">
    <div class="px-4 sm:px-6 flex items-center justify-between h-14">
      <div class="flex items-center gap-3">
        <a href="{{ route('client.lessons.library') }}" class="text-text-disabled hover:text-text-secondary">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
          <p class="text-xs font-semibold text-blue-500">Đọc & Phân tích</p>
          <p class="text-sm font-bold text-text-primary">{{ $lesson->title }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="hidden sm:inline px-2.5 py-1 bg-brand-light text-brand text-xs font-bold rounded-lg">Band {{ $lesson->band_score }}</span>
        <a href="{{ route('client.learning.dictation', $lesson->id) }}" class="hidden md:inline-flex px-3 py-1.5 border border-border-light text-xs font-medium text-text-secondary rounded-lg hover:bg-app-bg transition-colors cursor-pointer">Chép lại</a>
      </div>
    </div>
  </nav>

  {{-- Main split layout --}}
  <div class="flex flex-col lg:flex-row flex-1 overflow-x-hidden lg:overflow-hidden">
    {{-- Left: annotated essay --}}
    <div class="flex-1 overflow-y-auto">
      <div class="max-w-2xl mx-auto px-6 py-8">
        {{-- Collapsible Prompt & Image Preview --}}
        <details class="group bg-white rounded-xl border border-border-light shadow-card mb-5 overflow-hidden [&_summary::-webkit-details-marker]:hidden">
          <summary class="flex items-center justify-between px-5 py-3 cursor-pointer select-none hover:bg-gray-50/50 transition-colors">
            <div class="flex items-center gap-2.5">
              <div class="w-7 h-7 bg-brand-light text-brand rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
              </div>
              <span class="text-sm font-semibold text-text-primary">Xem đề bài & hình ảnh minh họa</span>
            </div>
            <svg class="w-4 h-4 text-text-secondary transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <div class="px-5 pb-5 pt-3 border-t border-border-light space-y-4 bg-white">
            <p class="text-sm text-text-secondary leading-relaxed whitespace-pre-line">{{ $lesson->prompt_text }}</p>
            @if($lesson->image_path)
              <div class="rounded-xl border border-border-light overflow-hidden bg-white p-2">
                <img src="{{ Storage::disk('public')->url($lesson->image_path) }}" alt="Diagram/Map" class="w-full max-h-[350px] object-contain mx-auto" />
              </div>
            @endif
          </div>
        </details>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-2 mb-6">
          <span class="text-xs text-text-disabled font-medium mr-1">Chú thích:</span>
          <span class="legend-pill" style="color:#FF5E5E;background:#FFF0F0">Grammar</span>
          <span class="legend-pill" style="color:#007AFF;background:#F0F5FF">Coherence</span>
          <span class="legend-pill" style="color:#11A683;background:#E8F8F3">Lexical</span>
          <span class="legend-pill" style="color:#8F00FF;background:#F5F0FF">Grammatical Range</span>
        </div>
        {{-- Essay body with annotations --}}
        <div id="annotated-essay-content" class="text-[15px] leading-9 text-text-primary space-y-4">
          {!! $annotatedHtml !!}
        </div>

        {{-- Mobile summary --}}
        @php
          $mobileCriteria = [
            ['label' => 'Lexical', 'count' => $stats['vocabulary'], 'color' => 'brand'],
            ['label' => 'Grammar', 'count' => $stats['grammar'], 'color' => 'semantic-red'],
            ['label' => 'Coherence', 'count' => $stats['coherence'], 'color' => 'semantic-blue'],
            ['label' => 'Range', 'count' => $stats['logic'], 'color' => 'semantic-purple'],
          ];
        @endphp
        <div class="lg:hidden mt-6 bg-white border border-border-light rounded-xl p-4">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Tổng quan nhanh</p>
            <span class="text-sm font-bold text-brand">Band {{ $lesson->band_score }}</span>
          </div>
          <div class="grid grid-cols-2 gap-2 mt-3">
            @foreach($mobileCriteria as $item)
              <div class="rounded-lg bg-app-bg px-3 py-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-text-secondary">{{ $item['label'] }}</span>
                  <span class="text-xs font-semibold text-text-primary">{{ $item['count'] }}</span>
                </div>
                <div class="w-full bg-white rounded-full h-1.5 mt-1.5">
                  <div class="score-bar-fill bg-{{ $item['color'] }}" style="width:{{ min(100, $item['count'] * 10) }}%"></div>
                </div>
              </div>
            @endforeach
          </div>
          <p class="text-[11px] text-semantic-red font-semibold mt-3">Chế độ Chép chính tả và Thi thử chấm AI chỉ hỗ trợ PC/Laptop.</p>
        </div>
      </div>
    </div>

    {{-- Right: Score sidebar --}}
    <div class="hidden lg:flex flex-col w-72 xl:w-80 border-l border-border-light bg-white overflow-y-auto flex-shrink-0">
      <div class="p-5">
        {{-- Overall band --}}
        <div class="text-center mb-5">
          <div class="w-24 h-24 rounded-full border-4 border-brand bg-brand-light flex flex-col items-center justify-center mx-auto">
            <p class="text-3xl font-black text-brand">{{ $lesson->band_score }}</p>
            <p class="text-xs font-semibold text-brand">Band</p>
          </div>
          <p class="text-sm font-semibold text-text-primary mt-3">{{ $lesson->task_type }}</p>
          <p class="text-xs text-text-secondary mt-0.5">{{ $lesson->question_type ?? '' }}</p>
        </div>

        {{-- Annotation stats --}}
        <div class="space-y-4">
          @php
            $criteria = [
              ['label' => 'Lexical Resource', 'count' => $stats['vocabulary'], 'color' => 'brand', 'pct' => min(100, $stats['vocabulary'] * 10)],
              ['label' => 'Grammar', 'count' => $stats['grammar'], 'color' => 'semantic-red', 'pct' => min(100, $stats['grammar'] * 10)],
              ['label' => 'Coherence & Cohesion', 'count' => $stats['coherence'], 'color' => 'semantic-blue', 'pct' => min(100, $stats['coherence'] * 10)],
              ['label' => 'Grammatical Range', 'count' => $stats['logic'], 'color' => 'semantic-purple', 'pct' => min(100, $stats['logic'] * 10)],
            ];
          @endphp
          @foreach($criteria as $c)
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-{{ $c['color'] }}"></span>
                  <span class="text-xs font-semibold text-text-primary">{{ $c['label'] }}</span>
                </div>
                <span class="text-sm font-bold text-text-primary">{{ $c['count'] }}</span>
              </div>
              <div class="w-full bg-app-bg rounded-full h-1.5">
                <div class="score-bar-fill bg-{{ $c['color'] }}" style="width:{{ $c['pct'] }}%"></div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Vocabulary list --}}
        @if($vocabularies->isNotEmpty())
          <div class="mt-5 pt-5 border-t border-border-light">
            <p class="text-xs font-semibold text-text-primary mb-2">Từ vựng nổi bật</p>
            <div class="space-y-1.5">
              @foreach($vocabularies->take(8) as $vocab)
                @php
                  $vocabularyMeaning = trim((string) ($vocab->meaning_vi ?? '')) !== ''
                    ? $vocab->meaning_vi
                    : (trim((string) ($vocab->meaning_en ?? '')) !== ''
                      ? $vocab->meaning_en
                      : 'Chưa có nghĩa');
                @endphp
                <div class="flex items-start justify-between gap-2 px-3 py-2 bg-app-bg rounded-lg">
                  <div class="min-w-0">
                    <p class="text-xs font-semibold text-text-primary">{{ $vocab->word }}</p>
                    <p class="text-[11px] text-text-secondary mt-0.5 leading-relaxed">{{ $vocabularyMeaning }}</p>
                  </div>
                  <form method="POST" action="{{ route('client.vocabulary.store') }}" class="js-save-vocabulary-form flex-shrink-0" data-word="{{ $vocab->word }}">
                    @csrf
                    <input type="hidden" name="word" value="{{ $vocab->word }}">
                    <input type="hidden" name="meaning" value="{{ $vocabularyMeaning }}">
                    <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                    <input type="hidden" name="context_sentence" value="{{ $vocab->example_sentence ?? '' }}">
                    <button type="submit" class="js-save-vocabulary-btn text-brand hover:text-brand-dark transition-colors cursor-pointer disabled:cursor-not-allowed disabled:opacity-60" title="Lưu từ vựng" data-state="idle" aria-label="Lưu từ {{ $vocab->word }}">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                  </form>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div id="vocabulary-save-toast" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:max-w-sm z-50 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-white shadow-float transition-all duration-200 opacity-0 translate-y-2 pointer-events-none hidden" role="status" aria-live="polite"></div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const essayContainer = document.getElementById('annotated-essay-content');
      if (!essayContainer) {
        return;
      }

      const annotationNodes = essayContainer.querySelectorAll('[class*="underline-"]');
      annotationNodes.forEach((node) => {
        node.setAttribute('tabindex', '0');
      });

      const closeAll = () => {
        annotationNodes.forEach((node) => node.classList.remove('mobile-open'));
      };

      essayContainer.addEventListener('click', (event) => {
        if (window.innerWidth >= 768) {
          return;
        }

        const target = event.target.closest('[class*="underline-"]');
        if (!target) {
          closeAll();
          return;
        }

        event.preventDefault();
        const alreadyOpen = target.classList.contains('mobile-open');
        closeAll();
        if (!alreadyOpen) {
          target.classList.add('mobile-open');
        }
      });

      document.addEventListener('click', (event) => {
        if (window.innerWidth >= 768) {
          return;
        }

        if (!event.target.closest('#annotated-essay-content [class*="underline-"]')) {
          closeAll();
        }
      });

      const saveVocabularyForms = document.querySelectorAll('.js-save-vocabulary-form');
      const toast = document.getElementById('vocabulary-save-toast');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
      const plusIcon = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
      const checkIcon = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
      const spinnerIcon = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
      let toastTimeoutId = null;

      const showToast = (message, isError = false) => {
        if (!toast) {
          return;
        }

        toast.textContent = message;
        toast.classList.remove('hidden', 'opacity-0', 'translate-y-2', 'bg-emerald-500', 'bg-semantic-red');
        toast.classList.add('opacity-100', 'translate-y-0', isError ? 'bg-semantic-red' : 'bg-emerald-500');

        if (toastTimeoutId) {
          clearTimeout(toastTimeoutId);
        }

        toastTimeoutId = setTimeout(() => {
          toast.classList.remove('opacity-100', 'translate-y-0', 'bg-emerald-500', 'bg-semantic-red');
          toast.classList.add('opacity-0', 'translate-y-2');
          setTimeout(() => {
            toast.classList.add('hidden');
          }, 200);
        }, 1800);
      };

      const setButtonState = (button, state) => {
        button.dataset.state = state;
        button.disabled = state === 'loading' || state === 'saved';

        if (state === 'loading') {
          button.innerHTML = spinnerIcon;
          button.title = 'Đang lưu từ vựng';
          return;
        }

        if (state === 'saved') {
          button.classList.remove('text-brand', 'hover:text-brand-dark');
          button.classList.add('text-semantic-green');
          button.innerHTML = checkIcon;
          button.title = 'Đã lưu';
          return;
        }

        button.classList.remove('text-semantic-green');
        button.classList.add('text-brand', 'hover:text-brand-dark');
        button.innerHTML = plusIcon;
        button.title = 'Lưu từ vựng';
      };

      saveVocabularyForms.forEach((form) => {
        const button = form.querySelector('.js-save-vocabulary-btn');

        if (!button) {
          return;
        }

        setButtonState(button, 'idle');

        form.addEventListener('submit', async (event) => {
          event.preventDefault();

          if (button.dataset.state === 'loading' || button.dataset.state === 'saved') {
            return;
          }

          setButtonState(button, 'loading');

          try {
            const response = await fetch(form.action, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
              },
              body: new FormData(form),
              credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok) {
              let errorMessage = payload?.message ?? 'Không thể lưu từ vựng. Vui lòng thử lại.';

              if (payload?.errors) {
                const firstError = Object.values(payload.errors)[0];
                if (Array.isArray(firstError) && firstError.length > 0) {
                  errorMessage = firstError[0];
                }
              }

              throw new Error(errorMessage);
            }

            const word = payload?.vocabulary?.word ?? form.dataset.word ?? 'Từ vựng';
            const isCreated = payload?.status === 'created';

            setButtonState(button, 'saved');
            showToast(isCreated ? `Đã lưu "${word}" vào sổ tay.` : `"${word}" đã có trong sổ tay.`);
          } catch (error) {
            setButtonState(button, 'idle');
            showToast(error instanceof Error ? error.message : 'Không thể lưu từ vựng. Vui lòng thử lại.', true);
          }
        });
      });
    });
  </script>
  <x-client.layout.ai-chat-widget />
</body>
</html>
