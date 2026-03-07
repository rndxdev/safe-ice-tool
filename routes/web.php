<?php

use App\Http\Controllers\IceReportController;
use App\Http\Controllers\LakeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripShareController;
use App\Http\Controllers\TripPostController;
use App\Http\Controllers\TripPostShareController;
use App\Http\Controllers\TripPostCommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedInteractionController;
use App\Http\Controllers\CommentLikeController;
use App\Http\Controllers\LakeVerificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\NotificationController;
use App\Models\Trip;
use App\Services\LakeSafetyService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

// Public trip share page (no login)
Route::get('/t/{token}', function (string $token) {
    $trip = Trip::query()
        ->with(['lake'])
        ->where('is_public', true)
        ->where('share_token', $token)
        ->firstOrFail();

    $safety = null;

    if ($trip->lake) {
        $safety = app(LakeSafetyService::class)->computeForLake($trip->lake);
    }

    return Inertia::render('Trips/Share', [
        'trip' => [
            'id' => $trip->id,
            'trip_date' => $trip->trip_date,
            'time_of_day' => $trip->time_of_day,
            'min_thickness_inches' => $trip->min_thickness_inches,
            'avoid_slush' => (bool) $trip->avoid_slush,
            'avoid_pressure_cracks' => (bool) $trip->avoid_pressure_cracks,
            'target_species' => $trip->target_species,
            'notes' => $trip->notes,
        ],
        'lake' => $trip->lake ? [
            'name' => $trip->lake->name,
            'slug' => $trip->lake->slug,
            'region' => $trip->lake->region,
        ] : null,
        'safety' => $safety,
        'shareUrl' => url()->current(),
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('trips.share.show');

// Public post share page (no login)
Route::get('/p/{token}', [TripPostShareController::class, 'show'])
    ->name('posts.share.show');

// Authenticated area
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/feed/acknowledge', [FeedInteractionController::class, 'acknowledge'])
        ->name('feed.acknowledge');
    Route::post('/feed/like', [FeedInteractionController::class, 'toggleLike'])
        ->name('feed.like');
    Route::post('/feed/comment', [FeedInteractionController::class, 'comment'])
        ->name('feed.comment');

    Route::post('/comments/like', [CommentLikeController::class, 'toggle'])
        ->name('comments.like');

    Route::post('/lakes/{lake}/verify', [LakeVerificationController::class, 'store'])
        ->name('lakes.verify');

    // User profiles (public lookup)
    Route::get('/users/{username}', [UserProfileController::class, 'show'])
        ->name('users.show');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lakes (order matters)
    Route::get('/lakes', [LakeController::class, 'index'])->name('lakes.index');
    Route::get('/lakes/create', [LakeController::class, 'create'])->name('lakes.create');
    Route::post('/lakes', [LakeController::class, 'store'])->name('lakes.store');
    Route::get('/lakes/{slug}', [LakeController::class, 'show'])->name('lakes.show');

    Route::get('/my-lakes', [LakeController::class, 'mine'])->name('lakes.mine');

    Route::post('/lakes/{slug}/favorite', [LakeController::class, 'toggleFavorite'])
        ->name('lakes.favorite');

    // Ice reports
    Route::post('/lakes/{slug}/reports', [IceReportController::class, 'store'])->name('reports.store');
    Route::get('/my-reports', [IceReportController::class, 'myReports'])->name('reports.mine');

    Route::post('/reports/{report}/upvote', [IceReportController::class, 'upvote'])->name('reports.upvote');
    Route::post('/reports/{report}/downvote', [IceReportController::class, 'downvote'])->name('reports.downvote');

    // Trips
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');

    Route::post('/trips/{trip}/share', [TripShareController::class, 'share'])->name('trips.share');
    Route::post('/trips/{trip}/unshare', [TripShareController::class, 'unshare'])->name('trips.unshare');
    Route::post('/trips/{trip}/rotate-share', [TripShareController::class, 'rotate'])->name('trips.share.rotate');

    // IMPORTANT: this should return JSON for axios
    Route::post('/trips/{trip}/share-link', [TripShareController::class, 'createShareLink'])
        ->name('trips.share.create');

    // Community posts
    Route::get('/community', [TripPostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [TripPostController::class, 'show'])->name('posts.show');

    Route::get('/trips/{trip}/post', [TripPostController::class, 'createForTrip'])->name('trips.post.create');
    Route::post('/trips/{trip}/post', [TripPostController::class, 'storeForTrip'])->name('trips.post.store');

    Route::post('/posts/{post}/share', [TripPostShareController::class, 'share'])->name('posts.share');
    Route::post('/posts/{post}/unshare', [TripPostShareController::class, 'unshare'])->name('posts.unshare');

    Route::post('/posts/{post}/comments', [TripPostCommentController::class, 'store'])->name('posts.comments.store');
});

require __DIR__ . '/auth.php';
