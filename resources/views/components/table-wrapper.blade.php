@props(['minWidth' => null, 'class' => ''])
<div {{ $attributes->merge(['class' => 'overflow-x-auto border border-gray-200 rounded-lg']) }}>
    <table {{ $attributes->merge(['class' => trim('w-full ' . $class . ($minWidth ? " lg:min-w-[{$minWidth}]" : '')) ]) }}>
        {{ $slot }}
    </table>
</div>
