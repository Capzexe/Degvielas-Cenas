# Laravel Task Manager Learning Map

This project is intentionally small. Its goal is to help you understand the Laravel flow:

```txt
route -> controller -> model -> database -> view
```

## Files to read first

- `routes/web.php` decides which controller method runs for each URL.
- `app/Http/Controllers/TaskController.php` handles listing, creating, editing, updating, and deleting tasks.
- `app/Models/Task.php` represents one row in the `tasks` table.
- `database/migrations/2026_09_13_111103_create_tasks_table.php` defines the `tasks` database table.
- `resources/views/tasks/*.blade.php` contains the HTML templates.

## First exercises

1. Change the task list heading.
2. Add a `description` column to tasks.
3. Show the created date under each task.
4. Add a button that marks a task done from the list page.
5. Add search by task title.

## Commands

Use the XAMPP PHP path on this machine:

```powershell
C:\xampp\php\php.exe artisan serve
C:\xampp\php\php.exe artisan migrate
C:\xampp\php\php.exe artisan route:list
C:\xampp\php\php.exe artisan test
```
