<x-mail::message>
    ## Dear {{ $name }}

    Here's your daily task reminder:

    @foreach ($tasks as $idx => $task)
        {{ $idx + 1 }}. {{ $task }}
    @endforeach

    Please remember to complete these tasks at your earliest convenience.If you have already completed any of them,
    please mark them as done in your task list.

    Best regards,
    {{ config('app.name') }}

</x-mail::message>
