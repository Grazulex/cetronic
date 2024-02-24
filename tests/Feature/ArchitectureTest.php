<?php

declare(strict_types=1);

test('globals')
    ->expect(['dd','dump','var_dump'])
    ->not->toBeUsed();
