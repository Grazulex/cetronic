<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enum\UserRoleEnum;
use App\SiteTexts\RichTextCleaner;
use App\SiteTexts\SaveSiteTextAction;
use App\SiteTexts\SiteText;
use App\SiteTexts\SiteTextConfig;
use App\SiteTexts\SiteTextFilter;
use App\SiteTexts\SiteTextRepository;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * "Textes du site": the client edits the site texts (ltu_* tables) in the
 * three languages side by side; every save is published right away.
 */
final class SiteTexts extends Page
{
    public const LANGUAGE_NAMES = ['fr' => 'français', 'nl' => 'néerlandais', 'en' => 'anglais'];

    public string $search = '';

    public ?string $zone = null;

    public string $show = SiteTextFilter::SHOW_ALL;

    public bool $technical = false;

    /** @var list<string> */
    public array $openZones = [];

    /** @var array<string, array<string, ?string>> SiteText::id() => locale => value */
    public array $drafts = [];

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?string $navigationLabel = 'Textes du site';

    protected static ?string $title = 'Textes du site';

    protected static ?string $slug = 'textes-du-site';

    protected static string $view = 'filament.pages.site-texts';

    public static function canAccess(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public function toggleZone(string $zone): void
    {
        if (in_array($zone, $this->openZones, true)) {
            $this->openZones = array_values(array_diff($this->openZones, [$zone]));

            return;
        }

        $this->openZones[] = $zone;
        $this->fillDrafts($this->filteredTexts()->where('zone', $zone));
    }

    public function updatedSearch(): void
    {
        if ('' === Str::of($this->search)->trim()->value()) {
            return;
        }

        $texts = $this->filteredTexts();
        $this->openZones = $texts->pluck('zone')->unique()->values()->all();
        $this->fillDrafts($texts);
    }

    public function saveRow(string $id): void
    {
        $text = app(SiteTextRepository::class)->find($id);

        if (null === $text) {
            Notification::make()->title('Ce texte n’existe plus.')->danger()->send();

            return;
        }

        $this->saveAndNotify($text, $this->drafts[$id] ?? []);
    }

    /**
     * "Modifier le texte": long texts are edited in a rich editor, one tab per
     * language, and saved (cleaned) all three at once.
     */
    public function editLongTextAction(): Action
    {
        return Action::make('editLongText')
            ->label('Modifier le texte')
            ->icon('heroicon-m-pencil-square')
            ->size('sm')
            ->modalHeading(fn(array $arguments): string => $this->longTextHeading((string) ($arguments['id'] ?? '')))
            ->modalSubmitActionLabel('Enregistrer et publier')
            ->modalWidth(MaxWidth::FourExtraLarge)
            ->fillForm(fn(array $arguments): array => $this->longTextFormData((string) ($arguments['id'] ?? '')))
            ->form([
                Tabs::make('languages')->tabs(array_map(
                    fn(string $locale, string $name): Tab => Tab::make(mb_strtoupper($locale))->schema([
                        RichEditor::make($locale)
                            ->label("Texte — {$name}")
                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList']),
                    ]),
                    array_keys(self::LANGUAGE_NAMES),
                    self::LANGUAGE_NAMES,
                )),
            ])
            ->action(function (array $data, array $arguments): void {
                $text = app(SiteTextRepository::class)->find((string) ($arguments['id'] ?? ''));

                if (null === $text) {
                    Notification::make()->title('Ce texte n’existe plus.')->danger()->send();

                    return;
                }

                $cleaner = app(RichTextCleaner::class);
                $values = [];
                foreach (SiteText::LOCALES as $locale) {
                    $values[$locale] = $cleaner->clean((string) ($data[$locale] ?? ''));
                }

                $this->saveAndNotify($text, $values);
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $filter = app(SiteTextFilter::class);
        $zones = app(SiteTextConfig::class)->zones();
        $texts = $this->filteredTexts();

        $groups = [];
        foreach ($texts->groupBy('zone') as $zoneId => $zoneTexts) {
            $open = in_array($zoneId, $this->openZones, true);
            $groups[$zoneId] = [
                'label' => $zones[$zoneId]['label'] ?? $zoneId,
                'count' => $zoneTexts->count(),
                'open' => $open,
                'texts' => $open ? $zoneTexts->map(fn(SiteText $text): array => [
                    'text' => $text,
                    'matches' => $filter->matches($text, $this->search),
                ])->all() : [],
            ];
        }

        return [
            'groups' => $groups,
            'zoneOptions' => collect($zones)
                ->reject(fn(array $zone): bool => $zone['technical'] && ! $this->technical)
                ->map(fn(array $zone): string => $zone['label'])
                ->all(),
            'languages' => self::LANGUAGE_NAMES,
        ];
    }

    /**
     * @param  array<string, ?string>  $values
     */
    private function saveAndNotify(SiteText $text, array $values): void
    {
        $result = app(SaveSiteTextAction::class)->handle($text, $values);
        $this->drafts[$text->id()] = $result->text->values;

        if ($result->published) {
            Notification::make()->title('Texte enregistré et publié')->success()->send();

            return;
        }

        Notification::make()
            ->title('Texte enregistré, mais pas encore publié')
            ->body("{$result->error}. Il le sera au prochain enregistrement ou au prochain déploiement.")
            ->warning()
            ->persistent()
            ->send();
    }

    private function longTextHeading(string $id): string
    {
        $text = app(SiteTextRepository::class)->find($id);

        if (null === $text) {
            return 'Texte';
        }

        $zone = app(SiteTextConfig::class)->zones()[$text->zone]['label'] ?? $text->zone;

        return "{$zone} › {$text->label}";
    }

    /**
     * @return array<string, string>
     */
    private function longTextFormData(string $id): array
    {
        $text = app(SiteTextRepository::class)->find($id);
        $cleaner = app(RichTextCleaner::class);

        $data = [];
        foreach (SiteText::LOCALES as $locale) {
            $data[$locale] = $cleaner->toEditor((string) ($text?->values[$locale] ?? ''));
        }

        return $data;
    }

    /**
     * @return Collection<int, SiteText>
     */
    private function filteredTexts(): Collection
    {
        return app(SiteTextFilter::class)->apply(
            app(SiteTextRepository::class)->all(),
            $this->search,
            $this->zone,
            $this->show,
            $this->technical,
        );
    }

    /**
     * @param  Collection<int, SiteText>  $texts
     */
    private function fillDrafts(Collection $texts): void
    {
        foreach ($texts as $text) {
            $this->drafts[$text->id()] ??= $text->values;
        }
    }
}
