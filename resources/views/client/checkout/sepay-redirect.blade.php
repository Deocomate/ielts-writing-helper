<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dang ket noi cong thanh toan - IELTS Type & Learn</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
  <main class="w-full max-w-sm text-center bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
    <svg class="animate-spin h-10 w-10 text-emerald-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <h1 class="text-gray-900 font-semibold text-lg">Dang chuyen huong den SePay</h1>
    <p class="text-sm text-gray-500 mt-2">Vui long khong dong trinh duyet luc nay.</p>
  </main>

  <form id="sepay-form" action="{{ $actionUrl }}" method="POST" class="hidden">
    @foreach($formFields as $key => $value)
      <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(function () {
        document.getElementById('sepay-form').submit();
      }, 500);
    });
  </script>
</body>
</html>
