  <!-- ============================================================
       SECTION 1: NAVIGATION BAR (Fixed, Floating, Blur)
       ============================================================ -->
  @php
    $navLinkBase = 'text-sm font-medium transition-colors duration-200 cursor-pointer';
    $activeNav = 'text-text-primary';
    $inactiveNav = 'text-text-secondary hover:text-text-primary';
  @endphp

  <nav class="navbar-blur fixed top-0 left-0 right-0 z-50 border-b border-border-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 cursor-pointer group" aria-label="Trang chủ IELTS Type & Learn">
          <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center transition-transform duration-200 group-hover:scale-105">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
          </div>
          <span class="text-lg font-semibold text-text-primary hidden sm:inline">IELTS Type & Learn</span>
        </a>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center gap-8">
          <a href="{{ route('home') }}" class="{{ $navLinkBase }} {{ request()->routeIs('home') ? $activeNav : $inactiveNav }}">Trang chủ</a>
          <a href="{{ route('client.home.about') }}" class="{{ $navLinkBase }} {{ request()->routeIs('client.home.about') ? $activeNav : $inactiveNav }}">Giới thiệu</a>
          <a href="{{ route('client.home.features') }}" class="{{ $navLinkBase }} {{ request()->routeIs('client.home.features') ? $activeNav : $inactiveNav }}">Chức năng</a>
          <a href="{{ route('client.home.pricing-contact') }}" class="{{ $navLinkBase }} {{ request()->routeIs('client.home.pricing-contact') ? $activeNav : $inactiveNav }}">Gói & Liên hệ</a>
        </div>

        <!-- CTA Buttons -->
        <div class="flex items-center gap-3">
          @auth
            <a href="{{ auth()->user()->role === 'user' ? route('client.dashboard') : route('admin.dashboard') }}" class="text-sm font-medium text-text-secondary hover:text-text-primary transition-colors duration-200 cursor-pointer hidden sm:inline-block">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors duration-200 cursor-pointer focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                Đăng xuất
              </button>
            </form>
          @else
            <a href="{{ route('login') }}" class="text-sm font-medium text-text-secondary hover:text-text-primary transition-colors duration-200 cursor-pointer hidden sm:inline-block">Đăng nhập</a>
            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors duration-200 cursor-pointer focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
              Bắt đầu miễn phí
            </a>
          @endauth
          <!-- Mobile Menu Button -->
          <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-gray-100 transition-colors duration-200 cursor-pointer focus-visible:ring-2 focus-visible:ring-brand" aria-label="Mở menu" aria-expanded="false">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="md:hidden hidden border-t border-border-light bg-white">
      <div class="px-4 py-4 space-y-3">
        <a href="{{ route('home') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Trang chủ</a>
        <a href="{{ route('client.home.about') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Giới thiệu</a>
        <a href="{{ route('client.home.features') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Chức năng</a>
        <a href="{{ route('client.home.pricing-contact') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Gói & Liên hệ</a>
        <hr class="border-border-light" />
        @auth
          <a href="{{ auth()->user()->role === 'user' ? route('client.dashboard') : route('admin.dashboard') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Dashboard</a>
          <form method="POST" action="{{ route('logout') }}" class="block w-full">
            @csrf
            <button type="submit" class="w-full text-center px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Đăng xuất</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="block text-sm font-medium text-text-secondary hover:text-text-primary transition-colors cursor-pointer">Đăng nhập</a>
          <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Bắt đầu miễn phí</a>
        @endauth
      </div>
    </div>
  </nav>