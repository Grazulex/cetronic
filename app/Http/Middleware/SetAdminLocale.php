<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin staff works in French. Customer e-mails are not affected:
 * User implements HasLocalePreference, so mails use the customer's language.
 */
final class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('fr');

        return $next($request);
    }
}
