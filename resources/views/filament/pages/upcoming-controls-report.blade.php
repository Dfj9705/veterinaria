<x-filament-panels::page>

    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $stats = $this->getStats();
    @endphp

    <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-4">

        <x-filament::section>
            <div class="text-sm text-gray-500">
                Controles vencidos
            </div>

            <div class="text-3xl font-bold">
                {{ $stats['expired'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">
                Para hoy
            </div>

            <div class="text-3xl font-bold">
                {{ $stats['today'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">
                Próximos 7 días
            </div>

            <div class="text-3xl font-bold">
                {{ $stats['week'] }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">
                Próximos 30 días
            </div>

            <div class="text-3xl font-bold">
                {{ $stats['month'] }}
            </div>
        </x-filament::section>

    </div>

    <div class="mt-4 flex justify-end">
        <x-filament::button icon="heroicon-o-printer" wire:click="generatePdf">
            Generar PDF
        </x-filament::button>
    </div>

    <div class="mt-6">
        <x-filament::section>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="p-2 text-left">Fecha</th>
                        <th class="p-2 text-left">Estado</th>
                        <th class="p-2 text-left">Mascota</th>
                        <th class="p-2 text-left">Propietario</th>
                        <th class="p-2 text-left">Teléfono</th>
                        <th class="p-2 text-left">Veterinario</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($this->getRecords() as $record)

                        @php
                            $days = $this->daysRemaining($record->next_control_date);
                        @endphp

                        <tr class="border-b">

                            <td class="p-2">
                                {{ \Carbon\Carbon::parse($record->next_control_date)->format('d/m/Y') }}
                            </td>

                            <td class="p-2">
                                @if($days < 0)
                                    Vencido {{ abs($days) }} días
                                @elseif($days === 0)
                                    Hoy
                                @else
                                    {{ $days }} días
                                @endif
                            </td>

                            <td class="p-2">
                                {{ $record->pet?->name }}
                            </td>

                            <td class="p-2">
                                {{ $record->pet?->customer?->name }}
                            </td>

                            <td class="p-2">
                                {{ $record->pet?->customer?->phone }}
                            </td>

                            <td class="p-2">
                                {{ $record->veterinarian?->name }}
                            </td>

                        </tr>

                    @endforeach
                </tbody>

            </table>

        </x-filament::section>
    </div>

</x-filament-panels::page>