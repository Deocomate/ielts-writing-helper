<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đang chờ thanh toán - IELTS Type & Learn</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{'brand':'#11A683','brand-dark':'#0E8A6D','brand-light':'#E8F8F3','text-primary':'#0E101A','text-secondary':'#6D758D','text-disabled':'#B9BDC5','border-light':'#E1E4E8','app-bg':'#F9F9FA'}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>body{font-family:'Inter',sans-serif;color:#0E101A;-webkit-font-smoothing:antialiased;}</style>
</head>
<body class="bg-app-bg min-h-screen">
  <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="max-w-xl mx-auto bg-white rounded-2xl border border-border-light p-8 text-center">
      <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h1 class="text-2xl font-black text-text-primary">Đang chờ thanh toán</h1>
      <p class="text-sm text-text-secondary mt-2">Đơn hàng của bạn đang chờ xác nhận thanh toán. Vui lòng hoàn tất thanh toán qua QR bên dưới.</p>

      @if(!empty($order['qr_url']))
        <div class="mt-6 inline-block bg-white p-3 border border-border-light rounded-xl">
          <img src="{{ $order['qr_url'] }}" alt="QR thanh toán" class="w-48 h-48 mx-auto" />
        </div>
      @endif

      <div class="mt-6 bg-app-bg rounded-xl p-4 text-left space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-text-secondary">Mã đơn hàng</span>
          <span class="font-semibold text-text-primary">{{ $order['order_code'] ?? '—' }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-text-secondary">Số tiền</span>
          <span class="font-semibold text-text-primary">{{ number_format($order['amount'] ?? 0) }}đ</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-text-secondary">Trạng thái</span>
          <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded">Chờ thanh toán</span>
        </div>
      </div>

      <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
        @if(!empty($order['checkout_url']))
          <a href="{{ $order['checkout_url'] }}" target="_blank" class="py-3 px-6 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-dark transition-colors cursor-pointer text-center">Thanh toán ngay</a>
        @endif
        <a href="{{ route('client.dashboard') }}" class="py-3 px-6 border border-border-light text-sm font-medium text-text-secondary rounded-xl hover:bg-app-bg transition-colors cursor-pointer text-center">Về Dashboard</a>
      </div>

      <p class="text-xs text-text-disabled mt-5">Trang sẽ tự động cập nhật khi thanh toán thành công.</p>
    </div>
  </main>

  <script>
    setTimeout(function() {
      location.reload();
    }, 15000);
  </script>
</body>
</html>
