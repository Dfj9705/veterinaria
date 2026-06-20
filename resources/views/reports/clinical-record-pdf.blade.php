<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Expediente clínico</title>

        <style>
            body {
                font-family: sans-serif;
                font-size: 11px;
                color: #111827;
            }

            h1 {
                text-align: center;
                font-size: 18px;
                margin-bottom: 4px;
            }

            h2 {
                font-size: 14px;
                margin-top: 18px;
                margin-bottom: 8px;
                border-bottom: 1px solid #9ca3af;
                padding-bottom: 4px;
            }

            h3 {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .subtitle {
                text-align: center;
                color: #4b5563;
                margin-bottom: 16px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 12px;
            }

            th,
            td {
                border: 1px solid #9ca3af;
                padding: 6px;
                vertical-align: top;
            }

            th {
                background: #e5e7eb;
                font-weight: bold;
                text-align: left;
            }

            .record {
                border: 1px solid #9ca3af;
                padding: 8px;
                margin-bottom: 12px;
            }

            .record-header {
                background: #f3f4f6;
                padding: 6px;
                margin: -8px -8px 8px -8px;
                font-weight: bold;
            }

            .muted {
                color: #6b7280;
            }

            .text-center {
                text-align: center;
            }
        </style>
    </head>

    <body>
        <h1>Expediente clínico</h1>

        <div class="subtitle">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>

        <h2>Datos del paciente</h2>

        <table>
            <tr>
                <th>Mascota</th>
                <td>{{ $pet->name }}</td>

                <th>Propietario</th>
                <td>{{ $pet->customer?->name }}</td>
            </tr>

            <tr>
                <th>Teléfono</th>
                <td>{{ $pet->customer?->phone }}</td>

                <th>Especie</th>
                <td>{{ $pet->species?->name }}</td>
            </tr>

            <tr>
                <th>Raza</th>
                <td>{{ $pet->breed?->name }}</td>

                <th>Sexo</th>
                <td>{{ $pet->sex }}</td>
            </tr>

            <tr>
                <th>Peso</th>
                <td>{{ $pet->weight }} kg</td>

                <th>Color</th>
                <td>{{ $pet->color }}</td>
            </tr>

            <tr>
                <th>Alergias</th>
                <td colspan="3">{{ $pet->allergies ?: 'No registradas' }}</td>
            </tr>

            <tr>
                <th>Observaciones</th>
                <td colspan="3">{{ $pet->observations ?: 'Sin observaciones' }}</td>
            </tr>
        </table>

        <h2>Resumen</h2>

        <table>
            <tr>
                <th>Total de consultas</th>
                <td>{{ $pet->clinicalRecords->count() }}</td>

                <th>Total de recetas</th>
                <td>{{ $pet->clinicalRecords->sum(fn($record) => $record->prescriptions->count()) }}</td>
            </tr>

            <tr>
                <th>Última atención</th>
                <td>
                    {{ $pet->clinicalRecords->first()?->created_at?->format('d/m/Y H:i') ?? 'Sin registros' }}
                </td>

                <th>Próximo control</th>
                <td>
                    @php
                        $nextControl = $pet->clinicalRecords
                            ->whereNotNull('next_control_date')
                            ->sortBy('next_control_date')
                            ->first();
                    @endphp

                    {{ $nextControl?->next_control_date
    ? \Carbon\Carbon::parse($nextControl->next_control_date)->format('d/m/Y')
    : 'No definido' }}
                </td>
            </tr>
        </table>

        <h2>Historial clínico cronológico</h2>

        @forelse ($pet->clinicalRecords as $record)
            <div class="record">
                <div class="record-header">
                    Consulta del {{ $record->created_at?->format('d/m/Y H:i') }}
                    —
                    Veterinario: {{ $record->veterinarian?->name ?? 'No asignado' }}
                </div>

                <table>
                    <tr>
                        <th>Motivo</th>
                        <td>{{ $record->reason }}</td>
                    </tr>

                    <tr>
                        <th>Síntomas</th>
                        <td>{{ $record->symptoms }}</td>
                    </tr>

                    <tr>
                        <th>Diagnóstico</th>
                        <td>{{ $record->diagnosis }}</td>
                    </tr>

                    <tr>
                        <th>Peso</th>
                        <td>{{ $record->weight ? $record->weight . ' kg' : 'No registrado' }}</td>
                    </tr>

                    <tr>
                        <th>Temperatura</th>
                        <td>{{ $record->temperature ? $record->temperature . ' °C' : 'No registrada' }}</td>
                    </tr>

                    <tr>
                        <th>Signos relevantes</th>
                        <td>{{ $record->relevant_signs ?: 'No registrados' }}</td>
                    </tr>

                    <tr>
                        <th>Tratamiento</th>
                        <td>{{ $record->treatment }}</td>
                    </tr>

                    <tr>
                        <th>Observaciones médicas</th>
                        <td>{{ $record->medical_observations ?: 'Sin observaciones' }}</td>
                    </tr>

                    <tr>
                        <th>Próximo control</th>
                        <td>
                            {{ $record->next_control_date
            ? \Carbon\Carbon::parse($record->next_control_date)->format('d/m/Y')
            : 'No definido' }}
                        </td>
                    </tr>
                </table>

                <h3>Recetas asociadas</h3>

                @forelse ($record->prescriptions as $prescription)
                    <table>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ $prescription->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>

                        <tr>
                            <th>Medicamentos</th>
                            <td>
                                @forelse ($prescription->items as $item)
                                    <div>
                                        <strong>{{ $item->medicine }}</strong><br>
                                        Dosis: {{ $item->dose }}<br>
                                        Frecuencia: {{ $item->frequency }}<br>
                                        Duración: {{ $item->duration }}<br>
                                        Indicaciones: {{ $item->instructions ?: 'Sin indicaciones' }}
                                    </div>

                                    @if (!$loop->last)
                                        <hr>
                                    @endif
                                @empty
                                    Sin medicamentos registrados.
                                @endforelse
                            </td>
                        </tr>

                        <tr>
                            <th>Indicaciones especiales</th>
                            <td>{!! nl2br(e($prescription->special_instructions)) !!}</td>
                        </tr>
                    </table>
                @empty
                    <p class="muted">No hay recetas asociadas a esta consulta.</p>
                @endforelse
            </div>
        @empty
            <p class="text-center muted">
                Esta mascota no tiene historial clínico registrado.
            </p>
        @endforelse
    </body>

</html>