<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripPost;
use App\Models\TripPostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TripPostController extends Controller
{
    public function index()
    {
        $posts = TripPost::query()
            ->with(['user:id,name', 'lake:id,name,slug,region', 'media'])
            ->where('is_public', true)
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(TripPost $post)
{
    $post->load([
        'trip.lake',
        'user',
        'media',
    ]);

    $comments = $post->comments()
        ->with('user')
        ->orderBy('created_at', 'asc')
        ->get([
            'id',
            'trip_post_id',
            'user_id',
            'body',
            'created_at',
        ]);

    return Inertia::render('Posts/Show', [
        'post' => [
            'id' => $post->id,
            'caption' => $post->caption,
            'created_at' => $post->created_at,
            'user' => $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
            ] : null,
            'lake' => $post->trip && $post->trip->lake ? [
                'name' => $post->trip->lake->name,
                'slug' => $post->trip->lake->slug,
                'region' => $post->trip->lake->region,
            ] : null,
            'media' => $post->media->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'mime' => $m->mime_type,
            ])->values(),
        ],
        'comments' => $comments->map(fn ($c) => [
            'id' => $c->id,
            'body' => $c->body,
            'created_at' => $c->created_at,
            'user' => $c->user ? [
                'id' => $c->user->id,
                'name' => $c->user->name,
            ] : null,
        ])->values(),
    ]);
}


    public function createForTrip(Trip $trip)
    {
        if ((int) $trip->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $trip->load('lake:id,name,slug,region');

        return Inertia::render('Posts/Create', [
            'trip' => $trip,
        ]);
    }

    public function storeForTrip(Request $request, Trip $trip)
    {
        if ((int) $trip->user_id !== (int) Auth::id()) {
            abort(403);
        }

      $data = $request->validate([
    'caption' => ['nullable', 'string', 'max:5000'],
    'people_tags' => ['nullable', 'array'],
    'people_tags.*' => ['string', 'max:50'],
    'location_tags' => ['nullable', 'array'],
    'location_tags.*' => ['string', 'max:50'],
    'is_public' => ['nullable'],
    'photos' => ['nullable', 'array', 'max:10'],
    'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
]);

$post = TripPost::create([
    'user_id' => Auth::id(),
    'trip_id' => $trip->id,
    'lake_id' => $trip->lake_id,
    'caption' => $data['caption'] ?? null,
    'people_tags' => $data['people_tags'] ?? null,
    'location_tags' => $data['location_tags'] ?? null,
    'is_public' => $request->boolean('is_public', true),
]);


        $files = $request->file('photos', []);
        $i = 0;

        foreach ($files as $file) {
            $path = $file->store('posts', 'public');

            TripPostMedia::create([
                'trip_post_id' => $post->id,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => $i++,
            ]);
        }

        return redirect()->route('posts.show', $post->id);
    }
}
