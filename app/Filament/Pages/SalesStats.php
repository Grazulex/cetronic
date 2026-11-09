<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\SalesGroupByEnum;
use App\Enum\UserRoleEnum;
use App\Exports\SalesExport;
use App\Filament\Resources\ItemResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\SalesStatsService;
use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @property-read ComponentContainer $form
 */
final class SalesStats extends Page implements HasTable
{
    use InteractsWithTable;

    /** @var array<string, mixed> */
    public array $filters = [];

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?string $navigationLabel = 'Statistiques ventes';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Statistiques des ventes';

    protected static ?string $slug = 'sales-stats';

    protected static string $view = 'filament.pages.sales-stats';

    public function mount(): void
    {
        abort_unless(UserRoleEnum::ADMIN === auth()->user()?->role, 403);

        [$from, $to] = SalesFilters::periodRange('this_year');

        $this->form->fill([
            'period' => 'this_year',
            'from' => $from,
            'to' => $to,
            'group_by' => SalesGroupByEnum::ITEM->value,
        ]);
    }

    public function updatedFilters(mixed $value, string $key): void
    {
        if ('period' === $key && 'custom' !== $value) {
            [$this->filters['from'], $this->filters['to']] = SalesFilters::periodRange((string) $value);
        }

        // The date pickers echo back the dates set by a shortcut: only a real
        // change of dates switches the period to "custom".
        if (in_array($key, ['from', 'to'], true) && ! $this->datesMatchPeriod()) {
            $this->filters['period'] = 'custom';
        }

        if ('group_by' === $key) {
            $this->tableSortColumn = null;
            $this->tableSortDirection = null;
            // Columns were cached when the request booted, with the old grouping.
            $this->cacheTableColumns();
        }

        $this->resetPage();
    }

    public function salesFilters(): SalesFilters
    {
        return SalesFilters::fromArray($this->filters);
    }

    public function groupBy(): SalesGroupByEnum
    {
        return SalesGroupByEnum::tryFrom((string) ($this->filters['group_by'] ?? '')) ?? SalesGroupByEnum::ITEM;
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    protected function getActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en Excel')
                ->icon('heroicon-o-download')
                ->action(fn(): BinaryFileResponse => Excel::download(
                    new SalesExport($this->salesFilters(), $this->groupBy()),
                    sprintf(
                        'ventes-%s-%s.xlsx',
                        $this->salesFilters()->from?->toDateString() ?? 'debut',
                        $this->salesFilters()->to?->toDateString() ?? 'fin',
                    ),
                )),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'filters';
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)->schema([
                Select::make('period')
                    ->label('Période')
                    ->options(SalesFilters::periods() + ['custom' => 'Personnalisée'])
                    ->disablePlaceholderSelection()
                    ->reactive(),
                DatePicker::make('from')->label('Du')->format('Y-m-d')->displayFormat('d/m/Y')->reactive(),
                DatePicker::make('to')->label('Au')->format('Y-m-d')->displayFormat('d/m/Y')->reactive(),
                Select::make('group_by')
                    ->label('Regrouper par')
                    ->options(SalesGroupByEnum::options())
                    ->disablePlaceholderSelection()
                    ->reactive(),
                Select::make('brands')
                    ->label('Marques')
                    ->multiple()
                    ->options(fn() => Brand::orderBy('name')->pluck('name', 'id'))
                    ->reactive(),
                Select::make('categories')
                    ->label('Catégories')
                    ->multiple()
                    ->options(fn() => Category::orderBy('name')->pluck('name', 'id'))
                    ->reactive(),
                TextInput::make('search')
                    ->label('Produit (nom ou référence)')
                    ->lazy(),
                Select::make('user_id')
                    ->label('Client')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search) => User::query()
                        ->where('role', UserRoleEnum::CUSTOMER->value)
                        ->where(fn(Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn($value): ?string => User::query()->whereKey($value)->value('name'))
                    ->reactive(),
                Select::make('agent_id')
                    ->label('Agent')
                    ->options(fn() => User::where('role', UserRoleEnum::AGENT->value)->orderBy('name')->pluck('name', 'id'))
                    ->reactive(),
            ]),
        ];
    }

    protected function getViewData(): array
    {
        $filters = $this->salesFilters();

        return [
            'summary' => app(SalesStatsService::class)->summary($filters),
            'chartFilters' => collect($this->filters)->except(['period', 'group_by'])->all(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return app(SalesStatsService::class)->breakdownQuery($this->salesFilters(), $this->groupBy());
    }

    protected function getTableColumns(): array
    {
        $isItem = SalesGroupByEnum::ITEM === $this->groupBy();

        return array_values(array_filter([
            TextColumn::make('label')->label($this->groupBy()->label())->sortable(),
            $isItem ? TextColumn::make('brand_name')->label('Marque')->sortable() : null,
            $isItem ? TextColumn::make('category_name')->label('Catégorie')->sortable() : null,
            TextColumn::make('quantity')
                ->label('Quantité')
                ->formatStateUsing(fn($state): string => number_format((int) $state, 0, ',', ' '))
                ->sortable()
                ->alignRight(),
            TextColumn::make('revenue')
                ->label('CA HT')
                ->formatStateUsing(fn($state): string => number_format(((int) $state) / 100, 2, ',', ' ') . ' €')
                ->sortable()
                ->alignRight(),
            TextColumn::make('orders_count')->label('Commandes')->sortable()->alignRight(),
            TextColumn::make('customers_count')->label('Clients')->sortable()->alignRight(),
            TextColumn::make('last_sold_at')->label('Dernière vente')->date('d/m/Y')->sortable(),
        ]));
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'revenue';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        if (SalesGroupByEnum::ITEM !== $this->groupBy()) {
            return null;
        }

        return fn(OrderItem $record): string => ItemResource::getUrl('edit', ['record' => $record->getAttribute('slug')]);
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Aucune vente pour ces critères';
    }

    private function datesMatchPeriod(): bool
    {
        $period = (string) ($this->filters['period'] ?? 'custom');

        if ('custom' === $period) {
            return false;
        }

        $current = [
            mb_substr((string) ($this->filters['from'] ?? ''), 0, 10),
            mb_substr((string) ($this->filters['to'] ?? ''), 0, 10),
        ];

        return SalesFilters::periodRange($period) === $current;
    }
}
