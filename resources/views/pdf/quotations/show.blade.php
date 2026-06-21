<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Cotización {{ $quotation->number }}</title>

        <style>
            body {
                font-family: sans-serif;
                font-size: 12px;
                color: #111827;
            }

            .header {
                width: 100%;
                border-bottom: 2px solid #0f766e;
                padding-bottom: 10px;
                margin-bottom: 18px;
            }

            .title {
                font-size: 22px;
                font-weight: bold;
                color: #0f766e;
            }

            .subtitle {
                font-size: 12px;
                color: #374151;
            }

            .box {
                border: 1px solid #d1d5db;
                border-radius: 6px;
                padding: 10px;
                margin-bottom: 12px;
            }

            .box-title {
                font-weight: bold;
                font-size: 13px;
                margin-bottom: 8px;
                color: #0f766e;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th {
                background: #0f766e;
                color: white;
                padding: 7px;
                font-size: 11px;
                text-align: left;
            }

            td {
                border-bottom: 1px solid #e5e7eb;
                padding: 7px;
                font-size: 11px;
            }

            .text-right {
                text-align: right;
            }

            .totals {
                width: 40%;
                margin-left: auto;
                margin-top: 15px;
            }

            .totals td {
                padding: 6px;
                border-bottom: 1px solid #e5e7eb;
            }

            .total-final {
                font-weight: bold;
                font-size: 14px;
                color: #0f766e;
            }

            .footer {
                margin-top: 25px;
                font-size: 10px;
                color: #6b7280;
                border-top: 1px solid #d1d5db;
                padding-top: 10px;
            }

            .badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                background: #ccfbf1;
                color: #115e59;
                font-weight: bold;
            }
        </style>
    </head>

    <body>

        <table class="header">
            <tr>
                <td>
                    <div class="title">Cotización</div>
                    <div class="subtitle">Sistema veterinario</div>
                </td>
                <td class="text-right">
                    <strong>No.</strong> {{ $quotation->number }} <br>
                    <strong>Fecha:</strong> {{ $quotation->quotation_date?->format('d/m/Y') }} <br>
                    <strong>Válida hasta:</strong> {{ $quotation->valid_until?->format('d/m/Y') ?? 'No definida' }} <br>
                    <strong>Estado:</strong> <span class="badge">{{ $quotation->status }}</span>
                </td>
            </tr>
        </table>

        <div class="box">
            <div class="box-title">Datos del cliente</div>

            <table>
                <tr>
                    <td>
                        <strong>Cliente:</strong>
                        {{ $quotation->customer?->name }}
                    </td>
                    <td>
                        <strong>Teléfono:</strong>
                        {{ $quotation->customer?->phone ?? 'N/A' }}
                    </td>
                    <td>
                        <strong>Correo:</strong>
                        {{ $quotation->customer?->email ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <strong>Dirección:</strong>
                        {{ $quotation->customer?->address ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>

        @if($quotation->pet)
            <div class="box">
                <div class="box-title">Datos de la mascota</div>

                <table>
                    <tr>
                        <td>
                            <strong>Mascota:</strong>
                            {{ $quotation->pet?->name }}
                        </td>
                        <td>
                            <strong>Especie:</strong>
                            {{ $quotation->pet?->species?->name ?? 'N/A' }}
                        </td>
                        <td>
                            <strong>Raza:</strong>
                            {{ $quotation->pet?->breed?->name ?? 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="box">
            <div class="box-title">Servicios cotizados</div>

            <table>
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio ref.</th>
                        <th class="text-right">Precio cotizado</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">Q {{ number_format($item->reference_price, 2) }}</td>
                            <td class="text-right">Q {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">Q {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right">Q {{ number_format($quotation->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Descuento</strong></td>
                    <td class="text-right">Q {{ number_format($quotation->discount, 2) }}</td>
                </tr>
                <tr>
                    <td class="total-final">Total</td>
                    <td class="text-right total-final">
                        Q {{ number_format($quotation->total, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        @if($quotation->notes)
            <div class="box">
                <div class="box-title">Observaciones</div>
                {{ $quotation->notes }}
            </div>
        @endif

        <div class="footer">
            Esta cotización es válida hasta la fecha indicada. Los precios pueden variar después del vencimiento.
            <br>
            Generada por: {{ $quotation->creator?->name ?? 'Sistema' }}
        </div>

    </body>

</html>