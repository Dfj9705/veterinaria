<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Mpdf\Mpdf;

class PrescriptionPdfController extends Controller
{
    public function show(Prescription $prescription)
    {
        $prescription->load([
            'pet.customer',
            'customer',
            'veterinarian',
            'medicalRecord',
            'items',
        ]);

        $html = view('pdf.prescription', [
            'prescription' => $prescription,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $mpdf->SetTitle('Receta ' . $prescription->prescription_number);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="receta-' . $prescription->prescription_number . '.pdf"'
            );
    }
}
