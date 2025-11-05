<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class ItemNotificationsCommand extends Command
{
    protected $signature = 'items:notifications {action : enable ou disable}';

    protected $description = 'Active ou désactive les notifications d\'emails lors de mise à jour d\'items';

    public function handle(): int
    {
        $action = $this->argument('action');

        if (!in_array($action, ['enable', 'disable'])) {
            $this->error('Action invalide. Utilisez "enable" ou "disable"');

            return self::FAILURE;
        }

        $skip = $action === 'disable';
        config(['items.skip_notifications' => $skip]);

        if ($skip) {
            $this->warn('⚠️  Les notifications d\'emails sont maintenant DÉSACTIVÉES');
            $this->info('Les mises à jour d\'items ne déclencheront PAS d\'emails aux clients');
            $this->line('');
            $this->comment('💡 N\'oubliez pas de les réactiver après votre import :');
            $this->comment('   php artisan items:notifications enable');
        } else {
            $this->info('✅ Les notifications d\'emails sont maintenant ACTIVÉES');
            $this->info('Les mises à jour d\'items déclencheront des emails aux clients ayant ces items dans leur panier');
        }

        return self::SUCCESS;
    }
}
