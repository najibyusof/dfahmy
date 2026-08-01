<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            Roles & Permissions Matrix
        </h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Access Matrix</h3>
        <p class="mt-1 text-sm text-slate-600">Read-only overview of permissions assigned to each role.</p>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Permission</th>
                        @foreach ($roles as $role)
                            <th class="px-4 py-3">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @foreach ($permissions as $permission)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $permission }}</td>
                            @foreach ($roles as $role)
                                <td class="px-4 py-3">
                                    @if ($role->permissions->contains('name', $permission))
                                        <span
                                            class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Yes</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">No</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
