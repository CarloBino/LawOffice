@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#c7a47b] text-sm font-semibold leading-5 text-white focus:outline-none focus:border-[#c7a47b] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#d1d2cd] hover:text-white hover:border-[#9f7957] focus:outline-none focus:text-white focus:border-[#9f7957] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
