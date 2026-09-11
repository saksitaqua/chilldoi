<a href="{{ route('stories.show', $story) }}" class="block bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
    @if ($story->images->first())
        <img src="{{ $story->images->first()->url }}" alt="{{ $story->title }}" class="w-full h-48 object-cover">
    @else
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">ไม่มีรูปภาพ</div>
    @endif
    <div class="p-4">
        <span class="inline-block text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded mb-2">{{ $story->category_label }}</span>
        <h2 class="font-semibold text-lg mb-1">{{ $story->title }}</h2>
        <p class="text-sm text-gray-500 line-clamp-2">{{ $story->description }}</p>
        <p class="text-xs text-gray-400 mt-2">{{ $story->created_at->format('d/m/Y') }}</p>
    </div>
</a>
