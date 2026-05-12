<x-client.layout.dashboard title="Tổng quan" activePage="dashboard">
  <x-slot:headerContent>
    <div>
      <h1 class="text-lg font-bold text-text-primary">Xin chào, {{ auth()->user()->name }}!</h1>
      <p class="text-xs text-text-secondary">{{ now()->translatedFormat('l, d \\t\\há\\n\\g n, Y') }}</p>
    </div>
  </x-slot:headerContent>

  <x-slot:headerActions>
    @unless(auth()->user()->isPro())
      <a href="{{ route('client.checkout') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs font-semibold rounded-lg hover:bg-yellow-100 transition-colors">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        Nâng cấp Pro
      </a>
    @endunless
    <a href="{{ route('client.lessons.library') }}" class="px-3 py-1.5 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark transition-colors">Bắt đầu luyện</a>
  </x-slot:headerActions>

  @php
    $summary = $analytics['summary'] ?? [];
    $charts = $analytics['charts'] ?? [];

    $avgBand = $summary['avg_band'] !== null ? number_format((float) $summary['avg_band'], 1) : '--';
    $avgTr = $summary['avg_tr'] !== null ? number_format((float) $summary['avg_tr'], 1) : '--';
    $avgCc = $summary['avg_cc'] !== null ? number_format((float) $summary['avg_cc'], 1) : '--';
    $avgLr = $summary['avg_lr'] !== null ? number_format((float) $summary['avg_lr'], 1) : '--';
    $avgGra = $summary['avg_gra'] !== null ? number_format((float) $summary['avg_gra'], 1) : '--';
    $avgWpm = $summary['avg_wpm'] !== null ? number_format((float) $summary['avg_wpm'], 0) : '--';
    $avgAccuracy = $summary['avg_accuracy'] !== null ? number_format((float) $summary['avg_accuracy'], 2) : '--';

    $selectedPeriod = $periodDays ?? ($analytics['period_days'] ?? 30);
  @endphp

  <div class="bg-white rounded-xl border border-border-light p-4 mb-6">
    <form method="GET" action="{{ route('client.dashboard') }}" class="grid md:grid-cols-4 gap-3 items-end">
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wide mb-1.5">Khung thời gian phân tích</label>
        <select name="period_days" class="w-full border border-border-light rounded-lg px-3 py-2.5 text-sm text-text-primary bg-white focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all">
          <option value="7" @selected($selectedPeriod == 7)>7 ngày</option>
          <option value="30" @selected($selectedPeriod == 30)>30 ngày</option>
          <option value="90" @selected($selectedPeriod == 90)>90 ngày</option>
          <option value="180" @selected($selectedPeriod == 180)>180 ngày</option>
          <option value="365" @selected($selectedPeriod == 365)>365 ngày</option>
        </select>
      </div>
      <div class="md:col-span-3 flex gap-2 md:justify-end">
        <button type="submit" class="inline-flex items-center justify-center gap-1.5 bg-brand text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-brand-dark transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707L14 14v6l-4-2v-4L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          Áp dụng
        </button>
        <a href="{{ route('client.dashboard') }}" class="inline-flex items-center justify-center border border-border-light text-text-secondary rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-app-bg transition-colors">Mặc định</a>
      </div>
    </form>
  </div>

  <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Tổng lượt luyện</p>
      <p class="text-2xl font-black text-text-primary mt-2">{{ number_format((int) ($summary['total_attempts'] ?? 0)) }}</p>
      <p class="text-xs text-text-disabled mt-1">Trong {{ $selectedPeriod }} ngày</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Mock Exams</p>
      <p class="text-2xl font-black text-text-primary mt-2">{{ number_format((int) ($stats['total_mock_exams'] ?? 0)) }}</p>
      <p class="text-xs text-text-disabled mt-1">Hoàn thành: {{ number_format((int) ($stats['completed_mock_exams'] ?? 0)) }}</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Band trung bình</p>
      <p class="text-2xl font-black text-brand mt-2">{{ $avgBand }}</p>
      <p class="text-xs text-text-disabled mt-1">TR {{ $avgTr }} · CC {{ $avgCc }} · LR {{ $avgLr }} · GRA {{ $avgGra }}</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Thời gian luyện tập</p>
      <p class="text-2xl font-black text-text-primary mt-2">{{ $summary['total_learning_time_label'] ?? '--' }}</p>
      <p class="text-xs text-text-disabled mt-1">Mock {{ $summary['mock_exam_time_label'] ?? '--' }} · Gõ {{ $summary['dictation_time_label'] ?? '--' }}</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Bài đã chép</p>
      <p class="text-2xl font-black text-text-primary mt-2">{{ number_format((int) ($stats['total_dictations'] ?? 0)) }}</p>
      <p class="text-xs text-text-disabled mt-1">Accuracy TB {{ $avgAccuracy }}%</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">WPM trung bình</p>
      <p class="text-2xl font-black text-blue-600 mt-2">{{ $avgWpm }}</p>
      <p class="text-xs text-text-disabled mt-1">Tốc độ gõ hiện tại</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Accuracy trung bình</p>
      <p class="text-2xl font-black text-emerald-600 mt-2">{{ $avgAccuracy }}%</p>
      <p class="text-xs text-text-disabled mt-1">Độ chính xác khi gõ</p>
    </div>

    <div class="bg-white rounded-xl p-4 border border-border-light hover:shadow-float hover:-translate-y-0.5 transition-all">
      <p class="text-xs font-medium text-text-secondary">Lần học gần nhất</p>
      <p class="text-lg font-bold text-text-primary mt-2">{{ optional($summary['latest_activity_at'])->format('H:i d/m/Y') ?? '--' }}</p>
      <p class="text-xs text-text-disabled mt-1">Cập nhật theo bộ lọc</p>
    </div>
  </div>

  <div class="grid xl:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-border-light p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-text-primary text-sm">Xu hướng điểm Mock Exam</h2>
        <span class="text-xs text-text-secondary">12 bài hoàn thành gần nhất</span>
      </div>
      <div class="h-72">
        <canvas id="dashboardMockBandTrendChart"></canvas>
      </div>
      <p id="dashboardMockBandTrendEmpty" class="hidden text-xs text-text-secondary mt-2">Chưa có dữ liệu điểm Mock Exam trong bộ lọc hiện tại.</p>
    </div>

    <div class="bg-white rounded-xl border border-border-light p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-text-primary text-sm">Radar 4 tiêu chí chấm điểm</h2>
        <span class="text-xs text-text-secondary">So sánh với mục tiêu Band 7.0</span>
      </div>
      <div class="h-72">
        <canvas id="dashboardScoreRadarChart"></canvas>
      </div>
      <p id="dashboardScoreRadarEmpty" class="hidden text-xs text-text-secondary mt-2">Cần có Mock Exam đã chấm để hiển thị radar điểm.</p>
    </div>
  </div>

  <div class="grid xl:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-border-light p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-text-primary text-sm">Xu hướng gõ: WPM và Accuracy</h2>
        <span class="text-xs text-text-secondary">12 lần gần nhất</span>
      </div>
      <div class="h-72">
        <canvas id="dashboardDictationTrendChart"></canvas>
      </div>
      <p id="dashboardDictationTrendEmpty" class="hidden text-xs text-text-secondary mt-2">Chưa có dữ liệu Dictation trong bộ lọc hiện tại.</p>
    </div>

    <div class="bg-white rounded-xl border border-border-light p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-text-primary text-sm">Tương quan tốc độ và độ chính xác</h2>
        <span class="text-xs text-text-secondary">30 lần gõ gần nhất</span>
      </div>
      <div class="h-72">
        <canvas id="dashboardTypingScatterChart"></canvas>
      </div>
      <p id="dashboardTypingScatterEmpty" class="hidden text-xs text-text-secondary mt-2">Chưa có đủ dữ liệu để phân tích tương quan WPM/Accuracy.</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-border-light p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-text-primary text-sm">Khối lượng luyện tập theo ngày</h2>
      <span class="text-xs text-text-secondary">{{ $charts['attempt_volume']['window_days'] ?? 14 }} ngày gần nhất</span>
    </div>
    <div class="h-72">
      <canvas id="dashboardAttemptVolumeChart"></canvas>
    </div>
    <p id="dashboardAttemptVolumeEmpty" class="hidden text-xs text-text-secondary mt-2">Chưa có dữ liệu khối lượng luyện tập theo ngày.</p>
  </div>

  <div class="grid lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-border-light p-5 lg:col-span-2">
      <h2 class="font-semibold text-text-primary text-sm mb-4">Hoạt động gần đây</h2>
      @forelse($recentActivity->take(6) as $activity)
        @php
          $isDictation = $activity['type'] === 'dictation';
          $activityUrl = $isDictation
            ? route('client.learning.dictation.report', $activity['history_id'])
            : route('client.learning.mock-exam.report', $activity['exam_id']);
          $accuracyText = rtrim(rtrim(number_format((float) ($activity['accuracy'] ?? 0), 2), '0'), '.');
          $mockBandText = ($activity['status'] ?? null) === 'grading'
            ? 'Đang chấm'
            : (($activity['band'] ?? null) !== null ? 'Band ' . $activity['band'] : 'Chưa có điểm');
        @endphp
        <a href="{{ $activityUrl }}" class="group -mx-2 block rounded-lg p-2 transition-colors hover:bg-app-bg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 {{ !$loop->last ? 'mb-2' : '' }}">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg {{ $isDictation ? 'bg-brand-light' : 'bg-purple-50' }} flex items-center justify-center shrink-0">
              @if($isDictation)
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              @else
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              @endif
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-text-primary truncate">{{ $activity['lesson']->title ?? 'Bài học' }}</p>
              <p class="text-xs text-text-secondary">
                @if($isDictation)
                  {{ $activity['wpm'] }} WPM · {{ $accuracyText }}% · {{ $activity['created_at']?->diffForHumans() ?? 'Vừa xong' }}
                @else
                  Thi thử · {{ $mockBandText }} · {{ $activity['created_at']?->diffForHumans() ?? 'Vừa xong' }}
                @endif
              </p>
            </div>
            <span class="mt-0.5 text-[11px] font-semibold text-brand opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">Chi tiết</span>
          </div>
        </a>
      @empty
        <p class="text-xs text-text-disabled text-center py-4">Chưa có hoạt động nào.</p>
      @endforelse
      <a href="{{ route('client.lessons.library') }}" class="mt-4 block text-center text-xs text-brand font-medium hover:text-brand-dark transition-colors">Xem thêm →</a>
    </div>

    <div class="bg-white rounded-xl border border-border-light p-5">
      <h2 class="font-semibold text-text-primary text-sm mb-4">Tổng hợp nhanh</h2>
      <div class="space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-text-secondary">Band trung bình</span>
          <span class="font-semibold text-brand">{{ $avgBand }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-text-secondary">WPM trung bình</span>
          <span class="font-semibold text-blue-600">{{ $avgWpm }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-text-secondary">Accuracy trung bình</span>
          <span class="font-semibold text-emerald-600">{{ $avgAccuracy }}%</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-text-secondary">Mock Exams hoàn thành</span>
          <span class="font-semibold text-text-primary">{{ number_format((int) ($stats['completed_mock_exams'] ?? 0)) }}</span>
        </div>
        <div class="pt-3 border-t border-border-light">
          <p class="text-xs text-text-secondary leading-relaxed">
            Mục tiêu gợi ý: đẩy TR/CC/LR/GRA lên mức 6.5+ trước, sau đó tối ưu tốc độ gõ ổn định để tăng chất lượng bài viết khi thi thử.
          </p>
        </div>
      </div>
    </div>
  </div>

  @unless(auth()->user()->isPro())
    <div class="bg-linear-to-r from-brand to-brand-dark rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
      <div class="text-white">
        <p class="font-bold text-sm flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          Mở khóa toàn bộ tiềm năng với Pro
        </p>
        <p class="text-xs opacity-80 mt-1">Thi thử AI, 500+ bài Band 8.0-9.0, phân tích sâu Grammarly-style.</p>
      </div>
      <a href="{{ route('client.checkout') }}" class="shrink-0 px-4 py-2 bg-white text-brand text-sm font-bold rounded-lg hover:bg-brand-light transition-colors">Nâng cấp Pro →</a>
    </div>
  @endunless

  @if($recommendedLessons->isNotEmpty())
    <div class="bg-white rounded-xl border border-border-light p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-text-primary text-sm">Bài học đề xuất cho bạn</h2>
        <a href="{{ route('client.lessons.library') }}" class="text-xs text-brand hover:text-brand-dark transition-colors">Xem tất cả</a>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($recommendedLessons as $lesson)
          <a href="{{ route('client.learning.dictation', $lesson->id) }}" class="p-4 bg-app-bg rounded-xl border border-border-light hover:border-brand transition-colors group block">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs px-2 py-0.5 {{ $lesson->is_premium ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-brand' }} font-semibold rounded-full">
                {{ $lesson->is_premium ? 'Pro' : 'Miễn phí' }}
              </span>
              <span class="text-xs text-text-secondary">{{ $lesson->task_type }}</span>
            </div>
            <p class="text-sm font-semibold text-text-primary group-hover:text-brand transition-colors">{{ $lesson->title }}</p>
            <p class="text-xs text-text-secondary mt-1">{{ $lesson->question_type }} · Band {{ $lesson->band_score }}</p>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const dashboardChartPayload = @json($charts);

    function hasDataset(dataset) {
      return Array.isArray(dataset) && dataset.some((value) => value !== null && value !== undefined && Number.isFinite(Number(value)));
    }

    function hasPointDataset(points) {
      return Array.isArray(points) && points.some((point) => point && Number.isFinite(Number(point.x)) && Number.isFinite(Number(point.y)));
    }

    function renderMockBandTrendChart() {
      const target = document.getElementById('dashboardMockBandTrendChart');
      const emptyMessage = document.getElementById('dashboardMockBandTrendEmpty');
      const labels = dashboardChartPayload?.mock_exam_band_trend?.labels ?? [];
      const datasets = dashboardChartPayload?.mock_exam_band_trend?.datasets ?? {};

      if (!target || !labels.length || !hasDataset(datasets.overall || [])) {
        if (emptyMessage) {
          emptyMessage.classList.remove('hidden');
        }
        return;
      }

      new Chart(target, {
        type: 'line',
        data: {
          labels,
          datasets: [
            { label: 'Overall', data: datasets.overall, borderColor: '#11A683', backgroundColor: 'rgba(17,166,131,0.12)', tension: 0.32, fill: false },
            { label: 'TR', data: datasets.tr, borderColor: '#8B5CF6', tension: 0.32, fill: false },
            { label: 'CC', data: datasets.cc, borderColor: '#0EA5E9', tension: 0.32, fill: false },
            { label: 'LR', data: datasets.lr, borderColor: '#16A34A', tension: 0.32, fill: false },
            { label: 'GRA', data: datasets.gra, borderColor: '#F97316', tension: 0.32, fill: false },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: {
            y: { min: 0, max: 9, ticks: { stepSize: 1 } },
          },
        },
      });
    }

    function renderScoreRadarChart() {
      const target = document.getElementById('dashboardScoreRadarChart');
      const emptyMessage = document.getElementById('dashboardScoreRadarEmpty');
      const scoreRadar = dashboardChartPayload?.score_radar ?? {};

      if (!target || !scoreRadar.has_data) {
        if (emptyMessage) {
          emptyMessage.classList.remove('hidden');
        }
        return;
      }

      new Chart(target, {
        type: 'radar',
        data: {
          labels: scoreRadar.labels ?? ['TR', 'CC', 'LR', 'GRA'],
          datasets: [
            {
              label: 'Điểm hiện tại',
              data: scoreRadar.values ?? [0, 0, 0, 0],
              borderColor: '#11A683',
              backgroundColor: 'rgba(17,166,131,0.20)',
              pointBackgroundColor: '#11A683',
            },
            {
              label: 'Mục tiêu 7.0',
              data: scoreRadar.target ?? [7, 7, 7, 7],
              borderColor: '#B9BDC5',
              backgroundColor: 'rgba(185,189,197,0.08)',
              pointBackgroundColor: '#B9BDC5',
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: {
            r: {
              beginAtZero: true,
              min: 0,
              max: 9,
              ticks: { stepSize: 1 },
            },
          },
        },
      });
    }

    function renderDictationTrendChart() {
      const target = document.getElementById('dashboardDictationTrendChart');
      const emptyMessage = document.getElementById('dashboardDictationTrendEmpty');
      const labels = dashboardChartPayload?.dictation_trend?.labels ?? [];
      const datasets = dashboardChartPayload?.dictation_trend?.datasets ?? {};

      if (!target || !labels.length || !hasDataset(datasets.wpm || [])) {
        if (emptyMessage) {
          emptyMessage.classList.remove('hidden');
        }
        return;
      }

      new Chart(target, {
        data: {
          labels,
          datasets: [
            { type: 'line', label: 'WPM', data: datasets.wpm, borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,0.14)', yAxisID: 'y', tension: 0.35 },
            { type: 'line', label: 'Accuracy (%)', data: datasets.accuracy, borderColor: '#16A34A', backgroundColor: 'rgba(22,163,74,0.14)', yAxisID: 'y1', tension: 0.35 },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: {
            y: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'WPM' } },
            y1: { type: 'linear', position: 'right', beginAtZero: true, max: 100, grid: { drawOnChartArea: false }, title: { display: true, text: 'Accuracy (%)' } },
          },
        },
      });
    }

    function renderTypingScatterChart() {
      const target = document.getElementById('dashboardTypingScatterChart');
      const emptyMessage = document.getElementById('dashboardTypingScatterEmpty');
      const labels = dashboardChartPayload?.typing_scatter?.labels ?? [];
      const points = dashboardChartPayload?.typing_scatter?.points ?? [];

      if (!target || !hasPointDataset(points)) {
        if (emptyMessage) {
          emptyMessage.classList.remove('hidden');
        }
        return;
      }

      new Chart(target, {
        type: 'scatter',
        data: {
          datasets: [
            {
              label: 'Phiên gõ',
              data: points,
              backgroundColor: 'rgba(17,166,131,0.65)',
              borderColor: '#11A683',
              pointRadius: 5,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' },
            tooltip: {
              callbacks: {
                label(context) {
                  const timeLabel = labels[context.dataIndex] ? `${labels[context.dataIndex]}: ` : '';
                  return `${timeLabel}${context.parsed.x} WPM - ${context.parsed.y}%`;
                },
              },
            },
          },
          scales: {
            x: { beginAtZero: true, title: { display: true, text: 'WPM' } },
            y: { beginAtZero: true, max: 100, title: { display: true, text: 'Accuracy (%)' } },
          },
        },
      });
    }

    function renderAttemptVolumeChart() {
      const target = document.getElementById('dashboardAttemptVolumeChart');
      const emptyMessage = document.getElementById('dashboardAttemptVolumeEmpty');
      const labels = dashboardChartPayload?.attempt_volume?.labels ?? [];
      const mockVolumes = dashboardChartPayload?.attempt_volume?.mock_exam ?? [];
      const dictationVolumes = dashboardChartPayload?.attempt_volume?.dictation ?? [];
      const mockMinutes = dashboardChartPayload?.attempt_volume?.mock_exam_minutes ?? [];
      const dictationMinutes = dashboardChartPayload?.attempt_volume?.dictation_minutes ?? [];

      if (!target || !labels.length || (!hasDataset(mockVolumes) && !hasDataset(dictationVolumes))) {
        if (emptyMessage) {
          emptyMessage.classList.remove('hidden');
        }
        return;
      }

      new Chart(target, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            { label: 'Lượt Mock Exam', data: mockVolumes, backgroundColor: 'rgba(17,166,131,0.68)' },
            { label: 'Lượt Dictation', data: dictationVolumes, backgroundColor: 'rgba(37,99,235,0.68)' },
            { type: 'line', label: 'Phút Mock', data: mockMinutes, borderColor: '#047857', yAxisID: 'y1', tension: 0.3 },
            { type: 'line', label: 'Phút Dictation', data: dictationMinutes, borderColor: '#1D4ED8', yAxisID: 'y1', tension: 0.3 },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Số lượt' } },
            y1: {
              position: 'right',
              beginAtZero: true,
              grid: { drawOnChartArea: false },
              title: { display: true, text: 'Phút luyện' },
            },
          },
        },
      });
    }

    renderMockBandTrendChart();
    renderScoreRadarChart();
    renderDictationTrendChart();
    renderTypingScatterChart();
    renderAttemptVolumeChart();
  </script>
</x-client.layout.dashboard>