<x-admin.layout.app title="Sửa bài học" active="lessons">
    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.lessons.index') }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Bài học</a>
        <svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-text-primary font-medium truncate max-w-xs">{{ $lesson->title }}</span>
    </div>

    <div class="max-w-4xl bg-white border border-border-light rounded-xl p-5 sm:p-6 shadow-card">
        @include('admin.lessons.partials.form', [
            'action' => route('admin.lessons.update', $lesson),
            'method' => 'PUT',
            'lesson' => $lesson,
        ])
    </div>

    <div id="lesson-vocabulary-section" class="mt-6 max-w-4xl bg-white border border-border-light rounded-xl p-5 sm:p-6 shadow-card">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-semibold text-text-primary">Sổ tay từ vựng của bài học</h2>
                <p class="text-xs text-text-secondary mt-1">Quản lý danh sách từ vựng nổi bật sẽ hiển thị ở phía học viên.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-brand-light text-brand text-xs font-semibold">
                {{ $lesson->vocabularies->count() }} từ
            </span>
        </div>

        <form method="POST" action="{{ route('admin.lessons.vocabularies.store', $lesson) }}" class="rounded-xl border border-border-light bg-gray-50/40 p-4 mb-4">
            @csrf
            <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mb-3">Thêm từ vựng mới</p>
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-text-secondary mb-1">Word</label>
                    <input name="word" value="{{ old('word') }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="block text-xs text-text-secondary mb-1">Nghĩa tiếng Việt</label>
                    <input name="meaning_vi" value="{{ old('meaning_vi') }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" required />
                </div>
                <div>
                    <label class="block text-xs text-text-secondary mb-1">Nghĩa tiếng Anh <span class="text-text-disabled">(optional)</span></label>
                    <input name="meaning_en" value="{{ old('meaning_en') }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-text-secondary mb-1">Quyền truy cập</label>
                    <select name="access_tier" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer">
                        <option value="free" @selected(old('access_tier', 'free') === 'free')>Free</option>
                        <option value="pro" @selected(old('access_tier') === 'pro')>Pro</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-text-secondary mb-1">Ví dụ câu</label>
                    <textarea name="example_sentence" rows="2" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm">{{ old('example_sentence') }}</textarea>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Thêm từ vựng
                </button>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($lesson->vocabularies as $vocabulary)
                <div class="rounded-xl border border-border-light p-4">
                    <div class="flex flex-col lg:flex-row gap-3">
                        <form method="POST" action="{{ route('admin.lessons.vocabularies.update', [$lesson, $vocabulary]) }}" class="flex-1 grid sm:grid-cols-2 gap-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs text-text-secondary mb-1">Word</label>
                                <input name="word" value="{{ $vocabulary->word }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" required />
                            </div>
                            <div>
                                <label class="block text-xs text-text-secondary mb-1">Nghĩa tiếng Việt</label>
                                <input name="meaning_vi" value="{{ $vocabulary->meaning_vi }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" required />
                            </div>
                            <div>
                                <label class="block text-xs text-text-secondary mb-1">Nghĩa tiếng Anh</label>
                                <input name="meaning_en" value="{{ $vocabulary->meaning_en }}" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs text-text-secondary mb-1">Quyền truy cập</label>
                                <select name="access_tier" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer">
                                    <option value="free" @selected($vocabulary->access_tier === 'free')>Free</option>
                                    <option value="pro" @selected($vocabulary->access_tier === 'pro')>Pro</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-text-secondary mb-1">Ví dụ câu</label>
                                <textarea name="example_sentence" rows="2" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm">{{ $vocabulary->example_sentence }}</textarea>
                            </div>
                            <div class="sm:col-span-2 flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Cập nhật
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.lessons.vocabularies.destroy', [$lesson, $vocabulary]) }}" onsubmit="return confirm('Xóa từ vựng này khỏi bài học?')" class="flex lg:items-end">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Xóa
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-border-light p-6 text-center">
                    <p class="text-sm text-text-secondary">Bài học này chưa có từ vựng nào.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin.layout.app>
