@component('mail::message')
Hi **{{ $userName }}**,

Congratulations!<br>
Your registration process is complete.

@component('mail::button', ['url' => config('app.url')])
    Click here to visit
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
