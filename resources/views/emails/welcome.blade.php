@component('mail::message')
# Welcome to ArteVue - Your Art Scene.

Dear {{$user->name}},<br>
test email content


Thanks,<br>
{{ config('app.name') }}
@endcomponent