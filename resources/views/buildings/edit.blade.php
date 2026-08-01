<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Edit Building</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('buildings.update', $building) }}">
            @method('PATCH')
            @include('buildings._form', ['building' => $building])
        </form>
    </section>
</x-app-layout>
