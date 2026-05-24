<x-client.layout.dashboard :title="$material->title" activePage="lessons">
  <x-slot:head>
    <style>
      .reader-content{font-size:16px;line-height:1.9;color:#0E101A;}
      .reader-content p{margin-bottom:1rem;}
      .reader-content h2,.reader-content h3{font-weight:700;margin:1.5rem 0 .75rem;color:#0E101A;}
      .reader-content ul,.reader-content ol{margin:1rem 0 1rem 1.25rem;}
      .reader-content li{margin:.4rem 0;}
      .vocab-highlight{border-bottom:2px solid #11A683;position:relative;cursor:pointer;background:rgba(17,166,131,.06);border-radius:3px;padding:0 2px;}
      .vocab-highlight .tooltip-card{display:none;position:absolute;z-index:30;bottom:calc(100% + 10px);left:0;width:260px;background:#fff;color:#0E101A;border-radius:12px;padding:14px;box-shadow:0 8px 24px rgba(0,0,0,.12),0 2px 8px rgba(0,0,0,.08);border:1px solid #E1E4E8;font-size:12px;line-height:1.6;}
      .vocab-highlight:hover .tooltip-card,.vocab-highlight:focus .tooltip-card{display:block;}
      @media(prefers-reduced-motion:reduce){.vocab-highlight{transition:none;}}
    </style>
  </x-slot:head>

  <div class="max-w-4xl mx-auto">
    <div class="mb-5 flex items-center gap-2 text-sm">
      <a href="{{ route('client.lessons.library', ['tab' => 'materials']) }}" class="text-text-secondary hover:text-brand transition-colors cursor-pointer">Học liệu mở rộng</a>
      <svg class="w-3.5 h-3.5 text-text-disabled" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      <span class="text-text-primary font-medium truncate">{{ $material->title }}</span>
    </div>

    <article class="bg-white border border-border-light rounded-2xl shadow-card overflow-hidden">
      @if($material->image_path)
        <img src="{{ Storage::disk('public')->url($material->image_path) }}" alt="{{ $material->title }}" class="w-full h-56 sm:h-72 object-cover" />
      @endif
      <div class="p-6 sm:p-8">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <span class="px-2.5 py-1 bg-brand-light text-brand text-xs font-semibold rounded-full">{{ Str::headline($material->topic) }}</span>
          <span class="text-xs text-text-disabled">{{ $material->views_count }} lượt xem</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-text-primary leading-tight">{{ $material->title }}</h1>
        @if($material->excerpt)
          <p class="mt-3 text-base text-text-secondary leading-relaxed">{{ $material->excerpt }}</p>
        @endif
        <div id="reading-content" class="reader-content mt-8">
          {!! $material->content !!}
        </div>
      </div>
    </article>
  </div>

  <x-slot:scripts>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const notes = @json($material->vocabulary_notes ?? []);
        const root = document.getElementById('reading-content');
        if (!root || notes.length === 0) {
          return;
        }

        const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const validNotes = notes
          .filter((note) => note.term && note.meaning)
          .sort((a, b) => b.term.length - a.term.length);

        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
          acceptNode(node) {
            if (!node.nodeValue.trim() || node.parentElement.closest('script,style,a,.vocab-highlight')) {
              return NodeFilter.FILTER_REJECT;
            }
            return NodeFilter.FILTER_ACCEPT;
          },
        });

        const textNodes = [];
        while (walker.nextNode()) {
          textNodes.push(walker.currentNode);
        }

        textNodes.forEach((node) => {
          let html = node.nodeValue;
          let changed = false;

          validNotes.forEach((note) => {
            const pattern = new RegExp(`\\b(${escapeRegex(note.term)})\\b`, 'giu');
            html = html.replace(pattern, (match) => {
              changed = true;
              const tooltip = `<span class="tooltip-card"><strong>${note.term}</strong><br>${note.meaning}${note.note ? `<br><span class="text-text-secondary">${note.note}</span>` : ''}</span>`;
              return `<span class="vocab-highlight" tabindex="0">${match}${tooltip}</span>`;
            });
          });

          if (changed) {
            const template = document.createElement('template');
            template.innerHTML = html;
            node.replaceWith(template.content);
          }
        });
      });
    </script>
  </x-slot:scripts>
</x-client.layout.dashboard>
