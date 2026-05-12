{{-- SECTION 5: 3 CORE FEATURES (Modes) --}}
<section id="features" class="py-16 sm:py-24 bg-app-bg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center reveal">
      <h2 class="text-2xl sm:text-3xl font-bold text-text-primary leading-tight">
        3 chế độ học, một lộ trình toàn diện
      </h2>
      <p class="mt-3 text-base text-text-secondary max-w-2xl mx-auto leading-relaxed">
        Kết hợp Chép chính tả → Phân tích sâu → Thi thử AI để nắm vững kỹ năng Writing thực sự hiệu quả.
      </p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 lg:gap-8 mt-12">

      {{-- Feature 1: Dictation Mode --}}
      <div class="feature-card bg-white rounded-2xl p-6 lg:p-8 shadow-card border border-border-light cursor-pointer reveal" style="transition-delay: 0.05s">
        <div class="w-12 h-12 bg-brand-light rounded-xl flex items-center justify-center mb-5">
          <svg class="w-6 h-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
          </svg>
        </div>
        <span class="band-pill bg-brand-light text-brand mb-3">{{ $features[0]['badge'] ?? 'Miễn phí' }}</span>
        <h3 class="text-lg font-semibold text-text-primary mt-3">{{ $features[0]['title'] ?? 'Chép chính tả' }}</h3>
        <p class="text-sm text-text-secondary mt-2 leading-relaxed">
          Gõ lại bài mẫu Band 8.0+ từng ký tự. Hệ thống highlight <span class="text-brand font-medium">xanh khi đúng</span>, <span class="text-semantic-red font-medium">đỏ khi sai</span> ngay lập tức.
          Ghi nhớ từ vựng và cấu trúc qua cơ chế muscle memory.
        </p>
        <ul class="mt-4 space-y-2 text-sm text-text-secondary">
          @foreach(($features[0]['highlights'] ?? []) as $highlight)
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
              <span>{{ $highlight }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Feature 2: Read & Analyze Mode --}}
      <div class="feature-card bg-white rounded-2xl p-6 lg:p-8 shadow-card border border-border-light cursor-pointer reveal" style="transition-delay: 0.15s">
        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-5">
          <svg class="w-6 h-6 text-semantic-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <span class="band-pill bg-blue-50 text-semantic-blue mb-3">{{ $features[1]['badge'] ?? 'Miễn phí' }}</span>
        <h3 class="text-lg font-semibold text-text-primary mt-3">{{ $features[1]['title'] ?? 'Đọc & Phân tích' }}</h3>
        <p class="text-sm text-text-secondary mt-2 leading-relaxed">
          Giao diện phong cách <strong class="text-text-primary">Grammarly</strong>: mỗi từ vựng hay, cấu trúc ăn điểm được
          <span class="underline-lexical">gạch chân màu</span>. Hover vào để xem giải thích chi tiết.
        </p>
        <ul class="mt-4 space-y-2 text-sm text-text-secondary">
          @foreach(($features[1]['highlights'] ?? []) as $highlight)
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-semantic-blue mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
              <span>{!! $highlight !!}</span>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Feature 3: Mock Exam Mode --}}
      <div class="feature-card bg-white rounded-2xl p-6 lg:p-8 shadow-card border border-border-light cursor-pointer relative overflow-hidden reveal" style="transition-delay: 0.25s">
        <!-- Pro Badge -->
        <div class="absolute top-4 right-4">
          <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-50 border border-yellow-200 rounded-full text-[11px] font-semibold text-yellow-700">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
            PRO
          </span>
        </div>

        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-5">
          <svg class="w-6 h-6 text-semantic-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-text-primary mt-3">{{ $features[2]['title'] ?? 'Phòng thi & AI chấm' }}</h3>
        <p class="text-sm text-text-secondary mt-2 leading-relaxed">
          Giao diện thi thật: đề bài bên trái, editor bên phải. Đồng hồ đếm ngược + word count.
          AI chấm điểm chi tiết theo <strong class="text-text-primary">4 tiêu chí IELTS</strong> trong vòng 30 giây.
        </p>
        <ul class="mt-4 space-y-2 text-sm text-text-secondary">
          @foreach(($features[2]['highlights'] ?? []) as $highlight)
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-semantic-purple mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
              <span>{{ $highlight }}</span>
            </li>
          @endforeach
        </ul>
      </div>

    </div>
  </div>
</section>
