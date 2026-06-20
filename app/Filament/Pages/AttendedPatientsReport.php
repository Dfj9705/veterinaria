<?php

namespace App\Filament\Pages;

use App\Models\ClinicalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AttendedPatientsReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static string $view = 'filament.pages.attended-patients-report';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Pacientes atendidos';

    protected static ?string $title = 'Reporte de pacientes atendidos';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('from')
                    ->label('Desde')
                    ->required()
                    ->live(),

                Forms\Components\DatePicker::make('to')
                    ->label('Hasta')
                    ->required()
                    ->live(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getPatients()
    {
        $filters = $this->form->getState();

        return ClinicalRecord::query()
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
            ->when($filters['from'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($filters['to'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->groupBy('pet_id')
            ->orderByDesc('last_attention')
            ->get();
    }

    public function generatePdf()
    {
        return redirect()->route('reports.attended-patients.pdf', [
            ...$this->form->getState(),
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_reports') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_reports') ?? false;
    }
}