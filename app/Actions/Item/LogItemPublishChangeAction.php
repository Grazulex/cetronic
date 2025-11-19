<?php

declare(strict_types=1);

namespace App\Actions\Item;

use App\Models\Item;
use App\Models\ItemPublishLog;

final class LogItemPublishChangeAction
{
    /**
     * Log a change in is_published status for an item.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        Item $item,
        bool $oldValue,
        bool $newValue,
        string $action,
        ?int $userId = null,
        ?string $reason = null,
        ?array $metadata = null
    ): ?ItemPublishLog {
        // Only log if value actually changed
        if ($oldValue === $newValue) {
            return null;
        }

        return ItemPublishLog::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log multiple items changes (for bulk actions).
     *
     * @param  iterable<Item>  $items
     * @param  array<string, mixed>|null  $metadata
     * @return array<ItemPublishLog>
     */
    public function handleBulk(
        iterable $items,
        bool $newValue,
        string $action,
        ?int $userId = null,
        ?string $reason = null,
        ?array $metadata = null
    ): array {
        $logs = [];

        foreach ($items as $item) {
            $oldValue = (bool) $item->getOriginal('is_published', $item->is_published);

            $log = $this->handle(
                item: $item,
                oldValue: $oldValue,
                newValue: $newValue,
                action: $action,
                userId: $userId,
                reason: $reason,
                metadata: $metadata
            );

            if ($log !== null) {
                $logs[] = $log;
            }
        }

        return $logs;
    }
}
