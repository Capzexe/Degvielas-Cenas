<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Support\BlogPosts;
use App\Support\DiscountOffers;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::view('/', 'gas')->name('gas.index');

Route::redirect('/gas', '/', 301);
Route::redirect('/degvielas-cenas', '/', 301);
Route::redirect('/akcijas', '/degvielas-atlaides', 301);
Route::redirect('/about', '/par-projektu', 301);

Route::get('/degvielas-atlaides', fn () => view('discounts', ['offers' => DiscountOffers::all()]))->name('discounts.index');
Route::get('/blog', fn () => view('blog.index', ['posts' => BlogPosts::all()]))->name('blog.index');
Route::get('/blog/{slug}', function (string $slug) {
    $post = BlogPosts::find($slug);

    abort_if($post === null, 404);

    if ($post['slug'] !== $slug) {
        return redirect()->route('blog.show', $post['slug'], 301);
    }

    return view('blog.show', [
        'post' => $post,
        'posts' => BlogPosts::all(),
    ]);
})->name('blog.show');
Route::view('/par-projektu', 'about')->name('about');

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        '',
        'Sitemap: '.route('seo.sitemap'),
        'LLMs: '.route('seo.llms'),
        '',
    ]);

    return Response::make($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('seo.robots');

Route::get('/sitemap.xml', function () {
    $urls = collect([
        ['loc' => route('gas.index'), 'priority' => '1.0', 'changefreq' => 'hourly', 'lastmod' => now()->toDateString()],
        ['loc' => route('discounts.index'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
        ['loc' => route('blog.index'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
        ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
    ])->merge(BlogPosts::all()->map(fn (array $post): array => [
        'loc' => route('blog.show', $post['slug']),
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => $post['updated_at'],
    ]));

    return Response::view('seo.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('seo.sitemap');

Route::get('/llms.txt', function () {
    return Response::view('seo.llms', ['posts' => BlogPosts::all()], 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('seo.llms');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo-login');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('vehicles', VehicleController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('appointments', AppointmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
});
