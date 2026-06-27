<?php

namespace App\Filament\Pages;

use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class ClinicalRecordReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.clinical-record-report';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Expediente clínico';

    protected static ?string $title = 'Expediente clínico por mascota';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'pet_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pet_id')
                    ->label('Mascota')
                    ->options(function () {
                        return Pet::query()
                            ->with('customer')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn($pet) => [
                                $pet->id => "{$pet->name} - {$pet->customer?->name}",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function getPet()
    {
        $petId = $this->data['pet_id'] ?? null;

        if (!$petId) {
            return null;
        }

        return Pet::query()
            ->with([
                'customer',
                'species',
                'breed',
                'clinicalRecords' => fn($query) => $query->latest(),
                'clinicalRecords.assignedUser',
                'clinicalRecords.prescriptions',
                'clinicalRecords.prescriptions.items'
            ])
            ->find($petId);
    }

    public function generatePdf()
    {
        $petId = $this->data['pet_id'] ?? null;

        if (!$petId) {
            return;
        }

        return redirect()->route('reports.clinical-record.pdf', [
            'pet' => $petId,
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