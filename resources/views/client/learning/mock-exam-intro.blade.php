<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Hướng dẫn thi thử — {{ $lesson->title }} — IELTS Type & Learn</title>
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
  </style>
</head>
<body class="bg-app-bg min-h-screen flex flex-col">

  {{-- Minimal nav --}}
  <nav class="bg-white border-b border-border-light">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between h-14">
      <div class="flex items-center gap-3">
        <a href="{{ route('client.lessons.library') }}" class="text-text-disabled hover:text-text-secondary transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
          <p class="text-xs font-semibold text-semantic-purple">Thi thử</p>
          <p class="text-sm font-bold text-text-primary leading-tight">{{ $lesson->title }}</p>
        </div>
      </div>
      <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2">
        <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </div>
        <span class="font-bold text-sm text-text-primary hidden sm:inline">IELTS Type & Learn</span>
      </a>
    </div>
  </nav>

  <main class="flex-1 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-border-light w-full max-w-lg shadow-card">
      {{-- Header --}}
      <div class="p-6 border-b border-border-light">
        <div class="flex items-center gap-2 mb-3">
          <span class="px-2 py-0.5 bg-brand-light text-brand text-xs font-semibold rounded">{{ $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2' }}</span>
          @if($lesson->question_type)
            <span class="px-2 py-0.5 bg-app-bg text-text-secondary text-xs rounded">{{ $lesson->question_type }}</span>
          @endif
          <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-bold rounded">Band {{ $lesson->band_score }}</span>
        </div>
        <h1 class="text-xl font-bold text-text-primary">{{ $lesson->title }}</h1>
        <p class="text-sm text-text-secondary mt-1">Bạn sẽ viết bài luận {{ $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2' }} hoàn chỉnh dưới sự hỗ trợ của đồng hồ đếm ngược. Kết quả sẽ được chấm bằng AI.</p>
      </div>

      {{-- Timer choice --}}
      <div class="p-6 border-b border-border-light" x-data="{ selected: 40 }">
        <p class="text-sm font-semibold text-text-primary mb-3">Chọn thời gian</p>
        <div class="grid grid-cols-3 gap-2">
          @foreach([20, 40, 60] as $mins)
            <button
              @click="selected = {{ $mins }}"
              :class="selected === {{ $mins }}
                ? 'border-brand bg-brand-light text-brand font-semibold'
                : 'border-border-light text-text-secondary font-medium hover:border-brand hover:bg-brand-light'"
              class="time-btn py-3 rounded-xl border-2 text-sm transition-all cursor-pointer"
            >{{ $mins }} phút</button>
          @endforeach
        </div>
        <input type="hidden" id="selectedTime" :value="selected" />
      </div>

      {{-- Rules --}}
      <div class="p-6">
        <p class="text-sm font-semibold text-text-primary mb-3">Lưu ý trước khi bắt đầu</p>
        <ul class="space-y-2">
          @php $minWords = $lesson->task_type === 'task_1' ? 150 : 250; @endphp
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-xs text-text-secondary">Viết ít nhất <strong class="text-text-primary">{{ $minWords }} từ</strong> đối với {{ $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2' }}.</p>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-xs text-text-secondary">Không được sao chép, sử dụng chức năng tự điền hay AI khác.</p>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-xs text-text-secondary">Bài sẽ được chấm AI theo 4 tiêu chí IELTS: TR, CC, LR, GRA.</p>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-semantic-yellow flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <p class="text-xs text-text-secondary">Đồng hồ sẽ tự nộp bài khi hết giờ.</p>
          </li>
        </ul>
        <a id="startBtn" href="#" onclick="goToRoom(event)" class="mt-5 flex items-center justify-center gap-2 w-full py-3 bg-brand text-white font-bold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Bắt đầu thi
        </a>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    function goToRoom(e) {
      e.preventDefault();
      const mins = document.getElementById('selectedTime').value;
      const url = "{{ route('client.learning.mock-exam.room', $lesson->id) }}?time=" + mins;
      window.location.href = url;
    }
  </script>
  <x-client.layout.ai-chat-widget />
</body>
</html>
