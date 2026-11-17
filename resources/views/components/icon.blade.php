@props(['name'])
<svg {{ $attributes->merge(['class'=>'icon']) }} aria-hidden="true" focusable="false"><use href="{{ asset('icons.svg') }}#{{ $name }}"></use></svg>
