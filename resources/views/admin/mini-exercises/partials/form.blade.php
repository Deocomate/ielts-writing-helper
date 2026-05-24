@php
	$payload = $exercise?->question_data ?? [];
	$answers = $payload['answers'] ?? [];
	$options = $payload['options'] ?? [];
	$distractors = collect($options)->reject(fn ($option) => in_array($option, $answers, true))->implode(', ');
	$initialType = old('exercise_type', $exercise?->exercise_type ?? 'fill_blank');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ exerciseType: @js($initialType) }">
	@csrf
	@if($method === 'PUT')
		@method('PUT')
	@endif

	<div>
		<label for="title" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Tiêu đề</label>
		<input id="title" name="title" value="{{ old('title', $exercise?->title) }}" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" required />
	</div>

	<div class="grid md:grid-cols-3 gap-4">
		<div>
			<label for="mistake_type" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Loại lỗi</label>
			<select id="mistake_type" name="mistake_type" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
				@foreach($mistakeTypes as $value => $label)
					<option value="{{ $value }}" @selected(old('mistake_type', $exercise?->mistake_type ?? 'tense') === $value)>{{ $label }}</option>
				@endforeach
			</select>
		</div>
		<div>
			<label for="exercise_type" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Dạng bài</label>
			<select id="exercise_type" name="exercise_type" x-model="exerciseType" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
				@foreach($exerciseTypes as $value => $label)
					<option value="{{ $value }}">{{ $label }}</option>
				@endforeach
			</select>
		</div>
		<div>
			<label for="difficulty_level" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Độ khó</label>
			<select id="difficulty_level" name="difficulty_level" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
				@foreach($difficultyLevels as $value => $label)
					<option value="{{ $value }}" @selected(old('difficulty_level', $exercise?->difficulty_level ?? 'easy') === $value)>{{ $label }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div x-show="exerciseType === 'fill_blank' || exerciseType === 'drag_drop'" class="space-y-4">
		<div>
			<label for="marked_sentence" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Câu hỏi có đáp án trong dấu [ ]</label>
			<textarea id="marked_sentence" name="marked_sentence" rows="4" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" placeholder="She [went] to school yesterday.">{{ old('marked_sentence', $payload['marked_sentence'] ?? '') }}</textarea>
			<p class="text-xs text-text-secondary mt-1">Điền từ dùng một [answer]. Kéo thả có thể dùng nhiều [answer].</p>
		</div>
		<div>
			<label for="distractors" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Đáp án nhiễu</label>
			<input id="distractors" name="distractors" value="{{ old('distractors', $distractors) }}" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" placeholder="go, gone, going" />
		</div>
	</div>

	<div x-show="exerciseType === 'short_answer'" class="space-y-4">
		<div>
			<label for="prompt" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Prompt</label>
			<textarea id="prompt" name="prompt" rows="4" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm">{{ old('prompt', $payload['prompt'] ?? '') }}</textarea>
		</div>
		<div>
			<label for="accepted_answers" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Đáp án đúng</label>
			<input id="accepted_answers" name="accepted_answers" value="{{ old('accepted_answers', implode(', ', $answers)) }}" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" placeholder="went, she went" />
			<p class="text-xs text-text-secondary mt-1">Có thể nhập nhiều đáp án, cách nhau bằng dấu phẩy.</p>
		</div>
	</div>

	<div>
		<label for="explanation" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Giải thích</label>
		<textarea id="explanation" name="explanation" rows="5" class="input-admin w-full border border-border-light rounded-lg px-3.5 py-2.5 text-sm" required>{{ old('explanation', $exercise?->explanation) }}</textarea>
	</div>

	<div>
		<label for="status" class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Trạng thái</label>
		<select id="status" name="status" class="input-admin w-full md:w-64 border border-border-light rounded-lg px-3.5 py-2.5 text-sm cursor-pointer" required>
			<option value="draft" @selected(old('status', $exercise?->status ?? 'draft') === 'draft')>Draft</option>
			<option value="published" @selected(old('status', $exercise?->status) === 'published')>Published</option>
		</select>
	</div>

	<div class="flex items-center gap-3 pt-4 border-t border-border-light">
		<button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer shadow-sm">Lưu bài tập</button>
		<a href="{{ route('admin.mini-exercises.index') }}" class="px-4 py-2.5 border border-border-light rounded-lg text-sm text-text-secondary hover:bg-gray-50 transition-colors cursor-pointer">Hủy</a>
	</div>
</form>
