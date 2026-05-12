<x-admin.layout.app title="Quản lý bài học" active="lessons">
    {{-- Page header with filter & actions --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm bài học..." class="input-admin pl-9 pr-3 py-2 text-sm border border-border-light rounded-lg w-56 transition-all" />
            </div>
            <select name="task_type" class="input-admin border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer" onchange="this.form.submit()">
                <option value="">Tất cả task</option>
                <option value="task_1" @selected(($filters['task_type'] ?? '') === 'task_1')>Task 1</option>
                <option value="task_2" @selected(($filters['task_type'] ?? '') === 'task_2')>Task 2</option>
            </select>
            <select name="status" class="input-admin border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 text-text-primary rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">Lọc</button>
        </form>
        <a href="{{ route('admin.lessons.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-dark transition-colors cursor-pointer shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Thêm bài học
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-border-light rounded-xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm table-hover">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-border-light">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Tiêu đề</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Task</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Band</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Loại</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-text-secondary uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($lessons as $lesson)
                        <tr>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-text-primary truncate max-w-xs">{{ $lesson->title }}</p>
                                <p class="text-xs text-text-secondary mt-0.5 truncate max-w-xs">{{ Str::limit($lesson->prompt_text, 60) }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $lesson->task_type === 'task_1' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ strtoupper(str_replace('_', ' ', $lesson->task_type)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($lesson->band_score)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $lesson->band_score >= 7 ? 'bg-brand-light text-brand' : 'bg-orange-50 text-orange-600' }}">{{ $lesson->band_score }}</span>
                                @else
                                    <span class="text-text-disabled">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($lesson->is_premium)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-yellow-50 text-yellow-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Pro
                                    </span>
                                @else
                                    <span class="text-xs text-text-secondary">Free</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($lesson->status === 'published')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-text-disabled">
                                        <span class="w-1.5 h-1.5 rounded-full bg-text-disabled"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.lessons.mapping', $lesson) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-text-secondary hover:text-brand hover:bg-brand-light border border-border-light transition-all cursor-pointer" title="Mapping">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        Mapping
                                    </a>
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-text-secondary hover:text-blue-600 hover:bg-blue-50 border border-border-light transition-all cursor-pointer" title="Sửa">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Sửa
                                    </a>
                                    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Xác nhận xóa bài học này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-100 transition-all cursor-pointer" title="Xóa">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="w-10 h-10 text-text-disabled mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <p class="text-sm text-text-secondary">Chưa có bài học nào.</p>
                                <a href="{{ route('admin.lessons.create') }}" class="inline-block mt-2 text-sm text-brand hover:text-brand-dark font-medium cursor-pointer">Thêm bài học đầu tiên</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $lessons->withQueryString()->links() }}</div>
</x-admin.layout.app>
