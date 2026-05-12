<x-client.layout.dashboard title="Sổ tay từ vựng" activePage="vocabulary">
  <x-slot:headerContent>
    <div>
      <h1 class="text-lg font-bold text-text-primary">Sổ tay từ vựng</h1>
      <p class="text-xs text-text-secondary">{{ $totalCount }} từ/cụm từ đã lưu</p>
    </div>
  </x-slot:headerContent>
  <x-slot:headerActions>
    <form method="GET" action="{{ route('client.vocabulary') }}" class="hidden sm:flex items-center gap-2">
      <input name="search" type="text" value="{{ request('search') }}" placeholder="Tìm từ vựng..."
        class="px-3 py-1.5 bg-app-bg border border-border-light rounded-lg text-xs text-text-primary placeholder-text-disabled w-48" />
      <button type="submit" class="px-3 py-1.5 bg-brand text-white text-xs font-semibold rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Tìm</button>
    </form>
  </x-slot:headerActions>

  {{-- Mobile search --}}
  <form method="GET" action="{{ route('client.vocabulary') }}" class="sm:hidden flex items-center gap-2 mb-4">
    <input name="search" type="text" value="{{ request('search') }}" placeholder="Tìm từ vựng..."
      class="flex-1 px-3 py-2 bg-white border border-border-light rounded-lg text-sm text-text-primary placeholder-text-disabled" />
    <button type="submit" class="px-3 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark transition-colors cursor-pointer">Tìm</button>
  </form>

  @if($vocabularies->isEmpty())
    <div class="bg-white rounded-xl border border-border-light p-10 text-center">
      <div class="w-14 h-14 bg-brand-light rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
      </div>
      <h3 class="font-semibold text-text-primary mb-1">Chưa có từ vựng nào</h3>
      <p class="text-sm text-text-secondary mb-4">Lưu từ vựng khi bạn học bài để ôn tập sau.</p>
      <a href="{{ route('client.lessons.library') }}" class="inline-flex px-4 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-dark transition-colors">Bắt đầu học ngay</a>
    </div>
  @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($vocabularies as $vocab)
        <div class="bg-white rounded-xl border border-border-light p-4 hover:shadow-card transition-shadow">
          <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-text-primary text-sm">{{ $vocab->word }}</h3>
            <form method="POST" action="{{ route('client.vocabulary.destroy', $vocab->id) }}" onsubmit="return confirm('Xóa từ này?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-text-disabled hover:text-semantic-red transition-colors cursor-pointer" title="Xóa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
          <p class="text-xs text-text-secondary mb-2">{{ $vocab->meaning }}</p>
          @if($vocab->context_sentence)
            <p class="text-xs text-text-disabled italic border-l-2 border-brand-light pl-2">"{{ $vocab->context_sentence }}"</p>
          @endif
          @if($vocab->lesson)
            <p class="text-xs text-text-disabled mt-2">Từ: {{ $vocab->lesson->title }}</p>
          @endif
        </div>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $vocabularies->links() }}
    </div>
  @endif
</x-client.layout.dashboard>
