<x-client.layout.dashboard title="Thư viện bài học" activePage="lessons">
  <x-slot:head>
    <style>
      .lesson-card{transition:box-shadow 0.2s, transform 0.2s;}
      .lesson-card:hover{box-shadow:0 4px 6px rgba(0,0,0,0.05),0 10px 15px rgba(0,0,0,0.1);transform:translateY(-2px);}
      .filter-chip{transition:all 0.15s;}
      .filter-chip.active{background:#11A683;color:#fff;border-color:#11A683;}
      .filter-panel{transition:max-height 0.3s ease,opacity 0.3s ease;max-height:0;opacity:0;overflow:hidden;}
      .filter-panel.open{max-height:600px;opacity:1;}
      @media(prefers-reduced-motion:reduce){.lesson-card{transition:none;}.lesson-card:hover{transform:none;}.filter-chip{transition:none;}.filter-panel{transition:none;}}
    </style>
    <script>
      function lessonLibraryFilters(initialTaskType, initialQuestionType) {
        return {
          taskType: initialTaskType || '',
          questionType: initialQuestionType || '',
          questionTypeByTask: {
            task_1: [
              { value: 'line_graph', label: 'Line Graph' },
              { value: 'bar_chart', label: 'Bar Chart' },
              { value: 'pie_chart', label: 'Pie Chart' },
              { value: 'map', label: 'Map' },
              { value: 'table', label: 'Table' },
              { value: 'mixed_chart', label: 'Mixed / Combined Charts' },
              { value: 'process_diagram', label: 'Process Diagram' },
              { value: 'flowchart', label: 'Flowchart' },
            ],
            task_2: [
              { value: 'opinion', label: 'Opinion (Agree / Disagree)' },
              { value: 'discussion', label: 'Discussion (Discuss Both Views)' },
              { value: 'problem_solution', label: 'Problem & Solution' },
              { value: 'advantages_disadvantages', label: 'Advantages & Disadvantages' },
              { value: 'two_part', label: 'Two-part Question' },
              { value: 'causes_effects', label: 'Causes & Effects' },
              { value: 'positive_negative', label: 'Positive or Negative Development' },
            ],
          },
          get currentQuestionTypes() {
            return this.questionTypeByTask[this.taskType] ?? [];
          },
          syncQuestionType() {
            const isValid = this.currentQuestionTypes.some((type) => type.value === this.questionType);
            if (!isValid) {
              this.questionType = '';
            }
          },
          init() {
            this.syncQuestionType();
          },
        };
      }

      function lessonModePicker() {
        return {
          selectedLesson: null,
          isMobile: window.innerWidth < 768,
          dictationUrlTemplate: @js(route('client.learning.dictation', ['lesson' => '__LESSON__'])),
          analyzeUrlTemplate: @js(route('client.learning.analyze', ['lesson' => '__LESSON__'])),
          mockExamIntroUrlTemplate: @js(route('client.learning.mock-exam.intro', ['lesson' => '__LESSON__'])),
          initModePicker() {
            const updateViewport = () => {
              this.isMobile = window.innerWidth < 768;
            };

            updateViewport();
            window.addEventListener('resize', updateViewport);
          },
          buildLessonUrl(template) {
            return template.replace('__LESSON__', String(this.selectedLesson));
          },
        };
      }
    </script>
  </x-slot:head>

  <x-slot:headerContent>
    <div>
      <h1 class="text-lg font-bold text-text-primary">Thư viện bài học</h1>
      <p class="text-xs text-text-secondary">{{ $totalCount }} bài học &middot; Task 1 & Task 2</p>
    </div>
  </x-slot:headerContent>
  <x-slot:headerActions>
    <form method="GET" action="{{ route('client.lessons.library') }}" class="hidden sm:flex items-center gap-2">
      @foreach($filters as $k => $v)
        @if($k !== 'search')
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
      @endforeach
      <div class="relative">
        <input name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm kiếm bài học..."
          class="pl-8 pr-4 py-2 bg-app-bg border border-border-light rounded-lg text-sm text-text-primary placeholder-text-disabled w-56" />
        <svg class="w-4 h-4 text-text-disabled absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </form>
    <button onclick="document.getElementById('filterPanel').classList.toggle('open')" class="flex items-center gap-1.5 px-3 py-2 border border-border-light text-xs font-medium text-text-secondary rounded-lg hover:bg-app-bg transition-colors cursor-pointer">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
      Bộ lọc
    </button>
  </x-slot:headerActions>

  {{-- Collapsible filter panel --}}
  <div id="filterPanel" class="filter-panel mb-4">
    <form method="GET" action="{{ route('client.lessons.library') }}" class="bg-white rounded-xl border border-border-light p-5" x-data="lessonLibraryFilters(@js($filters['task_type'] ?? ''), @js($filters['question_type'] ?? ''))" x-init="init()">
      @if(!empty($filters['search']))
        <input type="hidden" name="search" value="{{ $filters['search'] }}">
      @endif
      <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
        <div>
          <p class="text-xs font-semibold text-text-disabled uppercase tracking-wide mb-2">Loại bài</p>
          <div class="space-y-1.5">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary hover:text-text-primary"><input type="radio" name="task_type" value="" x-model="taskType" @change="syncQuestionType()" class="accent-brand"> Tất cả</label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary hover:text-text-primary"><input type="radio" name="task_type" value="task_1" x-model="taskType" @change="syncQuestionType()" class="accent-brand"> Task 1</label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary hover:text-text-primary"><input type="radio" name="task_type" value="task_2" x-model="taskType" @change="syncQuestionType()" class="accent-brand"> Task 2</label>
          </div>
        </div>

        <div>
          <p class="text-xs font-semibold text-text-disabled uppercase tracking-wide mb-2">Dạng bài</p>
          <template x-if="taskType">
            <select name="question_type" x-model="questionType" class="w-full bg-white border border-border-light rounded-lg text-sm text-text-secondary px-3 py-2 cursor-pointer">
              <option value="">Tất cả dạng bài</option>
              <template x-for="type in currentQuestionTypes" :key="type.value">
                <option :value="type.value" x-text="type.label"></option>
              </template>
            </select>
          </template>
          <template x-if="!taskType">
            <div>
              <input type="hidden" name="question_type" value="" />
              <div class="w-full bg-app-bg border border-border-light rounded-lg text-sm text-text-disabled px-3 py-2">Chọn Task để lọc dạng bài</div>
            </div>
          </template>
        </div>

        <div>
          <p class="text-xs font-semibold text-text-disabled uppercase tracking-wide mb-2">Quyền truy cập</p>
          <div class="space-y-1.5">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary"><input type="radio" name="access" value="" {{ empty($filters['access']) ? 'checked' : '' }} class="accent-brand"> Tất cả</label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary"><input type="radio" name="access" value="free" {{ ($filters['access'] ?? '') === 'free' ? 'checked' : '' }} class="accent-brand"> Miễn phí</label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-text-secondary"><input type="radio" name="access" value="pro" {{ in_array(($filters['access'] ?? ''), ['pro', 'premium'], true) ? 'checked' : '' }} class="accent-brand"> Pro</label>
          </div>
        </div>

        <div>
          <p class="text-xs font-semibold text-text-disabled uppercase tracking-wide mb-2">Sắp xếp</p>
          <select name="sort" class="w-full bg-white border border-border-light rounded-lg text-sm text-text-secondary px-3 py-2 cursor-pointer">
            <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="band_high" {{ in_array(($filters['sort'] ?? ''), ['band_high', 'band_desc'], true) ? 'selected' : '' }}>Band cao nhất</option>
            <option value="band_low" {{ in_array(($filters['sort'] ?? ''), ['band_low', 'band_asc'], true) ? 'selected' : '' }}>Band thấp nhất</option>
          </select>
        </div>

        <div class="flex items-end">
          <button type="submit" class="w-full py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Áp dụng</button>
        </div>
      </div>
    </form>
  </div>

  {{-- Lesson grid --}}
  @if($lessons->isEmpty())
    <div class="bg-white rounded-xl border border-border-light p-10 text-center">
      <p class="text-sm text-text-disabled">Không tìm thấy bài học phù hợp.</p>
    </div>
  @else
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4" x-data="lessonModePicker()" x-init="initModePicker()">
      @foreach($lessons as $lesson)
        @php
          $isPro = $lesson->is_premium;
          $userIsPro = auth()->user()->isPro();
          $locked = $isPro && !$userIsPro;
          $gradientColors = [
            'task_1' => 'from-green-50 to-brand-light',
            'task_2' => 'from-purple-50 to-purple-100',
          ];
          $gradient = $gradientColors[$lesson->task_type] ?? 'from-gray-50 to-gray-100';
          $taskLabel = $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2';
        @endphp
        <div class="lesson-card bg-white rounded-xl border border-border-light overflow-hidden {{ $locked ? 'opacity-80' : '' }}">
          <div class="h-28 bg-linear-to-br {{ $gradient }} flex items-center justify-center relative">
            <svg class="w-12 h-12 text-brand opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            @if($isPro)
              <div class="absolute top-2 right-2 flex items-center gap-1 px-1.5 py-0.5 bg-white rounded-full shadow-card">
                <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span class="text-xs font-bold text-text-primary">Pro</span>
              </div>
            @endif
          </div>
          <div class="p-4">
            <div class="flex items-center gap-2 mb-2">
              <span class="px-2 py-0.5 {{ $lesson->task_type === 'task_1' ? 'bg-brand-light text-brand' : 'bg-orange-50 text-orange-600' }} text-xs font-semibold rounded">{{ $taskLabel }}</span>
              @if($lesson->question_type)
                <span class="px-2 py-0.5 bg-app-bg text-text-secondary text-xs rounded">{{ \Illuminate\Support\Str::headline($lesson->question_type) }}</span>
              @endif
              <span class="ml-auto text-xs font-bold text-text-primary">{{ $lesson->band_score }}</span>
            </div>
            <h3 class="font-semibold text-sm text-text-primary leading-snug mb-1">{{ $lesson->title }}</h3>
            @if($lesson->description)
              <p class="text-xs text-text-secondary line-clamp-2">{{ $lesson->description }}</p>
            @endif
            <div class="mt-3">
              @if($locked)
                <a href="{{ route('client.checkout') }}" class="flex items-center justify-center gap-1.5 w-full py-2 bg-app-bg border border-border-light text-xs font-semibold text-text-secondary rounded-lg hover:bg-brand-light hover:text-brand hover:border-brand transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  Nâng cấp Pro để truy cập
                </a>
              @else
                <button @click="selectedLesson = {{ $lesson->id }}" class="flex-1 w-full py-2 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Chọn chế độ</button>
              @endif
            </div>
          </div>
        </div>
      @endforeach

      {{-- Mode selection modal --}}
      <div x-show="selectedLesson" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="selectedLesson = null" style="display:none">
        <div class="bg-white rounded-2xl shadow-float border border-border-light w-full max-w-md mx-4 p-6">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-text-primary">Chọn chế độ học</h3>
            <button @click="selectedLesson = null" class="text-text-disabled hover:text-text-secondary cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
          </div>
          <p x-show="isMobile" class="md:hidden text-xs text-semantic-red font-semibold mb-3">Vui lòng sử dụng Máy tính (PC/Laptop) để trải nghiệm chế độ này</p>
          <div class="space-y-3">
            <div class="hidden md:block" x-show="!isMobile">
              <a :href="buildLessonUrl(dictationUrlTemplate)" class="block p-4 bg-app-bg rounded-xl border border-border-light hover:border-brand hover:bg-brand-light transition-colors group">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-brand-light rounded-lg flex items-center justify-center group-hover:bg-brand transition-colors">
                    <svg class="w-5 h-5 text-brand group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </div>
                  <div>
                    <p class="font-semibold text-sm text-text-primary">Chép chính tả</p>
                    <p class="text-xs text-text-secondary">Rèn luyện kỹ năng viết, nhớ cấu trúc câu</p>
                  </div>
                </div>
              </a>
            </div>
            <div class="md:hidden" x-show="isMobile">
              <div class="block p-4 bg-app-bg rounded-xl border border-border-light opacity-55 cursor-not-allowed">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-brand-light rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </div>
                  <div>
                    <p class="font-semibold text-sm text-text-primary">Chép chính tả</p>
                    <p class="text-xs text-text-secondary">Rèn luyện kỹ năng viết, nhớ cấu trúc câu</p>
                  </div>
                </div>
              </div>
            </div>
            <a :href="buildLessonUrl(analyzeUrlTemplate)" class="block p-4 bg-app-bg rounded-xl border border-border-light hover:border-brand hover:bg-brand-light transition-colors group">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-semantic-blue transition-colors">
                  <svg class="w-5 h-5 text-semantic-blue group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                  <p class="font-semibold text-sm text-text-primary">Phân tích bài mẫu</p>
                  <p class="text-xs text-text-secondary">Xem phân tích Grammarly-style chi tiết</p>
                </div>
              </div>
            </a>
            @if(auth()->user()->isPro())
              <div class="hidden md:block" x-show="!isMobile">
                <a :href="buildLessonUrl(mockExamIntroUrlTemplate)" class="block p-4 bg-app-bg rounded-xl border border-border-light hover:border-brand hover:bg-brand-light transition-colors group">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-semantic-purple transition-colors">
                      <svg class="w-5 h-5 text-semantic-purple group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                      <p class="font-semibold text-sm text-text-primary">Thi thử chấm AI</p>
                      <p class="text-xs text-text-secondary">Viết bài và nhận điểm Band từ AI</p>
                    </div>
                  </div>
                </a>
              </div>
              <div class="md:hidden" x-show="isMobile">
                <div class="block p-4 bg-app-bg rounded-xl border border-border-light opacity-55 cursor-not-allowed">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-semantic-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                      <p class="font-semibold text-sm text-text-primary">Thi thử chấm AI</p>
                      <p class="text-xs text-text-secondary">Viết bài và nhận điểm Band từ AI</p>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-200 opacity-80">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  </div>
                  <div>
                    <p class="font-semibold text-sm text-text-primary">Thi thử chấm AI</p>
                    <p class="text-xs text-text-secondary">Nâng cấp Pro để sử dụng tính năng này</p>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6">{{ $lessons->withQueryString()->links() }}</div>
  @endif
</x-client.layout.dashboard>
