<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Import Rooms from CSV</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-600">
            Upload a CSV file using the exported room template headers:
            <span
                class="font-medium text-slate-800">building_code,name,code,floor,room_type,status,base_nightly_rate,maximum_guests,notes,is_active,queen_bed_quantity,sofa_bed_quantity</span>.
        </p>

        <form method="POST" action="{{ route('rooms.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="csv_file" class="block text-sm font-medium text-slate-700">CSV File</label>
                <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>
                @error('csv_file')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Import</button>
                <a href="{{ route('rooms.export') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Download
                    CSV Template</a>
                <a href="{{ route('rooms.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back
                    to Rooms</a>
            </div>
        </form>
    </section>
</x-app-layout>
