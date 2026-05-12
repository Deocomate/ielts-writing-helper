{{-- SECTION 7: INTERACTIVE DEMO PREVIEW --}}
<section id="demo" class="py-16 sm:py-24 bg-app-bg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Demo: Dictation Mode Preview --}}
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center reveal">
      <div class="order-2 lg:order-1">
        <div class="bg-white rounded-2xl shadow-card border border-border-light p-6 sm:p-8">
          <!-- Mock Editor Header -->
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-border-light">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
              </svg>
              <span class="text-sm font-semibold text-text-primary">Dictation Mode</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-text-secondary">
              <span>WPM: <strong class="text-text-primary">67</strong></span>
              <span>Accuracy: <strong class="text-brand">96.3%</strong></span>
            </div>
          </div>

          <!-- Typing Demo -->
          <div class="font-mono text-base leading-[2] tracking-wide">
            <span class="typing-char-correct">The line graph </span><!--
            --><span class="typing-char-correct">illustrates </span><!--
            --><span class="typing-char-correct">the changes in </span><!--
            --><span class="typing-char-correct">the amount of </span><!--
            --><span class="typing-char-correct">goods </span><!--
            --><span class="typing-char-wrong">trasnported</span><!--
            --><span class="typing-cursor-line" aria-hidden="true"></span><!--
            --><span class="typing-char-pending"> via four different modes in the UK between 1974 and 2002.</span>
          </div>

          <!-- Progress Bar -->
          <div class="mt-6 bg-gray-100 rounded-full h-2 overflow-hidden">
            <div class="h-full bg-brand rounded-full transition-all duration-300" style="width: 42%"></div>
          </div>
          <p class="text-xs text-text-secondary mt-2">Tiến độ: 42% · 108/258 từ</p>
        </div>
      </div>

      <div class="order-1 lg:order-2">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-brand-light rounded-full text-xs font-semibold text-brand uppercase tracking-wide mb-4">Chế độ 1</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-text-primary leading-tight">
          Gõ lại, nhớ ngay.<br />
          <span class="text-brand">Muscle memory cho IELTS.</span>
        </h2>
        <p class="mt-4 text-base text-text-secondary leading-relaxed">
          Thay vì đọc thụ động, bạn sẽ gõ lại từng câu của bài mẫu Band 8.0+.
          Mỗi ký tự sai sẽ bị đánh dấu đỏ ngay lập tức — buộc bạn phải sửa trước khi đi tiếp.
          Phương pháp này giúp não bộ <strong class="text-text-primary">ghi nhớ sâu</strong> từ vựng và cấu trúc câu.
        </p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 mt-6 text-sm font-medium text-brand hover:text-brand-dark transition-colors duration-200 cursor-pointer group">
          Thử chép chính tả ngay
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </a>
      </div>
    </div>

    {{-- Demo: Analyze Mode Preview --}}
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mt-20 reveal">
      <div>
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-full text-xs font-semibold text-semantic-blue uppercase tracking-wide mb-4">Chế độ 2</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-text-primary leading-tight">
          Hiểu tại sao <span class="text-semantic-blue">bài này đạt Band 8.0.</span>
        </h2>
        <p class="mt-4 text-base text-text-secondary leading-relaxed">
          Mỗi từ vựng hay, cấu trúc ăn điểm, từ nối logic đều được
          <span class="underline-lexical">gạch chân</span> với
          <span class="underline-gra">màu sắc riêng</span>.
          Di chuột vào để xem giải thích chi tiết — giống hệt cách
          <strong class="text-text-primary">Grammarly</strong> phân tích văn bản.
        </p>
        <div class="flex flex-wrap items-center gap-3 mt-5">
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-semantic-green">
            <span class="w-3 h-0.5 bg-semantic-green rounded" aria-hidden="true"></span> Từ vựng (LR)
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-semantic-purple">
            <span class="w-3 h-0.5 bg-semantic-purple rounded" aria-hidden="true"></span> Ngữ pháp (GRA)
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-semantic-blue">
            <span class="w-3 h-0.5 bg-semantic-blue rounded" aria-hidden="true"></span> Mạch lạc (CC)
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-semantic-red">
            <span class="w-3 h-0.5 bg-semantic-red rounded" aria-hidden="true"></span> Lỗi sai
          </span>
        </div>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 mt-6 text-sm font-medium text-semantic-blue hover:text-blue-700 transition-colors duration-200 cursor-pointer group">
          Xem phân tích bài mẫu
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </a>
      </div>

      <div>
        <div class="bg-white rounded-2xl shadow-card border border-border-light p-6 sm:p-8">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-border-light">
            <svg class="w-4 h-4 text-semantic-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-semibold text-text-primary">Read & Analyze Mode</span>
          </div>

          <div class="text-[15px] leading-[1.85] text-text-primary space-y-3">
            <p>
              <span class="underline-coherence">Overall, it is evident that</span> the number of
              individuals <span class="underline-lexical">engaging in</span> regular physical activity
              <span class="underline-lexical">rose considerably</span> over the period,
              <span class="underline-gra">while sedentary behaviour, which had been predominant in the earlier years,</span>
              showed a <span class="underline-lexical">marked decline</span>.
            </p>
          </div>

          <!-- Score Sidebar Mini -->
          <div class="mt-5 pt-4 border-t border-border-light grid grid-cols-4 gap-3">
            <div class="text-center">
              <p class="text-lg font-bold text-text-primary">8.0</p>
              <p class="text-[10px] text-text-secondary uppercase tracking-wider">TR</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold text-semantic-blue">8.5</p>
              <p class="text-[10px] text-text-secondary uppercase tracking-wider">CC</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold text-semantic-green">8.5</p>
              <p class="text-[10px] text-text-secondary uppercase tracking-wider">LR</p>
            </div>
            <div class="text-center">
              <p class="text-lg font-bold text-semantic-purple">8.0</p>
              <p class="text-[10px] text-text-secondary uppercase tracking-wider">GRA</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Demo: Mock Exam Preview --}}
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mt-20 reveal">
      <div class="order-2 lg:order-1">
        <div class="bg-white rounded-2xl shadow-card border border-border-light overflow-hidden">
          <!-- Mock Exam Header -->
          <div class="bg-gray-50 px-6 py-3 border-b border-border-light flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-semantic-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
              </svg>
              <span class="text-sm font-semibold text-text-primary">Mock Exam — Task 2</span>
            </div>
            <div class="flex items-center gap-4 text-xs">
              <span class="text-semantic-red font-semibold">
                <svg class="w-3.5 h-3.5 inline mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                32:15
              </span>
              <span class="text-text-secondary">Words: <strong class="text-text-primary">187</strong>/250</span>
            </div>
          </div>

          <!-- Split Pane Preview -->
          <div class="grid grid-cols-2 divide-x divide-border-light">
            <!-- Left: Prompt -->
            <div class="p-5">
              <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-2">Đề bài</p>
              <p class="text-[13px] text-text-primary leading-relaxed">
                Some people believe that the best way to reduce crime is to give longer prison sentences.
                Others, however, argue that there are better ways to reduce crime.
              </p>
              <p class="text-[13px] text-text-primary leading-relaxed mt-2 font-medium">
                Discuss both views and give your own opinion.
              </p>
            </div>
            <!-- Right: Editor -->
            <div class="p-5 bg-white">
              <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-2">Bài viết</p>
              <p class="text-[13px] text-text-secondary leading-relaxed">
                It is often argued that imposing stricter punishments, particularly longer prison terms, is the most effective approach to...
              </p>
              <span class="typing-cursor-line ml-0.5" aria-hidden="true"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="order-1 lg:order-2">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-purple-50 rounded-full text-xs font-semibold text-semantic-purple uppercase tracking-wide mb-4">
          Chế độ 3 · PRO
        </span>
        <h2 class="text-2xl sm:text-3xl font-bold text-text-primary leading-tight">
          Thi thử. <span class="text-semantic-purple">AI chấm điểm.</span><br />
          Biết ngay mình band mấy.
        </h2>
        <p class="mt-4 text-base text-text-secondary leading-relaxed">
          Phòng thi mô phỏng thật: đề bài + editor chia đôi, đồng hồ đếm ngược,
          bộ đếm từ real-time. Sau khi nộp, AI chấm và trả kết quả chi tiết theo
          <strong class="text-text-primary">TR · CC · LR · GRA</strong> trong vòng 30 giây.
        </p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 mt-6 text-sm font-medium text-semantic-purple hover:text-purple-700 transition-colors duration-200 cursor-pointer group">
          Thử thi thử miễn phí
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </a>
      </div>
    </div>

  </div>
</section>
