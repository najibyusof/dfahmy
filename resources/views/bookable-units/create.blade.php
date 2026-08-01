<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">New Bookable Unit</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('bookable-units.store') }}">
            @include('bookable-units._form')
        </form>
    </section>
</x-app-layout>
