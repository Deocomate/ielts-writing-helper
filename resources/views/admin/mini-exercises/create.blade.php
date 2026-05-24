<x-admin.layout.app title="Thêm bài tập" active="mini-exercises">
	<div class="mb-4 flex items-center gap-2 text-sm">
		<a href="{{ route('admin.mini-exercises.index') }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Trạm sửa lỗi</a>
		<svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
		<span class="text-text-primary font-medium">Thêm mới</span>
	</div>

	<div class="max-w-4xl bg-white border border-border-light rounded-xl p-5 sm:p-6 shadow-card">
		@include('admin.mini-exercises.partials.form', [
			'action' => route('admin.mini-exercises.store'),
			'method' => 'POST',
			'exercise' => null,
		])
	</div>
</x-admin.layout.app>
