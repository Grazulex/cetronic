<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Skip Notifications
    |--------------------------------------------------------------------------
    |
    | Désactive l'envoi des notifications emails lors de mises à jour massives
    | d'items. Utile lors d'imports CSV pour éviter l'envoi de centaines
    | d'emails aux clients.
    |
    | Utilisation dans votre code d'import:
    |   config(['items.skip_notifications' => true]);
    |   // ... votre import ...
    |   config(['items.skip_notifications' => false]);
    |
    | Ou via CLI:
    |   php artisan config:set items.skip_notifications true
    |   php artisan import:items fichier.csv
    |   php artisan config:set items.skip_notifications false
    |
    */

    'skip_notifications' => env('ITEMS_SKIP_NOTIFICATIONS', false),
];
