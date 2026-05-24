@php
	$notes = old('vocabulary_notes', $material?->vocabulary_notes ?? []);
	if (empty($notes)) {
		$notes = [['term' => '', 'meaning' => '', 'note' => '']];
	}
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
	@csrf
	@if($method === 'PUT')
		@method('PUT')
	@endif

	<div class="grid md:grid-cols-2 gap-4">
		<div>
			<label for="title" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Tiêu đề</label>
			<input id="title" name="title" value="{{ old('title', $material?->title) }}" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" required />
		</div>
		<div>
			<label for="topic" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Chủ đề</label>
			<select id="topic" name="topic" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
				@foreach($topics as $value => $label)
					<option value="{{ $value }}" @selected(old('topic', $material?->topic ?? 'people') === $value)>{{ $label }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div>
		<label for="excerpt" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Tóm tắt</label>
		<textarea id="excerpt" name="excerpt" rows="3" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm">{{ old('excerpt', $material?->excerpt) }}</textarea>
	</div>

	<div>
		<label for="content-editor" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Nội dung</label>
		<textarea id="content-editor" name="content" rows="14" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm">{{ old('content', $material?->content) }}</textarea>
	</div>

	<div>
		<label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Ảnh đại diện <span class="font-normal text-text-disabled">(tùy chọn)</span></label>
		@if($material?->image_path)
			<div class="mb-3 flex items-center gap-3">
				<img src="{{ Storage::disk('public')->url($material->image_path) }}" alt="Ảnh học liệu" class="h-20 w-28 object-cover rounded-lg border border-border-light" />
				<label class="inline-flex items-center gap-2 text-sm text-text-secondary cursor-pointer">
					<input type="checkbox" name="remove_image" value="1" class="accent-brand" />
					Xóa ảnh hiện tại
				</label>
			</div>
		@endif
		<input type="file" name="image" accept="image/*" class="block w-full text-sm text-text-secondary file:mr-4 file:rounded-lg file:border-0 file:bg-brand-light file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand hover:file:bg-emerald-100" />
	</div>

	<div x-data="{ notes: @js($notes) }" class="space-y-3">
		<div class="flex items-center justify-between gap-3">
			<div>
				<p class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Vocabulary notes</p>
				<p class="text-xs text-text-disabled mt-1">Client sẽ tự tìm các term này trong bài đọc để gắn tooltip.</p>
			</div>
			<button type="button" @click="notes.push({term: '', meaning: '', note: ''})" class="px-3 py-2 text-xs font-semibold text-brand bg-brand-light rounded-lg hover:bg-emerald-100 cursor-pointer">Thêm từ</button>
		</div>
		<template x-for="(note, index) in notes" :key="index">
			<div class="grid md:grid-cols-12 gap-3 rounded-xl border border-border-light bg-gray-50/40 p-3">
				<div class="md:col-span-3">
					<label class="block text-xs text-text-secondary mb-1">Term</label>
					<input :name="`vocabulary_notes[${index}][term]`" x-model="note.term" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" />
				</div>
				<div class="md:col-span-4">
					<label class="block text-xs text-text-secondary mb-1">Nghĩa</label>
					<input :name="`vocabulary_notes[${index}][meaning]`" x-model="note.meaning" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" />
				</div>
				<div class="md:col-span-4">
					<label class="block text-xs text-text-secondary mb-1">Ghi chú</label>
					<input :name="`vocabulary_notes[${index}][note]`" x-model="note.note" class="input-admin w-full border border-border-light rounded-lg px-3 py-2 text-sm" />
				</div>
				<div class="md:col-span-1 flex md:items-end">
					<button type="button" @click="notes.splice(index, 1)" class="w-full px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 cursor-pointer">Xóa</button>
				</div>
			</div>
		</template>
	</div>

	<div class="grid md:grid-cols-2 gap-4">
		<div>
			<label for="status" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Trạng thái</label>
			<select id="status" name="status" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
				<option value="draft" @selected(old('status', $material?->status ?? 'draft') === 'draft')>Draft</option>
				<option value="published" @selected(old('status', $material?->status) === 'published')>Published</option>
			</select>
		</div>
	</div>

	<div class="flex items-center gap-3 pt-4 border-t border-border-light">
		<button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer shadow-sm">Lưu học liệu</button>
		<a href="{{ route('admin.reading-materials.index') }}" class="px-4 py-2.5 border border-border-light rounded-lg text-sm text-text-secondary hover:bg-gray-50 transition-colors cursor-pointer">Hủy</a>
	</div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const editor = document.querySelector('#content-editor');
		if (editor && window.ClassicEditor) {
			ClassicEditor.create(editor, {
				toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'],
			}).catch(() => {});
		}
	});
</script>
