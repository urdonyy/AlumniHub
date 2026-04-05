@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-none focus:ring-yellow-500 rounded-md shadow-sm']) }}>