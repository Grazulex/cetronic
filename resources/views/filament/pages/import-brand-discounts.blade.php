<x-filament::page>
    <form wire:submit.prevent="analyse" class="space-y-6">
        {{ $this->form }}

        <p class="text-sm text-gray-500">
            L'analyse ne modifie rien : elle montre ce qui sera changé. Une cellule vide ne change rien, 0 met la valeur à zéro.
        </p>

        <x-filament::button type="submit" wire:target="analyse,file" wire:loading.attr="disabled">
            Analyser le fichier
        </x-filament::button>
    </form>

    @if ($report)
        <x-filament::card>
            <div class="space-y-4">
                <ul class="text-sm list-disc list-inside">
                    @foreach ($report['summary'] as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>

                @if (count($report['errors']) > 0)
                    <div>
                        <p class="font-medium text-danger-600">
                            {{ count($report['errors']) }} erreur(s) : corrigez le fichier puis relancez l'analyse. Rien ne sera importé tant qu'il reste une erreur.
                        </p>
                        <table class="w-full mt-2 text-sm text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-1 pr-4">Ligne</th>
                                    <th class="py-1 pr-4">Colonne</th>
                                    <th class="py-1">Problème</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['errors'] as $error)
                                    <tr class="border-b">
                                        <td class="py-1 pr-4">{{ $error['row'] }}</td>
                                        <td class="py-1 pr-4">{{ $error['column'] }}</td>
                                        <td class="py-1">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-filament::button color="success" wire:click="apply" wire:loading.attr="disabled" wire:target="apply">
                        Appliquer les modifications
                    </x-filament::button>
                @endif
            </div>
        </x-filament::card>
    @endif
</x-filament::page>
