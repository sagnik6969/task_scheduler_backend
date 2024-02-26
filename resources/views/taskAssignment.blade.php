<!-- raw frontend have to add vue -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assignment</title>
</head>

<body>
    <h1>Task Assignment Details</h1>
    <p>Title: {{ $task->title }}</p>
    <p>Description: {{ $task->description }}</p>
    <p>Deadline: {{ $task->deadline }}</p>

    <!-- Option to accept or decline the task -->
    <form action="/tasks/accept/{{ $task->id }}" method="POST">
        @csrf
        <button type="submit">Accept</button>
    </form>

    <form action="/tasks/decline/{{ $task->id }}" method="POST">
        @csrf
        <button type="submit">Decline</button>
    </form>
</body>

</html>
