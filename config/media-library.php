<?php

declare(strict_types=1);

/*
 * Seules les clés surchargées sont listées ici : le reste de la configuration
 * vient du package (fusion de premier niveau via mergeConfigFrom).
 */
return [
    // Les conversions d'images passent par une queue dédiée pour ne pas retarder
    // les jobs de la queue "default" (mails, commandes…)
    'queue_name' => env('MEDIA_QUEUE', 'media'),
];
