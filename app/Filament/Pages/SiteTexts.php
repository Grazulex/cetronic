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

    /** @var array<string, array<string, ?string>> SiteText::id() => locale => value being edited */
    public array $drafts = [];

    /** @var array<string, array<string, ?string>> SiteText::id() => locale => value when the row was loaded */
    public array $loaded = [];

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 1;

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
        $this->syncDrafts();
    }

    public function updatedSearch(): void
    {
        if ('' !== Str::of($this->search)->trim()->value()) {
            $this->openZones = $this->filteredTexts()->pluck('zone')->unique()->values()->all();
        }

        $this->syncDrafts();
    }

    /**
     * Any filter change can show rows that were hidden: load them.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['zone', 'show', 'technical'], true)) {
            $this->syncDrafts();
        }
    }

    public function saveRow(string $id): void
    {
        $text = app(SiteTextRepository::class)->find($id);

        if (null === $text) {
            Notification::make()->title('Ce texte n’existe plus.')->danger()->send();

            return;
        }

        if ($text->long) {
            Notification::make()->title('Ce texte se modifie avec le bouton « Modifier le texte ».')->warning()->send();

            return;
        }

        $loaded = $this->loaded[$id] ?? $text->values;
        $cleaner = app(RichTextCleaner::class);
        $changes = [];

        foreach (SiteText::LOCALES as $locale) {
            $value = $this->drafts[$id][$locale] ?? null;

            if (null !== $value && str_contains($value, '<')) {
                $value = $cleaner->clean($value);
            }

            if ($this->isChange($loaded[$locale] ?? null, $value)) {
                $changes[$locale] = $value;
            }
        }

        $this->saveChanges($text, $loaded, $changes);
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

                // The editor rewrites the html it opens: compare cleaned values,
                // so a language left untouched is not rewritten.
                $cleaner = app(RichTextCleaner::class);
                $changes = [];
                foreach (SiteText::LOCALES as $locale) {
                    $original = $text->values[$locale];
                    $before = null === $original ? null : $cleaner->clean($cleaner->toEditor($original));
                    $after = $cleaner->clean((string) ($data[$locale] ?? ''));

                    if ($this->isChange($before, $after)) {
                        $changes[$locale] = $after;
                    }
                }

                $this->saveChanges($text, $text->values, $changes);
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
     * Writes only the changed languages, after checking that nobody changed
     * them since the row was loaded (another tab, another admin).
     *
     * @param  array<string, ?string>  $loaded  values when the row was loaded
     * @param  array<string, ?string>  $changes  locale => new value
     */
    private function saveChanges(SiteText $text, array $loaded, array $changes): void
    {
        $id = $text->id();

        if ([] === $changes) {
            Notification::make()->title('Aucune modification à enregistrer.')->info()->send();

            return;
        }

        foreach (array_keys($changes) as $locale) {
            if ($text->values[$locale] !== ($loaded[$locale] ?? null)) {
                $this->remember($text);

                Notification::make()
                    ->title('Ce texte a été modifié entre-temps')
                    ->body('La version actuelle est rechargée : vérifiez-la, puis refaites votre modification.')
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }
        }

        $result = app(SaveSiteTextAction::class)->handle($text, $changes);
        $this->remember($result->text);

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

    /**
     * An empty field for a language that has no text yet is not a change:
     * writing '' would hide the French fallback on the site.
     */
    private function isChange(?string $before, ?string $after): bool
    {
        if (null === $before && (null === $after || '' === $after)) {
            return false;
        }

        return $before !== $after;
    }

    private function remember(SiteText $text): void
    {
        if ($text->long) {
            unset($this->drafts[$text->id()], $this->loaded[$text->id()]);

            return;
        }

        $this->drafts[$text->id()] = $text->values;
        $this->loaded[$text->id()] = $text->values;
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
     * Loads the short texts shown in the open zones that are not loaded yet.
     * Long texts are edited in the rich editor and never kept in the drafts.
     */
    private function syncDrafts(): void
    {
        foreach ($this->filteredTexts() as $text) {
            if ($text->long || ! in_array($text->zone, $this->openZones, true) || isset($this->loaded[$text->id()])) {
                continue;
            }

            $this->remember($text);
        }
    }
}
