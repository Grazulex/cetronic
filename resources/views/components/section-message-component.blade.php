<div class="container">
    <div class="row d-flex">
        @foreach($messages as $message)
            <div class="col-12">
                <h1>{{ $message->title }}</h1>
                <h3>{!! str($message->content)->sanitizeHtml()  !!}</h3>
            </div>
        @endforeach
    </div>
</div>
