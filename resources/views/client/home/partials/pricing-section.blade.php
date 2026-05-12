{{-- SECTION 9: PRICING --}}
<section id="pricing" class="py-16 sm:py-24 bg-app-bg">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center reveal">
      <h2 class="text-2xl sm:text-3xl font-bold text-text-primary">Bảng giá đơn giản, giá trị rõ ràng</h2>
      <p class="mt-3 text-base text-text-secondary max-w-xl mx-auto leading-relaxed">
        Bắt đầu miễn phí. Nâng cấp Pro khi bạn sẵn sàng chinh phục Band 8.0+.
      </p>
    </div>

    <div class="grid md:grid-cols-2 gap-6 lg:gap-8 mt-12 max-w-3xl mx-auto">

      {{-- Free Plan --}}
      <div class="bg-white rounded-2xl shadow-card border border-border-light p-6 lg:p-8 reveal" style="transition-delay: 0.05s">
        <h3 class="text-lg font-semibold text-text-primary">{{ $pricing['free']['name'] ?? 'Free' }}</h3>
        <p class="text-sm text-text-secondary mt-1">Hoàn hảo để bắt đầu</p>
        <div class="mt-5">
          <span class="text-4xl font-bold text-text-primary">{{ $pricing['free']['price_label'] ?? '0đ' }}</span>
          <span class="text-sm text-text-secondary ml-1">{{ $pricing['free']['duration_label'] ?? '/mãi mãi' }}</span>
        </div>
        <a href="{{ route('register') }}" class="mt-6 w-full inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 text-text-primary text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200 cursor-pointer focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
          Bắt đầu miễn phí
        </a>
        <ul class="mt-6 space-y-3 text-sm text-text-secondary">
          @foreach(($pricing['free']['features'] ?? []) as $feature)
            <li class="flex items-start gap-2.5">
              @if($feature['included'])
                <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span>{{ $feature['label'] }}</span>
              @else
                <svg class="w-4 h-4 text-text-disabled mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <span class="text-text-disabled">{{ $feature['label'] }}</span>
              @endif
            </li>
          @endforeach
          {{-- Extra disabled features matching template --}}
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-text-disabled mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span class="text-text-disabled">Bài mẫu Band 8.0–9.0+</span>
          </li>
        </ul>
      </div>

      {{-- Pro Plan --}}
      @php
        $proPlan = $pricing['pro'][0] ?? null;
      @endphp
      <div class="pricing-card-popular bg-white rounded-2xl shadow-card p-6 lg:p-8 reveal" style="transition-delay: 0.15s">
        <!-- Popular Badge -->
        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
          <span class="inline-flex items-center gap-1 px-3 py-1 bg-brand text-white text-xs font-semibold rounded-full shadow-card">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
            Phổ biến nhất
          </span>
        </div>

        <h3 class="text-lg font-semibold text-text-primary">{{ $proPlan['name'] ?? 'Pro' }}</h3>
        <p class="text-sm text-text-secondary mt-1">Mở khóa toàn bộ tiềm năng</p>
        <div class="mt-5">
          <span class="text-4xl font-bold text-text-primary">{{ $proPlan['price_label'] ?? '199.000đ' }}</span>
          <span class="text-sm text-text-secondary ml-1">{{ $proPlan['duration_label'] ?? '/tháng' }}</span>
        </div>
        <a href="{{ route('register') }}" class="mt-6 w-full inline-flex items-center justify-center px-5 py-2.5 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors duration-200 cursor-pointer focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
          Nâng cấp Pro
        </a>
        <ul class="mt-6 space-y-3 text-sm text-text-secondary">
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span><strong class="text-text-primary">Tất cả</strong> tính năng Free</span>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>500+ bài mẫu Band 8.0–9.0+</span>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>Chép chính tả <strong class="text-text-primary">không giới hạn</strong></span>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>Phân tích sâu toàn bộ bài mẫu</span>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-semantic-purple mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg>
            <span><strong class="text-text-primary">Phòng thi & AI chấm điểm</strong></span>
          </li>
          <li class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>Sổ tay từ vựng không giới hạn</span>
          </li>
        </ul>
      </div>

    </div>
  </div>
</section>
