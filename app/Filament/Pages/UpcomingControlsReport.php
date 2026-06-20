<?php

namespace App\Filament\Pages;

use App\Models\ClinicalRecord;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class UpcomingControlsReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.pages.upcoming-controls-report';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Próximos controles';

    protected static ?string $title = 'Próximos controles';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => today()->toDateString(),
            'to' => today()->addDays(30)->toDateString(),
            'veterinarian_id' => null,
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

                Forms\Components\Select::make('veterinarian_id')
                    ->label('Veterinario')
                    ->options(
                        User::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->live(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function getRecords()
    {
        $filters = $this->form->getState();

        return ClinicalRecord::query()
            ->with([
                'pet.customer',
                'veterinarian',
            ])
            ->whereNotNull('next_control_date')
            ->when(
                $filters['from'] ?? null,
                fn($q, $date) =>
                $q->whereDate('next_control_date', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn($q, $date) =>
                $q->whereDate('next_control_date', '<=', $date)
            )
            ->when(
                $filters['veterinarian_id'] ?? null,
                fn($q, $id) =>
                $q->where('veterinarian_id', $id)
            )
            ->orderBy('next_control_date')
            ->get()
            ->unique('pet_id')
            ->values();
    }
    public function getStats(): array
    {
        return [
            'expired' => ClinicalRecord::query()
                ->whereNotNull('next_control_date')
                ->whereDate('next_control_date', '<', today())
                ->distinct('pet_id')
                ->count('pet_id'),

            'today' => ClinicalRecord::query()
                ->whereDate('next_control_date', today())
                ->distinct('pet_id')
                ->count('pet_id'),

            'week' => ClinicalRecord::query()
                ->whereBetween('next_control_date', [
                    today(),
                    today()->addDays(7),
                ])
                ->distinct('pet_id')
                ->count('pet_id'),

            'month' => ClinicalRecord::query()
                ->whereBetween('next_control_date', [
                    today(),
                    today()->addDays(30),
                ])
                ->distinct('pet_id')
                ->count('pet_id'),
        ];
    }

    public function generatePdf()
    {
        return redirect()->route('reports.upcoming-controls.pdf', [
            ...$this->form->getState(),
        ]);
    }

    public function daysRemaining($date): int
    {
        return Carbon::today()
            ->diffInDays(Carbon::parse($date), false);
    }
}