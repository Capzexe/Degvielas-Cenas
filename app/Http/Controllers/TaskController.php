<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::latest()->get();

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return redirect()->route('tasks.edit', $task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $task->update([
            'title' => $validated['title'],
            'completed' => $request->boolean('completed'),
        ]);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }
}
