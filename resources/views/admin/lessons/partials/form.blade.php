<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    {{-- Section: Thông tin cơ bản --}}
    <fieldset class="space-y-4">
        <legend class="text-xs font-bold text-text-secondary uppercase tracking-wider flex items-center gap-2 mb-1">
            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Thông tin cơ bản
        </legend>

        {{-- Title --}}
        <div>
            <label for="title" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Tiêu đề bài học</label>
            <input id="title" name="title" value="{{ old('title', $lesson?->title) }}" placeholder="VD: Task 1 - Line Graph Energy Consumption" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm transition-all" required />
        </div>

        {{-- Task type & Question type --}}
        <div class="grid md:grid-cols-2 gap-4" x-data="{
            taskType: '{{ old('task_type', $lesson?->task_type ?? 'task_1') }}',
            questionType: '{{ old('question_type', $lesson?->question_type ?? '') }}',
            task1Types: [
                { value: 'line_graph', label: 'Line Graph' },
                { value: 'bar_chart', label: 'Bar Chart' },
                { value: 'pie_chart', label: 'Pie Chart' },
                { value: 'table', label: 'Table' },
                { value: 'mixed_chart', label: 'Mixed / Combined Charts' },
                { value: 'process_diagram', label: 'Process Diagram' },
                { value: 'map', label: 'Map' },
                { value: 'flowchart', label: 'Flowchart' },
            ],
            task2Types: [
                { value: 'opinion', label: 'Opinion (Agree / Disagree)' },
                { value: 'discussion', label: 'Discussion (Discuss Both Views)' },
                { value: 'problem_solution', label: 'Problem & Solution' },
                { value: 'advantages_disadvantages', label: 'Advantages & Disadvantages' },
                { value: 'two_part', label: 'Two-part Question' },
                { value: 'causes_effects', label: 'Causes & Effects' },
                { value: 'positive_negative', label: 'Positive or Negative Development' },
            ],
            get currentTypes() { return this.taskType === 'task_1' ? this.task1Types : this.task2Types; },
            onTaskChange() {
                const valid = this.currentTypes.some(t => t.value === this.questionType);
                if (!valid) this.questionType = '';
            }
        }">
            <div>
                <label for="task_type" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Task type</label>
                <select id="task_type" name="task_type" x-model="taskType" @change="onTaskChange()" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all" required>
                    <option value="task_1">Task 1</option>
                    <option value="task_2">Task 2</option>
                </select>
            </div>
            <div>
                <label for="question_type" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Question type</label>
                <select id="question_type" name="question_type" x-model="questionType" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all">
                    <option value="">— Chọn dạng bài —</option>
                    <template x-for="type in currentTypes" :key="type.value">
                        <option :value="type.value" x-text="type.label" :selected="type.value === questionType"></option>
                    </template>
                </select>
            </div>
        </div>
    </fieldset>

    <hr class="border-border-light" />

    {{-- Section: Nội dung đề bài --}}
    <fieldset class="space-y-4">
        <legend class="text-xs font-bold text-text-secondary uppercase tracking-wider flex items-center gap-2 mb-1">
            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Nội dung đề bài
        </legend>

        {{-- Prompt --}}
        <div>
            <label for="prompt_text" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Đề bài (Prompt)</label>
            <textarea id="prompt_text" name="prompt_text" rows="4" placeholder="Nhập nội dung đề bài IELTS..." class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm transition-all leading-relaxed" required>{{ old('prompt_text', $lesson?->prompt_text) }}</textarea>
        </div>

    {{-- Image Upload --}}
    <div x-data="imageUpload()" x-init="init()">
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Hình ảnh đề bài <span class="font-normal text-text-disabled">(tuỳ chọn)</span></label>

        {{-- Current image preview --}}
        @if($lesson?->image_path)
            <div x-show="!removed && !newPreview" class="mb-3 relative inline-block group">
                <img src="{{ Storage::disk('public')->url($lesson->image_path) }}" alt="Ảnh đề bài" class="max-h-40 rounded-lg border border-border-light shadow-sm" />
                <button type="button" @click="removeExisting()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-colors cursor-pointer opacity-0 group-hover:opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <p class="text-xs text-text-disabled mt-1">Di chuột vào ảnh để xóa</p>
            </div>
            <input type="hidden" name="remove_image" :value="removed ? '1' : '0'" />
        @endif

        {{-- New image preview --}}
        <div x-show="newPreview" class="mb-3 relative inline-block group">
            <img :src="newPreview" alt="Ảnh mới" class="max-h-40 rounded-lg border border-brand shadow-sm" />
            <button type="button" @click="clearNew()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Drop zone --}}
        <div x-show="!newPreview"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="handleDrop($event)"
             :class="dragging ? 'border-brand bg-brand-light' : 'border-border-light bg-gray-50/50'"
             class="relative border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer hover:border-brand/50"
             @click="$refs.fileInput.click()">
            <input type="file" name="image" accept="image/*" x-ref="fileInput" @change="handleFile($event)" class="hidden" />
            <svg class="w-8 h-8 mx-auto text-text-disabled mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
            <p class="text-sm text-text-secondary">Kéo thả ảnh vào đây hoặc <span class="text-brand font-medium">nhấn để chọn</span></p>
            <p class="text-xs text-text-disabled mt-1">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
        </div>
    </div>

    <script>
        function imageUpload() {
            return {
                removed: false,
                dragging: false,
                newPreview: null,
                init() {},
                removeExisting() {
                    this.removed = true;
                },
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        this.newPreview = URL.createObjectURL(file);
                        this.removed = false;
                    }
                },
                handleDrop(e) {
                    this.dragging = false;
                    const file = e.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        // Set the file to the hidden input
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        this.$refs.fileInput.files = dt.files;
                        this.newPreview = URL.createObjectURL(file);
                        this.removed = false;
                    }
                },
                clearNew() {
                    this.newPreview = null;
                    this.$refs.fileInput.value = '';
                }
            }
        }
    </script>

    </fieldset>

    <hr class="border-border-light" />

    {{-- Section: Bài mẫu & điểm --}}
    <fieldset class="space-y-4">
        <legend class="text-xs font-bold text-text-secondary uppercase tracking-wider flex items-center gap-2 mb-1">
            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Bài mẫu & Điểm số
        </legend>

        {{-- Sample essay --}}
        <div>
            <label for="sample_essay" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Bài mẫu (Sample Essay)</label>
            <textarea id="sample_essay" name="sample_essay" rows="10" placeholder="Nhập nội dung bài mẫu..." class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm transition-all leading-relaxed font-mono" required>{{ old('sample_essay', $lesson?->sample_essay) }}</textarea>
            <p class="text-xs text-text-disabled mt-1">Bài mẫu sẽ được sử dụng cho chế độ Chép chính tả và Đọc & Phân tích.</p>
        </div>

        {{-- Band scores --}}
        <div>
            <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-2">Điểm số</p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label for="band_score" class="block text-xs text-text-secondary mb-1">Band Overall</label>
                    <input id="band_score" type="number" step="0.5" min="0" max="9" name="band_score" value="{{ old('band_score', $lesson?->band_score) }}" placeholder="0-9" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm text-center transition-all" />
                </div>
                <div>
                    <label for="tr_score" class="block text-xs text-text-secondary mb-1">TR / TA</label>
                    <input id="tr_score" type="number" step="0.5" min="0" max="9" name="tr_score" value="{{ old('tr_score', $lesson?->tr_score) }}" placeholder="0-9" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm text-center transition-all" />
                </div>
                <div>
                    <label for="cc_score" class="block text-xs text-text-secondary mb-1">CC</label>
                    <input id="cc_score" type="number" step="0.5" min="0" max="9" name="cc_score" value="{{ old('cc_score', $lesson?->cc_score) }}" placeholder="0-9" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm text-center transition-all" />
                </div>
                <div>
                    <label for="lr_score" class="block text-xs text-text-secondary mb-1">LR</label>
                    <input id="lr_score" type="number" step="0.5" min="0" max="9" name="lr_score" value="{{ old('lr_score', $lesson?->lr_score) }}" placeholder="0-9" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm text-center transition-all" />
                </div>
                <div>
                    <label for="gra_score" class="block text-xs text-text-secondary mb-1">GRA</label>
                    <input id="gra_score" type="number" step="0.5" min="0" max="9" name="gra_score" value="{{ old('gra_score', $lesson?->gra_score) }}" placeholder="0-9" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm text-center transition-all" />
                </div>
            </div>
        </div>
    </fieldset>

    <hr class="border-border-light" />

    {{-- Section: Phát hành --}}
    <fieldset class="space-y-4">
        <legend class="text-xs font-bold text-text-secondary uppercase tracking-wider flex items-center gap-2 mb-1">
            <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Phát hành
        </legend>

        {{-- Premium & Status --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="is_premium" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Phân loại truy cập</label>
                <select id="is_premium" name="is_premium" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all" required>
                    <option value="0" @selected((string) old('is_premium', (int) ($lesson?->is_premium ?? 0)) === '0')>Free — Miễn phí</option>
                    <option value="1" @selected((string) old('is_premium', (int) ($lesson?->is_premium ?? 0)) === '1')>Pro — Trả phí</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Trạng thái</label>
                <select id="status" name="status" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all" required>
                    <option value="draft" @selected(old('status', $lesson?->status ?? 'draft') === 'draft')>Draft — Bản nháp</option>
                    <option value="published" @selected(old('status', $lesson?->status) === 'published')>Published — Đã xuất bản</option>
                </select>
            </div>
        </div>
    </fieldset>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-4 border-t border-border-light">
        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Lưu bài học
        </button>
        <a href="{{ route('admin.lessons.index') }}" class="px-4 py-2.5 border border-border-light rounded-lg text-sm text-text-secondary hover:bg-gray-50 transition-colors cursor-pointer">Hủy</a>
    </div>
</form>
