<x-admin.layout.app title="Mapping Annotations" active="lessons">
    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.lessons.index') }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Bài học</a>
        <svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-text-primary font-medium truncate max-w-xs">{{ $lesson->title }}</span>
        <svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-text-primary font-medium">Mapping</span>
    </div>

    {{-- Legend + Instructions --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
        <span class="text-text-secondary font-medium">Chú thích:</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-emerald-500 rounded"></span> Vocabulary</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-purple-500 rounded"></span> Grammar</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-blue-500 rounded"></span> Coherence</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-orange-500 rounded"></span> Logic</span>
        <span class="ml-auto text-text-disabled">Bôi đen text để tạo annotation &bull; Click annotation để sửa/xóa</span>
    </div>

    <div x-data="mappingEditor()" x-init="init()" class="grid lg:grid-cols-5 gap-6">
        {{-- Left: Question + Essay with highlighted annotations (3 cols) --}}
        <div class="lg:col-span-3 space-y-5">
            {{-- Question / Prompt section --}}
            <div class="bg-white border border-border-light rounded-xl shadow-card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border-light">
                    <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Đề bài
                        <span class="text-xs font-normal text-text-disabled ml-1">{{ ucfirst(str_replace('_', ' ', $lesson->task_type)) }}</span>
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    @if($lesson->image_path)
                        <div class="rounded-lg overflow-hidden border border-border-light bg-gray-50">
                            <img src="{{ Storage::disk('public')->url($lesson->image_path) }}" alt="Ảnh đề bài" class="w-full max-h-[300px] object-contain" />
                        </div>
                    @endif
                    <div class="text-sm text-text-primary leading-relaxed whitespace-pre-line">{{ $lesson->prompt_text }}</div>
                </div>
            </div>

            {{-- Essay with annotations --}}
            <div class="bg-white border border-border-light rounded-xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-border-light flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Bài mẫu
                        @if($lesson->band_score)
                            <span class="ml-1 px-1.5 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 rounded">Band {{ $lesson->band_score }}</span>
                        @endif
                    </h2>
                    <span class="text-xs text-text-disabled" x-text="annotations.length + ' annotations'"></span>
                </div>
                <div class="p-5">
                    {{-- Essay content: NO whitespace around {!! !!} to prevent offset mismatch --}}
                    <div class="text-sm text-text-primary leading-[1.9] select-text essay-content" id="essay-content" @mouseup="onTextSelected($event)">{!! $essayHtml ?? '' !!}</div>
                </div>
            </div>
        </div>

        {{-- Right: Annotation form + list (2 cols) --}}
        <div class="lg:col-span-2 lg:sticky lg:top-4 lg:self-start space-y-5 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto lg:pr-1" style="scrollbar-width: thin;">
            {{-- Add / Edit annotation form --}}
            <div class="bg-white border border-border-light rounded-xl shadow-card overflow-hidden transition-all"
                 :class="editingId ? 'ring-2 ring-brand/30' : ''">
                <div class="px-5 py-3.5 border-b border-border-light">
                    <h2 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                        <template x-if="!editingId">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Thêm annotation
                            </span>
                        </template>
                        <template x-if="editingId">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Sửa annotation
                            </span>
                        </template>
                    </h2>
                </div>
                <div class="p-5">
                    <form :action="formAction" method="POST" class="space-y-4" id="annotation-form">
                        @csrf
                        <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'" />

                        {{-- Selected text preview --}}
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Đoạn text đã chọn</label>
                            <div class="relative">
                                <div x-show="formData.selected_text"
                                     class="min-h-[40px] px-3.5 py-2.5 bg-gray-50 border border-border-light rounded-lg text-sm text-text-primary leading-relaxed">
                                    <span x-text="formData.selected_text" class="font-medium"></span>
                                </div>
                                <div x-show="!formData.selected_text"
                                     class="min-h-[40px] px-3.5 py-2.5 bg-gray-50 border border-dashed border-border-light rounded-lg text-sm text-text-disabled flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59"/></svg>
                                    Bôi đen text từ bài mẫu bên trái...
                                </div>
                                <input type="hidden" name="selected_text" :value="formData.selected_text" />
                                <input type="hidden" name="start_offset" :value="formData.start_offset" />
                                <input type="hidden" name="end_offset" :value="formData.end_offset" />
                            </div>
                            {{-- Offset info + verification badge --}}
                            <div x-show="formData.selected_text" class="mt-1.5 flex items-center gap-2 text-[11px]">
                                <span class="text-text-disabled">
                                    Offset: <span x-text="formData.start_offset"></span>–<span x-text="formData.end_offset"></span>
                                    (<span x-text="formData.end_offset - formData.start_offset"></span> ký tự)
                                </span>
                                <span x-show="offsetVerified" class="inline-flex items-center gap-0.5 text-emerald-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Đã xác minh
                                </span>
                                <span x-show="!offsetVerified && formData.selected_text" class="inline-flex items-center gap-0.5 text-amber-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Kiểm tra lại
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Loại thẻ</label>
                                <select name="tag_type" x-model="formData.tag_type" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all" required>
                                    <option value="vocabulary">Vocabulary</option>
                                    <option value="grammar">Grammar</option>
                                    <option value="coherence">Coherence</option>
                                    <option value="logic">Logic</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Quyền truy cập</label>
                                <select name="access_tier" x-model="formData.access_tier" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer transition-all" required>
                                    <option value="free">Free</option>
                                    <option value="pro">Pro</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Nội dung giải thích</label>
                            <textarea name="explanation" x-model="formData.explanation" rows="4" placeholder="Giải thích chi tiết về đoạn text đã chọn..." class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm transition-all leading-relaxed" required></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" :disabled="!formData.selected_text || !offsetVerified"
                                    :class="(formData.selected_text && offsetVerified) ? 'bg-brand hover:bg-brand-dark' : 'bg-gray-300 cursor-not-allowed'"
                                    class="inline-flex items-center gap-1.5 text-white rounded-lg px-4 py-2.5 text-sm font-medium transition-colors cursor-pointer shadow-sm">
                                <template x-if="!editingId">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Thêm
                                    </span>
                                </template>
                                <template x-if="editingId">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Cập nhật
                                    </span>
                                </template>
                            </button>
                            <button x-show="editingId" type="button" @click="cancelEdit()"
                                    class="px-4 py-2.5 border border-border-light rounded-lg text-sm text-text-secondary hover:bg-gray-50 transition-colors cursor-pointer">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Annotation list --}}
            <div class="bg-white border border-border-light rounded-xl shadow-card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border-light flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-text-primary">Danh sách annotations</h2>
                    <span class="text-xs text-text-disabled" x-text="annotations.length + ' mục'"></span>
                </div>
                <div class="divide-y divide-border-light max-h-[600px] overflow-y-auto">
                    @forelse($lesson->annotations->sortBy('start_offset') as $annotation)
                        @php
                            $tagColors = [
                                'vocabulary' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'border' => 'border-emerald-200'],
                                'grammar'    => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500', 'border' => 'border-purple-200'],
                                'coherence'  => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500', 'border' => 'border-blue-200'],
                                'logic'      => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500', 'border' => 'border-orange-200'],
                            ];
                            $tc = $tagColors[$annotation->tag_type] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500', 'border' => 'border-gray-200'];
                        @endphp
                        <div class="px-5 py-3 group hover:bg-gray-50/50 transition-colors cursor-pointer border-l-2 {{ $tc['border'] }}"
                             @click="startEdit({{ $annotation->id }}, {{ json_encode([
                                'selected_text' => $annotation->selected_text,
                                'start_offset' => $annotation->start_offset,
                                'end_offset' => $annotation->end_offset,
                                'tag_type' => $annotation->tag_type,
                                'access_tier' => $annotation->access_tier,
                                'explanation' => $annotation->explanation,
                             ]) }})"
                             @mouseenter="highlightAnnotation({{ $annotation->id }})"
                             @mouseleave="unhighlightAnnotation({{ $annotation->id }})">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-text-primary truncate">&ldquo;{{ Str::limit($annotation->selected_text, 60) }}&rdquo;</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-medium {{ $tc['bg'] }} {{ $tc['text'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $tc['dot'] }}"></span>
                                            {{ ucfirst($annotation->tag_type) }}
                                        </span>
                                        @if($annotation->access_tier === 'pro')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700">Pro</span>
                                        @endif
                                        <span class="text-[11px] text-text-disabled font-mono">{{ $annotation->start_offset }}–{{ $annotation->end_offset }}</span>
                                    </div>
                                    <p class="text-xs text-text-secondary mt-1 line-clamp-2 leading-relaxed">{{ $annotation->explanation }}</p>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                    <button type="button" @click.stop="startEdit({{ $annotation->id }}, {{ json_encode([
                                        'selected_text' => $annotation->selected_text,
                                        'start_offset' => $annotation->start_offset,
                                        'end_offset' => $annotation->end_offset,
                                        'tag_type' => $annotation->tag_type,
                                        'access_tier' => $annotation->access_tier,
                                        'explanation' => $annotation->explanation,
                                    ]) }})" class="p-1.5 rounded-md text-text-disabled hover:text-blue-600 hover:bg-blue-50 transition-all" title="Sửa">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.lessons.annotations.destroy', [$lesson, $annotation]) }}" @click.stop onsubmit="return confirm('Xác nhận xóa annotation này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-md text-text-disabled hover:text-red-500 hover:bg-red-50 transition-all cursor-pointer" title="Xóa">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <svg class="w-10 h-10 text-text-disabled mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59"/></svg>
                            <p class="text-sm text-text-secondary">Bôi đen text từ bài mẫu bên trái để bắt đầu mapping.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Mapping editor styles --}}
    <style>
        .essay-content { word-break: break-word; }
        .annotation-highlight {
            border-bottom: 2px solid;
            padding-bottom: 1px;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
            border-radius: 2px;
        }
        .annotation-highlight:hover,
        .annotation-highlight.active {
            padding: 1px 2px;
            border-radius: 3px;
        }
        .annotation-highlight[data-tag="vocabulary"] { border-color: #10b981; }
        .annotation-highlight[data-tag="vocabulary"]:hover,
        .annotation-highlight[data-tag="vocabulary"].active { background-color: #d1fae5; }
        .annotation-highlight[data-tag="grammar"] { border-color: #a855f7; }
        .annotation-highlight[data-tag="grammar"]:hover,
        .annotation-highlight[data-tag="grammar"].active { background-color: #f3e8ff; }
        .annotation-highlight[data-tag="coherence"] { border-color: #3b82f6; }
        .annotation-highlight[data-tag="coherence"]:hover,
        .annotation-highlight[data-tag="coherence"].active { background-color: #dbeafe; }
        .annotation-highlight[data-tag="logic"] { border-color: #f97316; }
        .annotation-highlight[data-tag="logic"]:hover,
        .annotation-highlight[data-tag="logic"].active { background-color: #ffedd5; }

        .annotation-highlight.highlighted {
            padding: 1px 2px;
            border-radius: 3px;
            filter: brightness(0.95);
        }
        .annotation-highlight[data-tag="vocabulary"].highlighted { background-color: #a7f3d0; }
        .annotation-highlight[data-tag="grammar"].highlighted { background-color: #e9d5ff; }
        .annotation-highlight[data-tag="coherence"].highlighted { background-color: #bfdbfe; }
        .annotation-highlight[data-tag="logic"].highlighted { background-color: #fed7aa; }

        .annotation-highlight[title] { position: relative; }

        /* Selection highlight style */
        #essay-content ::selection { background-color: #fef08a; }
        #essay-content ::-moz-selection { background-color: #fef08a; }
    </style>

    {{-- Mapping editor JS --}}
    <script>
        function mappingEditor() {
            const lessonId = {{ $lesson->id }};
            const storeUrl = "{{ route('admin.lessons.annotations.store', $lesson) }}";
            const updateUrlTemplate = @json(route('admin.lessons.annotations.update', ['lesson' => $lesson->id, 'annotation' => '__ANNOTATION__']));
            // Use normalized essay text (consistent \n line endings) from controller
            const essayText = @json($normalizedEssay);

            return {
                annotations: @json($lesson->annotations->sortBy('start_offset')->values()),
                editingId: null,
                offsetVerified: false,
                formData: {
                    selected_text: '',
                    start_offset: null,
                    end_offset: null,
                    tag_type: 'vocabulary',
                    access_tier: 'free',
                    explanation: '',
                },

                get formAction() {
                    return this.editingId
                        ? updateUrlTemplate.replace('__ANNOTATION__', String(this.editingId))
                        : storeUrl;
                },

                init() {
                    // Nothing extra needed — essay HTML is server-rendered
                },

                /**
                 * Handle text selection on the essay.
                 * Calculates offset relative to plain text, then verifies it
                 * against the raw essay string for accuracy.
                 */
                onTextSelected(event) {
                    const selection = window.getSelection();
                    const rawSelectedText = selection.toString();
                    if (!rawSelectedText || rawSelectedText.length < 1) return;

                    const essayEl = document.getElementById('essay-content');
                    const range = selection.getRangeAt(0);

                    // Ensure selection is within the essay element
                    if (!essayEl.contains(range.startContainer) || !essayEl.contains(range.endContainer)) return;

                    // Calculate offset relative to plain text via DOM tree walker
                    const domStartOffset = this.getTextOffset(essayEl, range.startContainer, range.startOffset);
                    const domEndOffset = this.getTextOffset(essayEl, range.endContainer, range.endOffset);

                    const selectionPayload = this.prepareSelectionPayload(rawSelectedText, domStartOffset, domEndOffset);
                    if (!selectionPayload) {
                        return;
                    }

                    let startOffset = selectionPayload.start;
                    let endOffset = selectionPayload.end;
                    let selectedText = selectionPayload.text;

                    // Verify: check if the essay text at computed offset matches the selection
                    const verified = this.verifyAndCorrectOffset(selectedText, startOffset, endOffset);
                    startOffset = verified.start;
                    endOffset = verified.end;
                    this.offsetVerified = verified.ok;
                    selectedText = verified.selectedText;

                    // Only set if within essay bounds and verified
                    if (startOffset >= 0 && endOffset <= essayText.length) {
                        if (this.editingId) this.cancelEdit();

                        this.formData.selected_text = selectedText;
                        this.formData.start_offset = startOffset;
                        this.formData.end_offset = endOffset;

                        document.getElementById('annotation-form')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                },

                /**
                 * Normalize selected text by trimming surrounding whitespace,
                 * while keeping offset alignment consistent.
                 */
                prepareSelectionPayload(rawSelectedText, startOffset, endOffset) {
                    const normalized = rawSelectedText
                        .replace(/\r/g, '')
                        .replace(/\u00A0/g, ' ');

                    const leadingWhitespace = (normalized.match(/^\s*/) || [''])[0].length;
                    const trailingWhitespace = (normalized.match(/\s*$/) || [''])[0].length;
                    const coreLength = normalized.length - leadingWhitespace - trailingWhitespace;

                    if (coreLength <= 0) {
                        return null;
                    }

                    const coreText = normalized.substring(leadingWhitespace, leadingWhitespace + coreLength);

                    return {
                        text: coreText,
                        start: startOffset + leadingWhitespace,
                        end: endOffset - trailingWhitespace,
                    };
                },

                /**
                 * Walk text nodes to compute character offset from root to target node+offset.
                 */
                getTextOffset(root, targetNode, targetOffset) {
                    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
                    let offset = 0;
                    let node;
                    while (node = walker.nextNode()) {
                        if (node === targetNode) {
                            return offset + targetOffset;
                        }
                        offset += node.textContent.length;
                    }
                    return offset;
                },

                /**
                 * Verify that essayText.substring(start, end) matches the selected text.
                 * If not, search nearby for the correct position (fallback).
                 */
                verifyAndCorrectOffset(selectedText, start, end) {
                    const normalizedSelected = selectedText.replace(/\r/g, '');
                    if (!normalizedSelected) {
                        return { start, end, ok: false, selectedText: '' };
                    }

                    // Direct match check
                    const directSlice = essayText.substring(start, start + normalizedSelected.length);
                    if (directSlice === normalizedSelected) {
                        return { start, end: start + normalizedSelected.length, ok: true, selectedText: normalizedSelected };
                    }

                    // Fallback: search nearby and pick closest match to original offset
                    const nearbyMatches = this.findOccurrences(normalizedSelected, start, 140);
                    const nearbyMatch = this.pickBestMatch(nearbyMatches, start);
                    if (nearbyMatch !== null) {
                        return {
                            start: nearbyMatch,
                            end: nearbyMatch + normalizedSelected.length,
                            ok: true,
                            selectedText: normalizedSelected,
                        };
                    }

                    // Last resort: global search
                    const globalMatches = this.findOccurrences(normalizedSelected, start, essayText.length);
                    const globalIdx = this.pickBestMatch(globalMatches, start);
                    if (globalIdx !== null) {
                        return {
                            start: globalIdx,
                            end: globalIdx + normalizedSelected.length,
                            ok: true,
                            selectedText: normalizedSelected,
                        };
                    }

                    // Could not verify — use original values (flag as unverified)
                    return { start, end, ok: false, selectedText: normalizedSelected };
                },

                /**
                 * Find nearest occurrence of a text around expected offset.
                 */
                findOccurrences(targetText, expectedStart, radius) {
                    const searchStart = Math.max(0, expectedStart - radius);
                    const searchEnd = Math.min(essayText.length, expectedStart + radius + targetText.length);
                    const searchRegion = essayText.substring(searchStart, searchEnd);

                    let localIndex = searchRegion.indexOf(targetText);
                    const matches = [];

                    while (localIndex !== -1) {
                        const absoluteIndex = searchStart + localIndex;
                        matches.push(absoluteIndex);

                        localIndex = searchRegion.indexOf(targetText, localIndex + 1);
                    }

                    return matches;
                },

                /**
                 * Pick best matching position. If top candidates are too close,
                 * treat as ambiguous and require admin to reselect.
                 */
                pickBestMatch(matches, expectedStart) {
                    if (!matches.length) {
                        return null;
                    }

                    const ranked = [...matches].sort((left, right) => {
                        return Math.abs(left - expectedStart) - Math.abs(right - expectedStart);
                    });

                    if (ranked.length > 1) {
                        const bestDistance = Math.abs(ranked[0] - expectedStart);
                        const secondDistance = Math.abs(ranked[1] - expectedStart);
                        if (Math.abs(secondDistance - bestDistance) <= 2) {
                            return null;
                        }
                    }

                    return ranked[0];
                },

                startEdit(id, data) {
                    this.editingId = id;
                    this.formData = { ...data };

                    // Verify the stored offset
                    const verified = this.verifyAndCorrectOffset(
                        data.selected_text,
                        data.start_offset,
                        data.end_offset
                    );
                    this.offsetVerified = verified.ok;
                    this.formData.selected_text = verified.selectedText || data.selected_text;
                    this.formData.start_offset = verified.start;
                    this.formData.end_offset = verified.end;

                    // Highlight the annotation in the essay
                    document.querySelectorAll('.annotation-highlight').forEach(el => el.classList.remove('active'));
                    const el = document.querySelector(`[data-annotation-id="${id}"]`);
                    if (el) {
                        el.classList.add('active');
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    document.getElementById('annotation-form')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                },

                cancelEdit() {
                    this.editingId = null;
                    this.offsetVerified = false;
                    this.formData = {
                        selected_text: '',
                        start_offset: null,
                        end_offset: null,
                        tag_type: 'vocabulary',
                        access_tier: 'free',
                        explanation: '',
                    };
                    document.querySelectorAll('.annotation-highlight.active').forEach(el => el.classList.remove('active'));
                },

                highlightAnnotation(id) {
                    const el = document.querySelector(`[data-annotation-id="${id}"]`);
                    if (el) el.classList.add('highlighted');
                },

                unhighlightAnnotation(id) {
                    const el = document.querySelector(`[data-annotation-id="${id}"]`);
                    if (el) el.classList.remove('highlighted');
                },
            };
        }
    </script>
</x-admin.layout.app>
