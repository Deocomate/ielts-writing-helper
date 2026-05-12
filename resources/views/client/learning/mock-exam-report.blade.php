<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Kết quả thi — {{ $exam->lesson->title ?? 'Mock Exam' }} — IELTS Type & Learn</title>
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
    body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#0E101A;background:#F9F9FA;-webkit-font-smoothing:antialiased;}
    ::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:#D1D5DB;border-radius:3px;}::-webkit-scrollbar-thumb:hover{background:#9CA3AF;}
    .score-bar-fill{transition:width 1.2s cubic-bezier(0.16,1,0.3,1);border-radius:99px;height:8px;}
    .comment-chip{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;}
    @media(prefers-reduced-motion:reduce){.score-bar-fill{transition:none;}}
  </style>
</head>
<body class="bg-app-bg min-h-screen">

  @if($exam->status === 'grading')
    <main class="min-h-screen flex items-center justify-center px-4">
      <div class="max-w-xl w-full bg-white border border-border-light rounded-2xl p-8 text-center shadow-card">
        <div class="w-16 h-16 border-4 border-brand/20 border-t-brand rounded-full animate-spin mx-auto"></div>
        <h1 class="text-2xl font-black text-text-primary mt-5">Đang chấm bài thi</h1>
        <p class="text-sm text-text-secondary mt-2">AI đang phân tích bài luận của bạn (TR, CC, LR, GRA)...</p>
        <p class="text-xs text-text-disabled mt-2">Thời gian xử lý có thể mất 15-40 giây tuỳ tải hệ thống.</p>
        <div class="mt-6 flex justify-center">
          <span id="gradingPulse" class="px-3 py-1 rounded-full bg-brand-light text-brand text-xs font-semibold">Đang xử lý</span>
        </div>
      </div>
    </main>

    <script>
      const statusUrl = "{{ route('client.learning.mock-exam.status', $exam->id) }}";
      const pulseEl = document.getElementById('gradingPulse');
      let pulseStep = 0;

      const pulseInterval = setInterval(() => {
        pulseStep = (pulseStep + 1) % 4;
        pulseEl.textContent = 'Đang xử lý' + '.'.repeat(pulseStep);
      }, 450);

      async function pollExamStatus() {
        try {
          const response = await fetch(statusUrl, {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          if (!response.ok) {
            return;
          }

          const payload = await response.json();
          if (payload.status === 'completed' || payload.status === 'failed') {
            clearInterval(pulseInterval);
            window.location.reload();
          }
        } catch (error) {
          console.error('Poll mock exam status failed', error);
        }
      }

      pollExamStatus();
      setInterval(pollExamStatus, 4000);
    </script>
  @elseif($exam->status === 'failed')
    <main class="min-h-screen flex items-center justify-center px-4">
      <div class="max-w-xl w-full bg-white border border-border-light rounded-2xl p-8 text-center shadow-card">
        <div class="w-14 h-14 rounded-full bg-red-100 text-semantic-red flex items-center justify-center mx-auto">
          <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-10a1 1 0 10-2 0v3a1 1 0 102 0V8zm-1 8a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 16z" clip-rule="evenodd"/></svg>
        </div>
        <h1 class="text-2xl font-black text-text-primary mt-5">Hệ thống AI tạm thời không khả dụng</h1>
        <p class="text-sm font-semibold text-semantic-red mt-2">Bài thi hiện chưa có điểm.</p>
        <p class="text-sm text-text-secondary mt-2">{{ data_get($exam->ai_feedback, 'overall_feedback', 'Hệ thống AI tạm thời gặp sự cố. Vui lòng thử lại.') }}</p>
        <div class="mt-6 flex items-center justify-center gap-2">
          @if($exam->lesson)
            <a href="{{ route('client.learning.mock-exam.intro', $exam->lesson_id) }}" class="px-4 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark">Làm lại bài thi</a>
          @endif
          <a href="{{ route('client.lessons.library') }}" class="px-4 py-2 border border-border-light text-sm font-medium text-text-secondary rounded-lg hover:bg-app-bg">Về thư viện</a>
        </div>
      </div>
    </main>
  @else

  @php
    $feedback = $exam->ai_feedback ?? [];
    $errorItems = is_array($feedback['errors'] ?? null) ? $feedback['errors'] : [];
    $bandLabel = match(true) {
      $exam->overall_band >= 8.0 => 'Excellent',
      $exam->overall_band >= 7.0 => 'Good',
      $exam->overall_band >= 6.0 => 'Competent',
      $exam->overall_band >= 5.0 => 'Modest',
      default => 'Limited',
    };
    $criteriaDetails = [
      ['key' => 'tr', 'label' => 'Task Response (TR)', 'score' => $exam->tr_score, 'color' => 'brand', 'feedback_key' => 'tr_feedback'],
      ['key' => 'cc', 'label' => 'Coherence & Cohesion (CC)', 'score' => $exam->cc_score, 'color' => 'semantic-blue', 'feedback_key' => 'cc_feedback'],
      ['key' => 'lr', 'label' => 'Lexical Resource (LR)', 'score' => $exam->lr_score, 'color' => 'semantic-green', 'feedback_key' => 'lr_feedback'],
      ['key' => 'gra', 'label' => 'Grammatical Range & Accuracy', 'score' => $exam->gra_score, 'color' => 'semantic-purple', 'feedback_key' => 'gra_feedback'],
    ];
  @endphp

  {{-- Navbar --}}
  <nav class="bg-white border-b border-border-light sticky top-0 z-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
      <div class="flex items-center gap-3">
        <a href="{{ route('client.lessons.library') }}" class="text-text-disabled hover:text-text-secondary">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
          <p class="text-xs font-semibold text-orange-500">Kết quả thi thử</p>
          <p class="text-sm font-bold text-text-primary">{{ $exam->lesson->title ?? '' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        @if($exam->lesson)
          <a href="{{ route('client.learning.mock-exam.intro', $exam->lesson_id) }}" class="px-3 py-1.5 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark cursor-pointer">Thi lại</a>
        @endif
      </div>
    </div>
  </nav>

  <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    {{-- Band hero --}}
    <div class="bg-white rounded-2xl border border-border-light p-6 mb-6 flex flex-col sm:flex-row items-center gap-6">
      <div class="flex-shrink-0">
        <div class="w-28 h-28 rounded-full border-[6px] border-brand bg-brand-light flex flex-col items-center justify-center">
          <p class="text-4xl font-black text-brand leading-none">{{ number_format($exam->overall_band, 1) }}</p>
          <p class="text-xs font-bold text-brand mt-1">Band</p>
        </div>
      </div>
      <div class="flex-1 text-center sm:text-left">
        <h1 class="text-2xl font-black text-text-primary">{{ $bandLabel }}</h1>
        <p class="text-sm text-text-secondary mt-1">Đây là band IELTS của bài thi thử này. Kết quả có thể chênh lệch ±0.5 so với thi thật.</p>
        @if(!empty($feedback['highlights']))
          <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
            @foreach(array_slice((array) $feedback['highlights'], 0, 3) as $highlight)
              <span class="comment-chip bg-brand-light text-brand">{{ $highlight }}</span>
            @endforeach
          </div>
        @endif
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-shrink-0">
        <div class="text-center px-3 py-2 bg-app-bg rounded-xl">
          <p class="text-xl font-black text-text-primary">{{ number_format($exam->tr_score, 1) }}</p>
          <p class="text-xs text-text-disabled mt-0.5">TR</p>
        </div>
        <div class="text-center px-3 py-2 bg-app-bg rounded-xl">
          <p class="text-xl font-black text-text-primary">{{ number_format($exam->cc_score, 1) }}</p>
          <p class="text-xs text-text-disabled mt-0.5">CC</p>
        </div>
        <div class="text-center px-3 py-2 bg-app-bg rounded-xl">
          <p class="text-xl font-black text-text-primary">{{ number_format($exam->lr_score, 1) }}</p>
          <p class="text-xs text-text-disabled mt-0.5">LR</p>
        </div>
        <div class="text-center px-3 py-2 bg-app-bg rounded-xl">
          <p class="text-xl font-black text-text-primary">{{ number_format($exam->gra_score, 1) }}</p>
          <p class="text-xs text-text-disabled mt-0.5">GRA</p>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      {{-- Left: Detailed scores --}}
      <div class="space-y-4">
        @foreach($criteriaDetails as $c)
          <div class="bg-white rounded-xl border border-border-light p-5">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-{{ $c['color'] }}"></div>
                <h3 class="font-semibold text-sm text-text-primary">{{ $c['label'] }}</h3>
              </div>
              <span class="text-lg font-black text-text-primary">{{ number_format($c['score'], 1) }}</span>
            </div>
            <div class="w-full bg-app-bg rounded-full h-2 mb-3">
              <div class="score-bar-fill bg-{{ $c['color'] }}" style="width:{{ min(100, round($c['score'] / 9 * 100)) }}%"></div>
            </div>
            @if(!empty($feedback[$c['feedback_key']]))
              <p class="text-xs text-text-secondary">{{ $feedback[$c['feedback_key']] }}</p>
            @endif
          </div>
        @endforeach
      </div>

      {{-- Right: User essay --}}
      <div class="bg-white rounded-xl border border-border-light p-5">
        <h3 class="font-semibold text-sm text-text-primary mb-4">Bài viết của bạn</h3>
        <div class="text-sm leading-8 text-text-secondary space-y-3 whitespace-pre-line">{{ $exam->user_essay }}</div>

        {{-- Error list from AI feedback --}}
        @if(!empty($errorItems))
          <div class="mt-4 pt-4 border-t border-border-light space-y-2">
            <p class="text-xs font-semibold text-text-primary mb-2">Lỗi được phát hiện</p>
            @foreach($errorItems as $error)
              <div class="flex items-start gap-2 p-2.5 bg-red-50 rounded-lg">
                <svg class="w-4 h-4 text-semantic-red flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div>
                  <p class="text-xs font-semibold text-semantic-red">{{ $error['type'] ?? 'Error' }}: "{{ $error['text'] ?? '' }}"</p>
                  @if(!empty($error['correction']))
                    <p class="text-xs text-text-secondary">Sửa: <em>{{ $error['correction'] }}</em></p>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Exam stats --}}
        <div class="mt-4 pt-4 border-t border-border-light flex items-center gap-4 text-xs text-text-disabled">
          <span>{{ $exam->word_count }} từ</span>
          <span>{{ floor($exam->time_taken_seconds / 60) }} phút {{ $exam->time_taken_seconds % 60 }}s</span>
          <span>{{ $exam->submitted_at?->format('d/m/Y H:i') }}</span>
        </div>
      </div>
    </div>

    {{-- CTA --}}
    @if($exam->lesson)
      <div class="mt-6 bg-gradient-to-r from-brand to-brand-dark rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-white">
          <p class="font-bold">
            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
            Giờ chép bài mẫu để cải thiện!
          </p>
          <p class="text-sm opacity-80 mt-0.5">Học từ bài đạt Band {{ $exam->lesson->band_score }} cùng chủ đề này.</p>
        </div>
        <a href="{{ route('client.learning.dictation', $exam->lesson_id) }}" class="flex-shrink-0 px-5 py-2.5 bg-white text-brand font-bold text-sm rounded-xl hover:bg-brand-light transition-colors cursor-pointer">Chép bài mẫu Band {{ $exam->lesson->band_score }}</a>
      </div>
    @endif
  </main>
  @endif
  <x-client.layout.ai-chat-widget />
</body>
</html>
