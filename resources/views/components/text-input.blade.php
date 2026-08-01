@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge(['class' => 'rounded-md border-slate-300 shadow-sm transition focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-slate-100']) }}>
