@extends('layouts.app')

@section('content')
    <header>
        <h1>Edit Task</h1>
        <a class="button secondary" href="{{ route('tasks.index') }}">Back</a>
    </header>

    <section class="panel">
        <form class="stack" method="POST" action="{{ route('tasks.update', $task) }}">
            @csrf
            @method('PUT')

            <label>
                Task title
                <input type="text" name="title" value="{{ old('title', $task->title) }}" autofocus>
                @error('title')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label class="checkbox">
                <input type="checkbox" name="completed" value="1" @checked(old('completed', $task->completed))>
                Completed
            </label>

            <button type="submit">Update Task</button>
        </form>
    </section>
@endsection
