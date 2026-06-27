<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Reporte de citas</title>

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
                margin-bottom: 16px;
                color: #4b5563;
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
            }

            .text-center {
                text-align: center;
            }

            .summary {
                margin-bottom: 12px;
                font-size: 11px;
            }
        </style>
    </head>

    <body>
        <h1>Reporte de citas</h1>

        <div class="subtitle">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>

        <div class="summary">
            <strong>Desde:</strong> {{ $filters['from'] ?? 'Todos' }}
            &nbsp; | &nbsp;
            <strong>Hasta:</strong> {{ $filters['to'] ?? 'Todos' }}
            &nbsp; | &nbsp;
            <strong>Estado:</strong> {{ $filters['status'] ?? 'Todos' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Mascota</th>
                    <th>Servicio</th>
                    <th>Veterinario</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="text-center">
                            {{ $appointment->appointment_date?->format('d/m/Y') ?? $appointment->appointment_date }}
                        </td>
                        <td class="text-center">
                            {{ $appointment->appointment_time }}
                        </td>
                        <td>
                            {{ $appointment->customer?->name }}
                        </td>
                        <td>
                            {{ $appointment->pet?->name }}
                        </td>
                        <td>
                            {{ $appointment->service?->name }}
                        </td>
                        <td>
                            {{ $appointment->assignedUser?->name }}
                        </td>
                        <td class="text-center">
                            {{ $appointment->status }}
                        </td>
                        <td>
                            {{ $appointment->reason }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            No hay citas para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <br>

        <strong>Total de citas:</strong> {{ $appointments->count() }}
    </body>

</html>