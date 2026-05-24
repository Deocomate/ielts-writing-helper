<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Thanh toán - IELTS Type & Learn</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'app-bg':'#F9F9FA','surface':'#FFFFFF','border-light':'#E1E4E8',
            'text-primary':'#0E101A','text-secondary':'#6D758D','text-disabled':'#B9BDC5',
            'brand':'#11A683','brand-dark':'#0E8A6D','brand-light':'#E8F8F3',
          },
          fontFamily: { sans: ['Inter','Roboto','sans-serif'] },
          boxShadow: {
            'card':'0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03)',
            'float':'0 4px 6px rgba(0,0,0,0.05), 0 10px 15px rgba(0,0,0,0.1)',
          },
        },
      },
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body{font-family:'Inter',sans-serif;color:#0E101A;-webkit-font-smoothing:antialiased;}
    [x-cloak]{display:none!important;}
    .method-card{transition:all 0.15s;}
    .method-card.selected{border-color:#11A683;background:#E8F7F3;}
  </style>
</head>
<body class="bg-app-bg min-h-screen" x-data="checkoutPage()" x-init="init()">

  <nav class="bg-white border-b border-border-light">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
      <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2">
        <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </div>
        <span class="font-bold text-sm text-text-primary">IELTS Type & Learn</span>
      </a>
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-text-disabled" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span class="text-xs text-text-disabled">Thanh toán bảo mật</span>
      </div>
    </div>
  </nav>

  <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="grid lg:grid-cols-5 gap-8">
      <div class="lg:col-span-3">
        <h1 class="text-2xl font-black text-text-primary mb-6">Hoàn tất đăng ký</h1>

        @if($plans->count() > 1)
          <div class="mb-6">
            <p class="text-sm font-semibold text-text-primary mb-3">Chọn gói</p>
            <div class="grid grid-cols-1 sm:grid-cols-{{ min($plans->count(), 3) }} gap-3">
              @foreach($plans as $p)
                <button
                  type="button"
                  @click="selectPlan({{ $p->id }}, {{ $p->price }}, @js($p->name), {{ $p->duration_days }})"
                  :class="selectedPlanId === {{ $p->id }} ? 'border-brand bg-brand-light' : 'border-border-light hover:bg-white'"
                  class="p-3.5 border-2 rounded-xl cursor-pointer text-left transition-all bg-white min-h-[112px]"
                >
                  <span class="text-sm font-semibold text-text-primary block">{{ $p->name }}</span>
                  <span class="text-lg font-black text-text-primary mt-1 block">{{ number_format($p->price, 0, ',', '.') }}đ</span>
                  <span class="text-xs text-text-secondary">{{ $p->duration_days }} ngày</span>
                </button>
              @endforeach
            </div>
          </div>
        @endif

        <div class="mb-6">
          <p class="text-sm font-semibold text-text-primary mb-3">Phương thức thanh toán</p>
          <div class="grid grid-cols-1 gap-3">
            <button type="button" @click="method='sepay'" :class="method==='sepay' ? 'selected' : ''" class="method-card flex items-center justify-between gap-3 p-3.5 border-2 border-border-light rounded-xl cursor-pointer bg-white">
              <span class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 bg-brand-light rounded-lg flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </span>
                <span class="text-left min-w-0">
                  <span class="text-sm font-semibold text-text-primary block">SePay Checkout</span>
                  <span class="text-xs text-text-secondary block mt-0.5">QR, NAPAS và thẻ quốc tế</span>
                </span>
              </span>
              <svg x-show="method==='sepay'" x-cloak class="h-5 w-5 text-brand shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            </button>

            <button type="button" @click="method='payos'" :class="method==='payos' ? 'selected' : ''" class="method-card flex items-center justify-between gap-3 p-3.5 border-2 border-border-light rounded-xl cursor-pointer bg-white">
              <span class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center shrink-0">
                  <span class="font-bold text-blue-600 text-xs">PayOS</span>
                </span>
                <span class="text-left min-w-0">
                  <span class="text-sm font-semibold text-text-primary block">PayOS Checkout</span>
                  <span class="text-xs text-text-secondary block mt-0.5">VietQR Napas 24/7</span>
                </span>
              </span>
              <svg x-show="method==='payos'" x-cloak class="h-5 w-5 text-brand shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            </button>
          </div>
        </div>

        <div x-show="method==='sepay'" x-cloak class="mb-6">
          <div class="bg-white rounded-2xl border border-border-light p-6 flex flex-col sm:flex-row items-center gap-6">
            <div class="w-40 h-40 border-2 border-border-light rounded-xl flex items-center justify-center shrink-0 bg-app-bg">
              <svg class="w-24 h-24 text-brand" viewBox="0 0 100 100" fill="none">
                <rect x="10" y="10" width="30" height="30" rx="3" fill="currentColor" opacity="0.18"/>
                <rect x="15" y="15" width="20" height="20" rx="2" fill="currentColor" opacity="0.7"/>
                <rect x="60" y="10" width="30" height="30" rx="3" fill="currentColor" opacity="0.18"/>
                <rect x="65" y="15" width="20" height="20" rx="2" fill="currentColor" opacity="0.7"/>
                <rect x="10" y="60" width="30" height="30" rx="3" fill="currentColor" opacity="0.18"/>
                <rect x="15" y="65" width="20" height="20" rx="2" fill="currentColor" opacity="0.7"/>
                <rect x="50" y="50" width="8" height="8" fill="currentColor" opacity="0.9"/>
                <rect x="65" y="62" width="22" height="8" fill="currentColor" opacity="0.55"/>
                <rect x="50" y="76" width="36" height="8" fill="currentColor" opacity="0.75"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-xs text-text-disabled font-medium mb-1">SePay sẽ chuyển bạn đến cổng thanh toán bảo mật.</p>
              <p class="font-bold text-text-primary text-sm">Xác nhận tự động bằng IPN realtime</p>
              <p class="text-sm text-text-secondary">Hỗ trợ QR chuyển khoản, NAPAS và thẻ quốc tế tùy cấu hình merchant.</p>
            </div>
          </div>
          <p class="text-xs text-text-disabled mt-2 text-center">Tài khoản Pro chỉ được kích hoạt sau khi SePay gửi IPN hợp lệ.</p>
        </div>

        <div x-show="method==='payos'" x-cloak class="mb-6">
          <div class="bg-white rounded-2xl border border-border-light p-6 flex flex-col sm:flex-row items-center gap-6">
            <div class="w-40 h-40 border-2 border-border-light rounded-xl flex items-center justify-center shrink-0 bg-app-bg">
              <svg class="w-24 h-24 text-text-primary" viewBox="0 0 100 100" fill="none">
                <rect x="10" y="10" width="30" height="30" rx="3" fill="currentColor" opacity="0.15"/>
                <rect x="15" y="15" width="20" height="20" rx="2" fill="currentColor" opacity="0.6"/>
                <rect x="60" y="10" width="30" height="30" rx="3" fill="currentColor" opacity="0.15"/>
                <rect x="65" y="15" width="20" height="20" rx="2" fill="currentColor" opacity="0.6"/>
                <rect x="10" y="60" width="30" height="30" rx="3" fill="currentColor" opacity="0.15"/>
                <rect x="15" y="65" width="20" height="20" rx="2" fill="currentColor" opacity="0.6"/>
                <rect x="45" y="45" width="10" height="10" fill="currentColor" opacity="0.8"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-xs text-text-disabled font-medium mb-1">Sau khi tạo giao dịch, hệ thống sẽ trả về QR thật từ PayOS.</p>
              <p class="font-bold text-text-primary text-sm">Thanh toán tự động bằng webhook</p>
              <p class="text-sm text-text-secondary">Hỗ trợ VietQR Napas 24/7 qua trang thanh toán PayOS.</p>
            </div>
          </div>
          <p class="text-xs text-text-disabled mt-2 text-center">Đơn hàng sẽ tự động xác nhận sau khi PayOS gửi webhook thành công.</p>
        </div>

        <form method="POST" action="{{ route('client.checkout.process') }}">
          @csrf
          <input type="hidden" name="plan_id" :value="selectedPlanId" />
          <input type="hidden" name="payment_method" :value="method" />
          <button type="submit" class="w-full py-3 bg-brand text-white font-bold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer" x-text="'Thanh toán ' + formattedPrice">Thanh toán</button>
        </form>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-border-light p-5 sticky top-20">
          <h2 class="font-semibold text-text-primary mb-4">Đơn hàng của bạn</h2>
          <div class="flex items-center gap-3 pb-4 border-b border-border-light">
            <div class="w-10 h-10 bg-gradient-to-br from-brand to-brand-dark rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <p class="font-semibold text-sm text-text-primary" x-text="planName">IELTS Type & Learn Pro</p>
              <p class="text-xs text-text-secondary" x-text="planDays + ' ngày'"></p>
            </div>
          </div>
          <div class="py-3 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-text-secondary">Gói</span>
              <span class="text-text-primary font-medium" x-text="planName"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-text-secondary">Chu kỳ</span>
              <span class="text-text-primary font-medium" x-text="planDays + ' ngày'"></span>
            </div>
          </div>
          <div class="border-t border-border-light pt-3">
            <div class="flex justify-between">
              <span class="font-bold text-text-primary">Tổng</span>
              <span class="font-black text-text-primary text-lg" x-text="formattedPrice"></span>
            </div>
          </div>
          <ul class="mt-4 space-y-2">
            @foreach(['Chép không giới hạn', '500+ bài mẫu Band 8.0-9.0', 'Thi thử AI chấm điểm', 'Hủy bất cứ lúc nào'] as $feature)
              <li class="flex items-center gap-2 text-xs text-text-secondary">
                <svg class="w-3.5 h-3.5 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $feature }}
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    function checkoutPage() {
      return {
        method: 'sepay',
        selectedPlanId: {{ $plan?->id ?? ($plans->first()?->id ?? 'null') }},
        planName: @js($plan?->name ?? $plans->first()?->name ?? ''),
        planPrice: {{ $plan?->price ?? $plans->first()?->price ?? 0 }},
        planDays: {{ $plan?->duration_days ?? $plans->first()?->duration_days ?? 30 }},
        get formattedPrice() {
          return new Intl.NumberFormat('vi-VN').format(this.planPrice) + 'đ';
        },
        init() {},
        selectPlan(id, price, name, days) {
          this.selectedPlanId = id;
          this.planPrice = price;
          this.planName = name;
          this.planDays = days;
        }
      }
    }
  </script>
</body>
</html>
