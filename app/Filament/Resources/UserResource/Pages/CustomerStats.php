<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\DB;

class CustomerStats extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.customer-stats';
    
    protected static ?string $title = 'Statistiques Client';
    
    public User $record;

    public function mount($record): void
    {
        $this->record = $record;
        
        // Vérifier que c'est bien un client
        if ($this->record->role !== \App\Enum\UserRoleEnum::CUSTOMER) {
            abort(403, 'Cette page est réservée aux clients.');
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la liste')
                ->url(UserResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getTitle(): string
    {
        return 'Statistiques de ' . $this->record->name;
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'DB' => DB::class,
        ];
    }
}
