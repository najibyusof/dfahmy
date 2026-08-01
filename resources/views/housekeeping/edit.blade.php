<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Edit Housekeeping Task</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('housekeeping.manage.update', $task) }}">
            @method('PATCH')
            @include('housekeeping._form')
        </form>
    </section>
</x-app-layout>
