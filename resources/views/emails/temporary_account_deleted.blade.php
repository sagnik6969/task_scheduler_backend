@component('mail::message')
    # Temporary Account Deleted

    Dear {{ $notifiable->name }},

    We regret to inform you that your account has been temporarily deleted by the admin due to your actions.

    You have been given another chance to register freshly. Please click the button below to register now.

    <blade
        component|(%26%2339%3Bmail%3A%3Abutton%26%2339%3B%2C%20%5B%26%2339%3Burl%26%2339%3B%20%3D%3E%20%24url%2C%20%26%2339%3Bcolor%26%2339%3B%20%3D%3E%20%26%2339%3Bsuccess%26%2339%3B%5D)%0D>
        Register Now
    @endcomponent

    Thank you for using {{ config('app.name') }} app!

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
