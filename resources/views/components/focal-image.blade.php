@props([
    'src',
    'focal' => '',
    'alt' => '',
    'imgClass' => 'w-full h-full object-cover',
])

<div {{ $attributes->merge(['class' => 'overflow-hidden']) }}>
    <img src="{{ $src }}" alt="{{ $alt }}" class="{{ $imgClass }}" style="{{ $focal }}">
</div>
