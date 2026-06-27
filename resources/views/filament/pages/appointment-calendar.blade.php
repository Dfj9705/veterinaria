<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-4 border-b border-gray-200 dark:border-white/10">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Filtrar por veterinario
            </label>

            <select id="veterinarian-filter"
                class="mt-2 block w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">Todos los responsables</option>

                @foreach ($veterinarians as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div id="appointment-calendar" data-events='@json($events)' data-create-url="{{ $createUrl }}" class="p-4">
        </div>
    </div>

    @vite([
        'resources/css/appointment-calendar.css',
        'resources/js/appointment-calendar.js',
    ])
</x-filament-panels::page>