<?php

declare(strict_types=1);

namespace App\Http\Controllers\Help;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHelpRequest;
use App\Jobs\SendMailHelp;
use Illuminate\Http\RedirectResponse;

final class SendHelpController extends Controller
{
    public function __invoke(CreateHelpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($request->get(key: 'title'))) {
            $data['ip'] = UserSystemInfoHelper::get_ip();
            $data['browser'] = UserSystemInfoHelper::get_browsers();
            $data['device'] = UserSystemInfoHelper::get_device();
            $data['os'] = UserSystemInfoHelper::get_os();
            SendMailHelp::dispatch($data);
        }

        return redirect()->route(route: 'help')->with(key: 'success', value:  __(key: 'help.success'));
    }
}
