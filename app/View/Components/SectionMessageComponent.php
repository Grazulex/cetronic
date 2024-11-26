<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Message;
use Closure;
use Illuminate\View\Component;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SectionMessageComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $messages = Message::active()->local(LaravelLocalization::getCurrentLocale())->get();

        return view('components.section-message-component', compact('messages'));
    }
}
