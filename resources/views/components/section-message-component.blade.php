@foreach ($messages as $message)
    <section @class(['home-message', 'home-message--festive' => $festive]) data-aos="fade-up" data-aos-duration="800">
        <div class="home-message_card">
            @if ($festive)
                <span class="home-message_snow" aria-hidden="true"></span>
                <span class="home-message_ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18">
                        <path d="M12 1.5l2 7.1 7.2-1.6-5.2 5 5.2 5-7.2-1.6-2 7.1-2-7.1-7.2 1.6 5.2-5-5.2-5 7.2 1.6z" fill="currentColor"/>
                    </svg>
                </span>
            @endif
            <h2 class="home-message_title">{{ $message->title }}</h2>
            <div class="home-message_body">
                {!! str($message->content)->sanitizeHtml() !!}
            </div>
        </div>
    </section>
@endforeach
