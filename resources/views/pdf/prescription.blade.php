<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                color: #222;
            }

            .header {
                text-align: center;
                border-bottom: 2px solid #222;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }

            .title {
                font-size: 20px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .subtitle {
                font-size: 12px;
                color: #555;
            }

            .info-table,
            .med-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 12px;
            }

            .info-table td {
                padding: 5px;
                vertical-align: top;
            }

            .label {
                font-weight: bold;
                width: 18%;
            }

            .med-table th {
                background: #f0f0f0;
                border: 1px solid #999;
                padding: 6px;
                text-align: left;
            }

            .med-table td {
                border: 1px solid #999;
                padding: 6px;
            }

            .section-title {
                font-weight: bold;
                font-size: 14px;
                margin-top: 18px;
                border-bottom: 1px solid #aaa;
                padding-bottom: 4px;
            }

            .instructions {
                min-height: 70px;
                border: 1px solid #aaa;
                padding: 8px;
                margin-top: 8px;
            }

            .signature {
                margin-top: 60px;
                text-align: center;
            }

            .signature-line {
                border-top: 1px solid #222;
                width: 260px;
                margin: 0 auto;
                padding-top: 6px;
            }

            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
                color: #666;
            }
        </style>
    </head>

    <body>

        <div class="header">
            <div class="title">Receta médica veterinaria</div>
            <div class="subtitle">Clínica Veterinaria</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">No. receta:</td>
                <td>{{ $prescription->prescription_number }}</td>

                <td class="label">Fecha:</td>
                <td>{{ $prescription->created_at?->format('d/m/Y H:i') }}</td>
            </tr>

            <tr>
                <td class="label">Cliente:</td>
                <td>{{ $prescription->customer?->name }}</td>

                <td class="label">Mascota:</td>
                <td>{{ $prescription->pet?->name }}</td>
            </tr>

            <tr>
                <td class="label">Veterinario:</td>
                <td>{{ $prescription->veterinarian?->name }}</td>

                <td class="label">Historial:</td>
                <td>#{{ $prescription->clinical_record_id }}</td>
            </tr>

            <tr>
                <td class="label">Especie:</td>
                <td>{{ $prescription->pet?->species?->name ?? 'N/A' }}</td>

                <td class="label">Raza:</td>
                <td>{{ $prescription->pet?->breed?->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">Medicamentos indicados</div>

        <table class="med-table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Frecuencia</th>
                    <th>Duración</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prescription->items as $item)
                    <tr>
                        <td>{{ $item->medication }}</td>
                        <td>{{ $item->dosage }}</td>
                        <td>{{ $item->frequency }}</td>
                        <td>{{ $item->duration }}</td>
                        <td>{{ $item->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Indicaciones generales</div>

        <div class="instructions">
            {{ $prescription->instructions ?: 'Sin indicaciones adicionales.' }}
        </div>

        <div class="signature">
            <div class="signature-line">
                Firma y sello del médico veterinario
            </div>
        </div>

        <div class="footer">
            Documento generado digitalmente
        </div>

    </body>

</html>