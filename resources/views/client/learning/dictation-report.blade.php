<x-client.layout.dashboard title="Chi tiết bài đã chép" activePage="dashboard">
  <x-slot:headerContent>
    <div>
      <h1 class="text-lg font-bold text-text-primary">Chi tiết bài đã chép</h1>
      <p class="text-xs text-text-secondary">
        Hoàn thành {{ $history->completed_at?->format('H:i, d/m/Y') ?? 'gần đây' }}
      </p>
    </div>
  </x-slot:headerContent>

  <x-slot:headerActions>
    <a href="{{ route('client.dashboard') }}" class="px-3 py-1.5 border border-border-light text-xs font-medium text-text-secondary rounded-lg hover:bg-app-bg transition-colors">
      Quay lại tổng quan
    </a>
  </x-slot:headerActions>

  @php
    $accuracyText = rtrim(rtrim(number_format((float) $history->accuracy, 2), '0'), '.');
  @endphp

  <div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-border-light">
      <p class="text-xs font-medium text-text-secondary mb-1">Tốc độ gõ</p>
      <p class="text-2xl font-black text-text-primary">{{ $history->wpm }}</p>
      <p class="text-xs text-text-disabled mt-1">WPM</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-border-light">
      <p class="text-xs font-medium text-text-secondary mb-1">Độ chính xác</p>
      <p class="text-2xl font-black text-brand">{{ $accuracyText }}%</p>
      <p class="text-xs text-text-disabled mt-1">Theo lần chép này</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-border-light">
      <p class="text-xs font-medium text-text-secondary mb-1">Thời điểm hoàn thành</p>
      <p class="text-2xl font-black text-text-primary">{{ $history->completed_at?->format('H:i') ?? '--:--' }}</p>
      <p class="text-xs text-text-disabled mt-1">{{ $history->completed_at?->diffForHumans() ?? '' }}</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-border-light p-5">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
      <div class="min-w-0">
        <h2 class="text-sm font-semibold text-text-primary">
          {{ $history->lesson->title ?? 'Bài học không còn tồn tại' }}
        </h2>
        @if($history->lesson)
          <p class="text-xs text-text-secondary mt-1">
            {{ strtoupper(str_replace('_', ' ', $history->lesson->task_type)) }}
            @if($history->lesson->question_type)
              &middot; {{ $history->lesson->question_type }}
            @endif
            @if($history->lesson->band_score)
              &middot; Band {{ $history->lesson->band_score }}
            @endif
          </p>
        @endif
      </div>

      @if($history->lesson)
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('client.learning.dictation', $history->lesson_id) }}" class="px-3 py-1.5 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark transition-colors">
            Chép lại bài này
          </a>
          <a href="{{ route('client.learning.analyze', $history->lesson_id) }}" class="px-3 py-1.5 border border-border-light text-xs font-medium text-text-secondary rounded-lg hover:bg-app-bg transition-colors">
            Xem phân tích bài mẫu
          </a>
        </div>
      @endif
    </div>

    <div class="mt-4 pt-4 border-t border-border-light">
      <p class="text-xs text-text-secondary">
        Mẹo: từ Dashboard, bạn có thể bấm trực tiếp vào từng hoạt động để mở lại chi tiết kết quả.
      </p>
    </div>
  </div>
</x-client.layout.dashboard>
