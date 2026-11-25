<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Message;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SectionMessageComponent extends Component
{
    /**
     * From mid-November to Epiphany (6 January) the homepage message gets a
     * festive touch.
     */
    public static function isHolidaySeason(CarbonInterface $date): bool
    {
        $monthDay = (int) $date->format('md');

        return $monthDay >= 1115 || $monthDay <= 106;
    }

    public function render(): View
    {
        $messages = Message::active()->local(LaravelLocalization::getCurrentLocale())->get();

        return view('components.section-message-component', [
            'messages' => $messages,
            'festive' => self::isHolidaySeason(now()),
        ]);
    }
}
