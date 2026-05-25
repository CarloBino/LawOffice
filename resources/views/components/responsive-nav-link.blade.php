@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#c7a47b] text-start text-base font-semibold text-white bg-[#554b45] focus:outline-none focus:text-white focus:bg-[#554b45] focus:border-[#c7a47b] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#d1d2cd] hover:text-white hover:bg-[#554b45] hover:border-[#9f7957] focus:outline-none focus:text-white focus:bg-[#554b45] focus:border-[#9f7957] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
