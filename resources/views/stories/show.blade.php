<x-layouts.public :title="$story->title">
    <a href="{{ route('home') }}" class="text-sm text-emerald-700 hover:underline">&larr; กลับหน้ารวมเรื่องราว</a>

    <span class="inline-block text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded mt-4">{{ $story->category_label }}</span>
    <h1 class="text-2xl font-bold mt-2 mb-2">{{ $story->title }}</h1>
    <p class="text-xs text-gray-400 mb-6">{{ $story->created_at->format('d/m/Y H:i') }}</p>

    @if ($story->video)
        <video src="{{ $story->video_url }}" controls class="w-full rounded-lg mb-6 max-h-96"></video>
    @endif

    @if ($story->images->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            @foreach ($story->images as $image)
                <img src="{{ $image->url }}" alt="{{ $story->title }}" class="w-full rounded-lg object-cover max-h-96">
            @endforeach
        </div>
    @endif

    <div class="prose max-w-none whitespace-pre-line">{{ $story->description }}</div>
</x-layouts.public>
