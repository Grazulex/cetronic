<x-filament-panels::page>
    {{-- Toolbar: one search over the three languages and the names, then filters --}}
    <x-filament::section>
        <div style="display: grid; gap: 1rem;">
            <label>
                <span class="sr-only">Rechercher</span>
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Rechercher dans tous les textes (FR, NL, EN) ou par nom…"
                    />
                </x-filament::input.wrapper>
            </label>

            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center;">
                <label style="display: flex; gap: .5rem; align-items: center;">
                    <span class="text-sm font-medium">Zone</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="zone">
                            <option value="">Toutes</option>
                            @foreach ($zoneOptions as $zoneId => $zoneLabel)
                                <option value="{{ $zoneId }}">{{ $zoneLabel }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>

                <fieldset style="display: flex; gap: 1rem; align-items: center;">
                    <legend class="sr-only">Afficher</legend>
                    <span class="text-sm font-medium">Afficher :</span>
                    @foreach (['all' => 'Tous', 'untranslated' => 'À traduire', 'recent' => 'Modifiés récemment'] as $value => $label)
                        <label style="display: flex; gap: .35rem; align-items: center;" class="text-sm">
                            <input type="radio" wire:model.live="show" value="{{ $value }}" name="show">
                            {{ $label }}
                        </label>
                    @endforeach
                </fieldset>

                <label style="display: flex; gap: .35rem; align-items: center;" class="text-sm">
                    <x-filament::input.checkbox wire:model.live="technical" />
                    Afficher les textes techniques
                </label>
            </div>
        </div>
    </x-filament::section>

    @forelse ($groups as $zoneId => $group)
        <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5" wire:key="zone-{{ $zoneId }}">
            <button
                type="button"
                wire:click="toggleZone('{{ $zoneId }}')"
                aria-expanded="{{ $group['open'] ? 'true' : 'false' }}"
                style="display: flex; width: 100%; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; text-align: left;"
                class="text-base font-semibold"
            >
                <span>{{ $group['label'] }} ({{ $group['count'] }})</span>
                <x-filament::icon :icon="$group['open'] ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="h-5 w-5 text-gray-400" />
            </button>

            @if ($group['open'])
                <div style="border-top: 1px solid rgb(0 0 0 / .06); padding: .5rem 1.5rem 1rem;">
                    @foreach ($group['texts'] as $row)
                        @php($text = $row['text'])
                        @php($id = $text->id())
                        <div wire:key="text-{{ $id }}" style="padding: .75rem 0; border-bottom: 1px solid rgb(0 0 0 / .04);">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: .4rem;">
                                <span
                                    class="text-sm font-medium"
                                    title="{{ $text->fullKey() }}"
                                    @if ($row['matches']['label']) style="background: rgb(254 240 138);" @endif
                                >{{ $text->label }}</span>
                                <span class="text-xs text-gray-400" title="Clé technique">{{ $text->fullKey() }}</span>
                            </div>

                            @if ($text->long)
                                {{-- Long texts hold html: never in a plain field, always in the rich editor --}}
                                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) auto; gap: .75rem; align-items: start;">
                                    @foreach ($languages as $locale => $languageName)
                                        @php($plain = trim(strip_tags((string) ($text->values[$locale] ?? ''))))
                                        <div
                                            class="text-sm text-gray-600"
                                            style="padding: .5rem .75rem; border-radius: .5rem; background: rgb(249 250 251);{{ '' === $plain ? ' box-shadow: 0 0 0 2px rgb(245 158 11);' : ($row['matches'][$locale] ? ' box-shadow: 0 0 0 2px rgb(250 204 21);' : '') }}"
                                        >
                                            <span class="text-xs font-semibold text-gray-500">{{ mb_strtoupper($locale) }}</span>
                                            <span class="sr-only">{{ $text->label }} — {{ $languageName }}</span>
                                            <div>{{ '' === $plain ? 'À traduire' : \Illuminate\Support\Str::limit($plain, 120) }}</div>
                                        </div>
                                    @endforeach

                                    <div style="padding-top: .25rem;">
                                        {{ ($this->editLongTextAction)(['id' => $id]) }}
                                    </div>
                                </div>
                            @else
                            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) auto; gap: .75rem; align-items: start;">
                                @foreach ($languages as $locale => $languageName)
                                    @php($empty = '' === trim((string) ($drafts[$id][$locale] ?? '')))
                                    <label>
                                        <span class="sr-only">{{ $text->label }} — {{ $languageName }}</span>
                                        <span aria-hidden="true" class="text-xs font-semibold text-gray-500">{{ mb_strtoupper($locale) }}</span>
                                        <x-filament::input.wrapper
                                            :style="$empty ? 'box-shadow: 0 0 0 2px rgb(245 158 11);' : ($row['matches'][$locale] ? 'box-shadow: 0 0 0 2px rgb(250 204 21);' : null)"
                                        >
                                            @if ($text->multiline())
                                                {{-- Too long for one line: Enter adds a line, the button saves --}}
                                                <textarea
                                                    class="fi-input"
                                                    style="display: block; width: 100%; resize: vertical; border: none; background: transparent; padding: .375rem .75rem; font-size: .875rem; line-height: 1.5;"
                                                    rows="5"
                                                    wire:model="drafts.{{ $id }}.{{ $locale }}"
                                                ></textarea>
                                            @else
                                                <x-filament::input
                                                    type="text"
                                                    wire:model="drafts.{{ $id }}.{{ $locale }}"
                                                    wire:keydown.enter="saveRow('{{ $id }}')"
                                                />
                                            @endif
                                        </x-filament::input.wrapper>
                                        @if ($empty)
                                            <span class="text-xs" style="color: rgb(180 83 9);">À traduire</span>
                                        @endif
                                    </label>
                                @endforeach

                                <div style="padding-top: 1.1rem;">
                                    <x-filament::button size="sm" wire:click="saveRow('{{ $id }}')" wire:target="saveRow('{{ $id }}')">
                                        Enregistrer
                                    </x-filament::button>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <x-filament::section>
            <p class="text-sm text-gray-500">Aucun texte ne correspond à ces critères.</p>
        </x-filament::section>
    @endforelse
</x-filament-panels::page>
