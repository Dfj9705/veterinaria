<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Mpdf\Mpdf;

class QuotationPdfController extends Controller
{
    public function generate(Quotation $quotation)
    {

        if ($quotation->status === 'Borrador') {
            $quotation->update([
                'status' => 'Enviada',
            ]);
        }
        $quotation->load([
            'customer',
            'pet.species',
            'pet.breed',
            'items.service',
            'creator',
        ]);

        $html = view('pdf.quotations.show', [
            'quotation' => $quotation,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output("cotizacion-{$quotation->number}.pdf", 'I'))
            ->header('Content-Type', 'application/pdf');
    }
}