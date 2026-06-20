<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $pet = $this->getPet();
    @endphp

    @if ($pet)
        <div class="mt-4 flex justify-end">
            <x-filament::button icon="heroicon-o-printer" wire:click="generatePdf">
                Generar expediente PDF
            </x-filament::button>
        </div>

        <div class="mt-6 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Datos del paciente
                </x-slot>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <strong>Mascota:</strong><br>
                        {{ $pet->name }}
                    </div>

                    <div>
                        <strong>Propietario:</strong><br>
                        {{ $pet->customer?->name }}
                    </div>

                    <div>
                        <strong>Teléfono:</strong><br>
                        {{ $pet->customer?->phone }}
                    </div>

                    <div>
                        <strong>Especie:</strong><br>
                        {{ $pet->species?->name }}
                    </div>

                    <div>
                        <strong>Raza:</strong><br>
                        {{ $pet->breed?->name }}
                    </div>

                    <div>
                        <strong>Sexo:</strong><br>
                        {{ $pet->sex }}
                    </div>

                    <div>
                        <strong>Peso:</strong><br>
                        {{ $pet->weight }} kg
                    </div>

                    <div>
                        <strong>Color:</strong><br>
                        {{ $pet->color }}
                    </div>

                    <div>
                        <strong>Estado:</strong><br>
                        {{ $pet->status }}
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Historial clínico
                </x-slot>

                <div class="space-y-4">
                    @forelse ($pet->clinicalRecords as $record)
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="mb-3 flex flex-col justify-between gap-2 md:flex-row">
                                <div>
                                    <strong>
                                        {{ $record->created_at?->format('d/m/Y H:i') }}
                                    </strong>
                                </div>

                                <div>
                                    Veterinario:
                                    <strong>{{ $record->veterinarian?->name }}</strong>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <strong>Motivo:</strong><br>
                                    {{ $record->reason }}
                                </div>

                                <div>
                                    <strong>Síntomas:</strong><br>
                                    {{ $record->symptoms }}
                                </div>

                                <div>
                                    <strong>Diagnóstico:</strong><br>
                                    {{ $record->diagnosis }}
                                </div>

                                <div>
                                    <strong>Tratamiento:</strong><br>
                                    {{ $record->treatment }}
                                </div>

                                <div>
                                    <strong>Próximo control:</strong><br>
                                    {{ $record->next_control_date ? \Carbon\Carbon::parse($record->next_control_date)->format('d/m/Y') : 'No definido' }}
                                </div>

                                <div>
                                    <strong>Recetas asociadas:</strong><br>
                                    {{ $record->prescriptions->count() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500">
                            Esta mascota no tiene historial clínico registrado.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>