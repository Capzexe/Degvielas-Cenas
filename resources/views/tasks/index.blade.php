@extends('layouts.app')

@section('content')
    <header>
        <h1>Tasks</h1>
        <a class="button" href="{{ route('tasks.create') }}">New Task</a>
    </header>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <section class="panel">
        @forelse ($tasks as $task)
            <article class="task">
                <div>
                    <strong class="task-title @if ($task->completed) done @endif">
                        {{ $task->title }}
                    </strong>
                    <div>
                        {{ $task->completed ? 'Done' : 'Not done yet' }}
                    </div>
                </div>

                <div class="actions">
                    <a class="button secondary" href="{{ route('tasks.edit', $task) }}">Edit</a>

                    <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                        @csrf
                        @method('DELETE')
                        <button class="danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <article class="task">
                <div>No tasks yet. Create your first one.</div>
            </article>
        @endforelse
    </section>
@endsection
