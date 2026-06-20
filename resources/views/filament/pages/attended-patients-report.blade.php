<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>
    <div class="mt-4 flex justify-end">
        <x-filament::button icon="heroicon-o-printer" wire:click="generatePdf">
            Generar PDF
        </x-filament::button>
    </div>
    <div class="mt-6">
        <x-filament::section>
            <x-slot name="heading">
                Resultados
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="px-3 py-2">Mascota</th>
                            <th class="px-3 py-2">Dueño</th>
                            <th class="px-3 py-2">Especie</th>
                            <th class="px-3 py-2">Raza</th>
                            <th class="px-3 py-2">Consultas</th>
                            <th class="px-3 py-2">Última atención</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->getPatients() as $record)
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    {{ $record->pet?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $record->pet?->customer?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $record->pet?->species?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $record->pet?->breed?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $record->total_consultations }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ \Carbon\Carbon::parse($record->last_attention)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                    No hay pacientes atendidos en este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>