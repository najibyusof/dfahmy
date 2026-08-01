@props(['messages'])

@if ($messages)
    <ul role="alert" aria-live="assertive" {{ $attributes->merge(['class' => 'space-y-1 text-sm text-red-700']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
