<?php

declare(strict_types=1);

namespace App\Exports\BrandDiscounts;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

final class BrandDiscountsLegendSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Légende';
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['Comment remplir la feuille « Remises »'],
            [''],
            ['Cellule vide', 'Rien ne change (aucune remise n\'est créée si le client n\'en a pas).'],
            ['0', 'La valeur est mise à zéro.'],
            ['Réduction %', 'Pourcentage de 0 à 100 (ex. 12,5).'],
            ['Coefficient', '0 = pas de coefficient. Sinon multiplicateur du prix (ex. 1,2).'],
            ['Ajout €', 'Montant ajouté au prix, en euros.'],
            ['Type de prix', 'default, b2b, special_1, special_2, special_3 ou fix. Avec « fix », réduction, coefficient et ajout sont ignorés.'],
            ['Masquer promo / Exclu', 'oui ou non.'],
            ['ID client / ID marque', 'Ne pas modifier : servent à retrouver le client et la marque à l\'import.'],
            ['Nom / Email', 'Pour information : non réimportés.'],
            ['Référence externe / Franco / Frais de port', 'Mis à jour à l\'import.'],
        ];
    }
}
