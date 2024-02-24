<?php

declare(strict_types=1);

return [
    'order_new' => [
        'subject' => 'Nieuwe bestelling :',
        'body' => 'Beste :name,<br><br>
         Bedankt voor uw bestelling n° :reference. U kunt uw bestelgeschiedenis op elk moment bekijken door in te loggen
         op uw account op onze website – www.cetronicbenelux.com.<br><br>
         We doen ons best om het zo snel mogelijk klaar te maken en het naar u op te sturen. Houd er rekening mee dat
         we mogelijk niet al uw bestelde producten op voorraad hebben en dat dit de inhoud en de eindfactuur van deze
         bestelling kan wijzigen.<br><br>
         Aarzel niet om contact met ons op te nemen als u vragen heeft over uw bestelling.<br><br>
         Mvg,<br>
         Het Cetronic Team',
    ],
    'cart_remove' => [
        'subject' => 'Product verwijderd uit winkelwagen',
        'body' => 'Beste :name,<br><br>
         Houd er rekening mee dat de inhoud van uw winkelmandje is gewijzigd omdat de volgende producten niet meer op voorraad zijn:<br><br>
         :product<br><br>
         Mvg,<br>
         Het Cetronic Team',
    ],
    'cart_add' => [
        'subject' => 'Product toegevoegd aan winkelwagen',
        'body' => 'Beste :name,<br><br>
         Houd er rekening mee dat sommige producten die u in uw winkelwagen had, terug op voorraad zijn. Hier zijn de details van de producten die weer beschikbaar zijn:<br><br>
         :product<br><br>
         Mvg,<br>
         Het Cetronic Team',
    ],
    'cart_update' => [
        'subject' => 'Product wijziging in winkelwagen',
        'body' => 'Beste :name,<br><br>
         Houd er rekening mee dat sommige artikelen die u in uw winkelmandje heeft, gewijzigde prijzen hebben. Hier is de lijst met producten waarvan de prijzen zijn gewijzigd:<br><br>
         :product<br><br>
         Mvg,<br>
         Het Cetronic Team',
    ],
];
