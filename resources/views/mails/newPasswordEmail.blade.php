@component('mail::message')
# Password Reset Request

Dear User,
<br>
<br>
We have received a request to reset the password for your ArteVue account.
<br><br>
Your username is : <strong>{{ $username }}</strong>
<br>
Your new password is : <strong>{{ $new_password }}</strong>
<br><br>
Please log into the app using this username and the new password, which we have provided.
<br><br>
Once you are in the app, please reset the password in Settings, which can be found under your Profile. Passwords must be between 8- 16 characters long and contain at least one number.
<br><br>
If you did not request a new password please contact us immediately on <a href="info@artevue.co.uk">info@artevue.co.uk</a>
<br><br>

Best Regards,
<br>
<br>
ArteVue Team
@endcomponent