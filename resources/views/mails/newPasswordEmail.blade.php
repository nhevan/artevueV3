@component('mail::message')
# New Password generated

Your new password is : {{ $new_password }}

Please change your password after you log into Artevue from settings menu. 
keep your password safe.

Thanks for using ArteVue


Thanks,<br>
{{ config('app.name') }}
@endcomponent