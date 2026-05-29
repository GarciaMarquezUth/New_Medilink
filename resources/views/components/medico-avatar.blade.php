@props([
    'medico' => null,
    'class' => 'h-12 w-12 rounded-2xl',
    'textClass' => 'text-sm',
])

@php
    $fallbackClass = $class.' flex items-center justify-center bg-gradient-to-br from-violet-700 to-purple-600 font-black text-white shadow-sm shadow-violet-700/20 ring-1 ring-violet-100 '.$textClass;
@endphp

@if($medico?->photo_url)
    <span {{ $attributes->merge(['class' => $class.' relative inline-flex overflow-hidden bg-violet-100 ring-1 ring-violet-100']) }}>
        <img src="{{ $medico->photo_url }}" alt="Foto de {{ $medico->nombre }} {{ $medico->apellido }}" class="absolute inset-0 h-full w-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
        <span class="hidden {{ $fallbackClass }} h-full w-full rounded-none">
            {{ $medico?->initials ?? 'DR' }}
        </span>
    </span>
@else
    <div {{ $attributes->merge(['class' => $fallbackClass]) }}>
        {{ $medico?->initials ?? 'DR' }}
    </div>
@endif
