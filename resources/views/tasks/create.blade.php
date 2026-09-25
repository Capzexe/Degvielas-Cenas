@extends('layouts.app')

@section('content')
    <header>
        <h1>New Task</h1>
        <a class="button secondary" href="{{ route('tasks.index') }}">Back</a>
    </header>

    <section class="panel">
        <form class="stack" method="POST" action="{{ route('tasks.store') }}">
            @csrf

            <label>
                Task title
                <input type="text" name="title" value="{{ old('title') }}" autofocus>
                @error('title')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit">Save Task</button>
        </form>
    </section>
@endsection
