<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Jobs\SendMailContact;
use Illuminate\Http\RedirectResponse;

final class SendContactController extends Controller
{
    public function __invoke(CreateContactRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($request->get(key: 'title'))) {
            SendMailContact::dispatch($data);
        }

        return redirect()->route(route: 'contact.thanks')->with(key: 'success', value:  __(key: 'contact.success'));
    }
}
