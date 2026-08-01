<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Create Housekeeping Task</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('housekeeping.manage.store') }}">
            @include('housekeeping._form')
        </form>
    </section>
</x-app-layout>
