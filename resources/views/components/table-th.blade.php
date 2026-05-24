@props(['force' => false, 'align' => 'left', 'sizeClass' => 'text-xs', 'extra' => ''])
@php
    $base = 'px-3 py-2 md:px-6 md:py-3 ' . $sizeClass . ' font-medium text-gray-500 uppercase tracking-wider';
    $alignClass = $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left');
    $forceClass = $force ? 'hidden force-md' : '';
    $classes = trim("{$base} {$alignClass} {$forceClass} {$extra}");
@endphp
<th {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</th>
