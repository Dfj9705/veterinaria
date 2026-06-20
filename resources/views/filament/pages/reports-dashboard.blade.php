<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Citas de hoy
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $stats['appointments_today'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Citas finalizadas hoy
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $stats['finished_today'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Pacientes atendidos este mes
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $stats['patients_attended_month'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Próximos controles, 7 días
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $stats['next_controls'] }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>