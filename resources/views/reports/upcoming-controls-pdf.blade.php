<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Próximos controles</title>

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

            .subtitle {
                text-align: center;
                font-size: 11px;
                color: #4b5563;
                margin-bottom: 14px;
            }

            .summary {
                margin-bottom: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th {
                background: #e5e7eb;
                font-weight: bold;
            }

            th,
            td {
                border: 1px solid #9ca3af;
                padding: 6px;
                vertical-align: top;
            }

            .text-center {
                text-align: center;
            }
        </style>
    </head>

    <body>
        <h1>Reporte de próximos controles</h1>

        <div class="subtitle">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>

        <div class="summary">
            <strong>Desde:</strong> {{ $filters['from'] ?? 'Todos' }}
            &nbsp; | &nbsp;
            <strong>Hasta:</strong> {{ $filters['to'] ?? 'Todos' }}
            &nbsp; | &nbsp;
            <strong>Total:</strong> {{ $records->count() }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Fecha control</th>
                    <th>Estado</th>
                    <th>Mascota</th>
                    <th>Propietario</th>
                    <th>Teléfono</th>
                    <th>Veterinario</th>
                    <th>Último motivo</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($records as $record)
                    @php
                        $days = Carbon\Carbon::today()
                            ->diffInDays(Carbon\Carbon::parse($record->next_control_date), false);
                    @endphp

                    <tr>
                        <td class="text-center">
                            {{ Carbon\Carbon::parse($record->next_control_date)->format('d/m/Y') }}
                        </td>

                        <td class="text-center">
                            @if ($days < 0)
                                Vencido {{ abs($days) }} días
                            @elseif ($days === 0)
                                Hoy
                            @else
                                {{ $days }} días
                            @endif
                        </td>

                        <td>{{ $record->pet?->name }}</td>
                        <td>{{ $record->pet?->customer?->name }}</td>
                        <td>{{ $record->pet?->customer?->phone }}</td>
                        <td>{{ $record->assignedUser?->name }}</td>
                        <td>{{ $record->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            No hay próximos controles para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>

</html>