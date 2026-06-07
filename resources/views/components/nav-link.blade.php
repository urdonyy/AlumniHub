@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex flex-col items-center justify-center gap-1 px-1 border-b-2 border-red-900 text-sm font-medium leading-5 text-red-900 focus:outline-none focus:border-red-900 transition duration-150 ease-in-out'
        : 'inline-flex flex-col items-center justify-center gap-1 px-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-600 hover:text-[#FFC107] hover:border-[#FFC107] focus:outline-none focus:text-[#FFC107]focus:border-[#FFC107] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>