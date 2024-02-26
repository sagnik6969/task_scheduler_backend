@component('mail::message')
    ## Dear {{ $notifiable->name }}

    Here's your daily task reminder:

    @foreach($tasks as $idx => $task)
        {{ $idx + 1 }}. {{ $task->title }} (Deadline:
        {{ $task->deadline->format('Y-m-d H:i:s') }})
    @endforeach

    Please remember to complete these tasks at your earliest convenience. If you have already completed any of them,
    please mark them as done in your task list.

    Best regards,<br>
    {{ config('app.name') }}
@endcomponent
