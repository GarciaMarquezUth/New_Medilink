@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 transition duration-200'
            : 'inline-flex items-center rounded-2xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition duration-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500/30';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
