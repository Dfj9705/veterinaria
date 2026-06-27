<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalRecord;
use App\Models\Pet;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class ReportController extends Controller
{
    public function appointmentsPdf(Request $request)
    {
        $appointments = Appointment::query()
            ->with([
                'customer',
                'pet',
                'service',
                'assignedUser',
            ])
            ->when($request->from, function (Builder $query, $date) {
                $query->whereDate('appointment_date', '>=', $date);
            })
            ->when($request->to, function (Builder $query, $date) {
                $query->whereDate('appointment_date', '<=', $date);
            })
            ->when($request->status, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($request->service_id, function (Builder $query, $serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->when($request->assigned_user_id, function (Builder $query, $assignedUserId) {
                $query->where('assigned_user_id', $assignedUserId);
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $html = view('reports.appointments-pdf', [
            'appointments' => $appointments,
            'filters' => $request->all(),
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'orientation' => 'L',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('reporte-citas.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    public function clinicalRecordPdf(Pet $pet)
    {
        $pet->load([
            'customer',
            'species',
            'breed',
            'clinicalRecords' => fn($query) => $query->latest(),
            'clinicalRecords.assignedUser',
            'clinicalRecords.prescriptions',
            'clinicalRecords.prescriptions.items',
        ]);

        $html = view('reports.clinical-record-pdf', [
            'pet' => $pet,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('expediente-clinico.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    public function upcomingControlsPdf(Request $request)
    {
        $records = ClinicalRecord::query()
            ->with([
                'pet.customer',
                'assignedUser',
            ])
            ->whereNotNull('next_control_date')
            ->when(
                $request->from,
                fn($q, $date) =>
                $q->whereDate('next_control_date', '>=', $date)
            )
            ->when(
                $request->to,
                fn($q, $date) =>
                $q->whereDate('next_control_date', '<=', $date)
            )
            ->when(
                $request->assigned_user_id,
                fn($q, $id) =>
                $q->where('assigned_user_id', $id)
            )
            ->orderBy('next_control_date')
            ->get()
            ->unique('pet_id')
            ->values();

        $html = view('reports.upcoming-controls-pdf', [
            'records' => $records,
            'filters' => $request->all(),
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'orientation' => 'L',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('proximos-controles.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    public function attendedPatientsPdf(Request $request)
    {
        $patients = ClinicalRecord::query()
            ->select([
                'pet_id',
                DB::raw('COUNT(*) as total_consultations'),
                DB::raw('MAX(created_at) as last_attention'),
            ])
            ->with([
                'pet.customer',
                'pet.species',
                'pet.breed',
            ])
            ->when($request->from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->groupBy('pet_id')
            ->orderByDesc('last_attention')
            ->get();

        $html = view('reports.attended-patients-pdf', [
            'patients' => $patients,
            'filters' => $request->all(),
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'orientation' => 'L',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('pacientes-atendidos.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
}