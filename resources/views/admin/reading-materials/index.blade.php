<x-admin.layout.app title="Học liệu mở rộng" active="reading-materials">
	<div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
		<form method="GET" class="flex flex-wrap items-center gap-2">
			<div class="relative">
				<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm học liệu..." class="input-admin pl-9 pr-3 py-2 text-sm border border-border-light rounded-lg w-56 transition-all" />
			</div>
			<select name="topic" class="input-admin border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer" onchange="this.form.submit()">
				<option value="">Tất cả chủ đề</option>
				@foreach($topics as $value => $label)
					<option value="{{ $value }}" @selected(($filters['topic'] ?? '') === $value)>{{ $label }}</option>
				@endforeach
			</select>
			<select name="status" class="input-admin border border-border-light rounded-lg px-3 py-2 text-sm cursor-pointer" onchange="this.form.submit()">
				<option value="">Tất cả trạng thái</option>
				<option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
				<option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
			</select>
			<button type="submit" class="px-3 py-2 text-sm bg-gray-100 text-text-primary rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">Lọc</button>
		</form>
		<a href="{{ route('admin.reading-materials.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-dark transition-colors cursor-pointer shadow-sm">
			<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
			Thêm học liệu
		</a>
	</div>

	<div class="bg-white border border-border-light rounded-xl overflow-hidden shadow-card">
		<div class="overflow-x-auto">
			<table class="min-w-full text-sm table-hover">
				<thead>
					<tr class="bg-gray-50/80 border-b border-border-light">
						<th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Ảnh</th>
						<th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Tiêu đề</th>
						<th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Chủ đề</th>
						<th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Lượt xem</th>
						<th class="px-5 py-3 text-left text-xs font-semibold text-text-secondary uppercase tracking-wider">Trạng thái</th>
						<th class="px-5 py-3 text-right text-xs font-semibold text-text-secondary uppercase tracking-wider">Thao tác</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-border-light">
					@forelse($materials as $material)
						<tr>
							<td class="px-5 py-3.5 w-20">
								@if($material->image_path)
									<img src="{{ Storage::disk('public')->url($material->image_path) }}" class="w-12 h-10 object-cover rounded border border-border-light" alt="Thumbnail">
								@else
									<div class="w-12 h-10 bg-gray-100 rounded border border-dashed border-gray-300 flex items-center justify-center text-[10px] text-gray-400">No img</div>
								@endif
							</td>
							<td class="px-5 py-3.5">
								<p class="font-medium text-text-primary truncate max-w-xs">{{ $material->title }}</p>
								<p class="text-xs text-text-secondary mt-0.5 truncate max-w-xs">{{ $material->excerpt }}</p>
							</td>
							<td class="px-5 py-3.5">
								<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand-light text-brand">{{ $topics[$material->topic] ?? $material->topic }}</span>
							</td>
							<td class="px-5 py-3.5 text-text-secondary">{{ $material->views_count }}</td>
							<td class="px-5 py-3.5">
								@if($material->status === 'published')
									<span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Published</span>
								@else
									<span class="inline-flex items-center gap-1 text-xs font-medium text-text-disabled"><span class="w-1.5 h-1.5 rounded-full bg-text-disabled"></span>Draft</span>
								@endif
							</td>
							<td class="px-5 py-3.5">
								<div class="flex items-center justify-end gap-1.5">
									<a href="{{ route('admin.reading-materials.edit', $material) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-text-secondary hover:text-blue-600 hover:bg-blue-50 border border-border-light transition-all cursor-pointer">Sửa</a>
									<form method="POST" action="{{ route('admin.reading-materials.destroy', $material) }}" onsubmit="return confirm('Xóa học liệu này?')">
										@csrf
										@method('DELETE')
										<button class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-100 transition-all cursor-pointer">Xóa</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr><td colspan="6" class="px-5 py-12 text-center text-sm text-text-secondary">Chưa có học liệu nào.</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	<div class="mt-4">{{ $materials->withQueryString()->links() }}</div>
</x-admin.layout.app>
