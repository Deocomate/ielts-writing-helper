<x-client.layout.dashboard :title="$exercise->title" activePage="lessons">
  <x-slot:head>
    <script>
      function miniExerciseRoom(payload, type) {
        return {
          type,
          payload,
          selected: '',
          dropped: [],
          shortAnswer: '',
          checked: false,
          isCorrect: false,
          normalize(value) {
            return String(value || '').trim().replace(/\s+/g, ' ').toLowerCase();
          },
          checkFillBlank(option) {
            this.selected = option;
            this.checked = true;
            this.isCorrect = this.normalize(option) === this.normalize(this.payload.answers[0]);
          },
          chooseDrop(option) {
            if (this.dropped.includes(option)) {
              this.dropped = this.dropped.filter((item) => item !== option);
              this.checked = false;
              return;
            }
            if (this.dropped.length < this.payload.answers.length) {
              this.dropped.push(option);
            }
            this.checked = false;
          },
          checkDragDrop() {
            this.checked = true;
            this.isCorrect = this.payload.answers.every((answer, index) => this.normalize(answer) === this.normalize(this.dropped[index]));
          },
          checkShortAnswer() {
            this.checked = true;
            this.isCorrect = this.payload.answers.some((answer) => this.normalize(answer) === this.normalize(this.shortAnswer));
          },
          reset() {
            this.selected = '';
            this.dropped = [];
            this.shortAnswer = '';
            this.checked = false;
            this.isCorrect = false;
          },
        };
      }
    </script>
  </x-slot:head>

  <div class="max-w-3xl mx-auto">
    <div class="mb-5 flex items-center gap-2 text-sm">
      <a href="{{ route('client.lessons.library', ['tab' => 'exercises']) }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Trạm sửa lỗi</a>
      <svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      <span class="text-text-primary font-medium truncate">{{ $exercise->title }}</span>
    </div>

    <div
      class="bg-white border border-border-light rounded-2xl shadow-card p-6 sm:p-8"
      x-data="miniExerciseRoom(@js($exercise->question_data), @js($exercise->exercise_type))"
    >
      <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="px-2.5 py-1 bg-purple-50 text-semantic-purple text-xs font-semibold rounded-full">{{ Str::headline($exercise->mistake_type) }}</span>
        <span class="px-2.5 py-1 bg-brand-light text-brand text-xs font-semibold rounded-full">{{ Str::headline($exercise->exercise_type) }}</span>
        <span class="px-2.5 py-1 bg-app-bg text-text-secondary text-xs font-semibold rounded-full">{{ Str::headline($exercise->difficulty_level) }}</span>
      </div>

      <h1 class="text-2xl font-bold text-text-primary leading-tight">{{ $exercise->title }}</h1>

      <div class="mt-7">
        <template x-if="type === 'fill_blank'">
          <div>
            <p class="text-lg text-text-primary leading-relaxed" x-text="payload.sentence"></p>
            <div class="mt-5 flex flex-wrap gap-2">
              <template x-for="option in payload.options" :key="option">
                <button
                  type="button"
                  @click="checkFillBlank(option)"
                  class="px-4 py-2 rounded-lg border text-sm font-semibold transition-colors cursor-pointer"
                  :class="selected === option ? (isCorrect ? 'bg-brand text-white border-brand' : 'bg-red-50 text-semantic-red border-red-200') : 'bg-white text-text-secondary border-border-light hover:border-brand hover:text-brand'"
                  x-text="option"
                ></button>
              </template>
            </div>
          </div>
        </template>

        <template x-if="type === 'drag_drop'">
          <div>
            <p class="text-sm text-text-secondary mb-3">Chọn các đáp án theo đúng thứ tự ô trống.</p>
            <p class="text-lg text-text-primary leading-relaxed" x-text="payload.sentence"></p>
            <div class="mt-4 grid gap-2">
              <template x-for="(answer, index) in payload.answers" :key="index">
                <div class="min-h-11 rounded-lg border border-dashed border-border-light bg-app-bg px-3 py-2 text-sm flex items-center">
                  <span class="text-text-disabled mr-2" x-text="`Ô ${index + 1}:`"></span>
                  <span class="font-semibold text-text-primary" x-text="dropped[index] || 'Chưa chọn'"></span>
                </div>
              </template>
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
              <template x-for="option in payload.options" :key="option">
                <button
                  type="button"
                  @click="chooseDrop(option)"
                  class="px-4 py-2 rounded-lg border text-sm font-semibold transition-colors cursor-pointer"
                  :class="dropped.includes(option) ? 'bg-brand-light text-brand border-brand' : 'bg-white text-text-secondary border-border-light hover:border-brand hover:text-brand'"
                  x-text="option"
                ></button>
              </template>
            </div>
            <button type="button" @click="checkDragDrop()" class="mt-5 inline-flex items-center px-5 py-2.5 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark cursor-pointer">Kiểm tra</button>
          </div>
        </template>

        <template x-if="type === 'short_answer'">
          <div>
            <p class="text-lg text-text-primary leading-relaxed" x-text="payload.prompt"></p>
            <div class="mt-5">
              <label for="short-answer" class="block text-sm font-semibold text-text-primary mb-1.5">Câu trả lời</label>
              <input id="short-answer" x-model="shortAnswer" @keydown.enter.prevent="checkShortAnswer()" class="w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-4 focus:ring-emerald-100" />
              <button type="button" @click="checkShortAnswer()" class="mt-3 inline-flex items-center px-5 py-2.5 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark cursor-pointer">Kiểm tra</button>
            </div>
          </div>
        </template>
      </div>

      <div x-show="checked" x-transition class="mt-6 rounded-xl border p-4" :class="isCorrect ? 'bg-brand-light border-brand/20' : 'bg-red-50 border-red-200'" x-cloak>
        <p class="text-sm font-bold" :class="isCorrect ? 'text-brand' : 'text-semantic-red'" x-text="isCorrect ? 'Chính xác' : 'Chưa đúng'"></p>
        <p class="text-sm text-text-secondary leading-relaxed mt-2">{{ $exercise->explanation }}</p>
      </div>

      <div class="mt-6 flex items-center justify-between gap-3 border-t border-border-light pt-5">
        <button type="button" @click="reset()" class="px-4 py-2.5 border border-border-light rounded-lg text-sm text-text-secondary hover:bg-app-bg transition-colors cursor-pointer">Làm lại</button>
        <a href="{{ route('client.lessons.library', ['tab' => 'exercises']) }}" class="text-sm font-semibold text-brand hover:text-brand-dark">Quay lại thư viện</a>
      </div>
    </div>
  </div>
</x-client.layout.dashboard>
