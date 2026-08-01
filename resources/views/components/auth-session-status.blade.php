@props(['status'])

@if ($status)
    <div role="status" aria-live="polite" {{ $attributes->merge(['class' => 'text-sm font-medium text-emerald-700']) }}>
        {{ $status }}
    </div>
@endif
