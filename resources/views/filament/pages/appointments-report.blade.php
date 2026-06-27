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
                            <th class="px-3 py-2">Fecha</th>
                            <th class="px-3 py-2">Hora</th>
                            <th class="px-3 py-2">Cliente</th>
                            <th class="px-3 py-2">Mascota</th>
                            <th class="px-3 py-2">Servicio</th>
                            <th class="px-3 py-2">Responsable</th>
                            <th class="px-3 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->getAppointments() as $appointment)
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    {{ $appointment->appointment_date?->format('d/m/Y') ?? $appointment->appointment_date }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->appointment_time }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->customer?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->pet?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->service?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->assignedUser?->name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $appointment->status }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                    No hay citas para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>