<?php

namespace App\Http\Controllers;

use App\Models\TripPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TripPostShareController extends Controller
{
    public function show(string $token)
    {
        $post = TripPost::query()
            ->with(['user:id,name', 'lake:id,name,slug,region', 'media'])
            ->whereNotNull('share_token')
            ->where('share_token', $token)
            ->firstOrFail();

        if (!$post->is_public) {
            abort(404);
        }

        return Inertia::render('Posts/Share', [
            'post' => $post,
            'shareUrl' => url()->current(),
            'canLogin' => route_has('login'),
            'canRegister' => route_has('register'),
        ]);
    }

    public function share(TripPost $post): RedirectResponse
    {
        if ((int) $post->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (empty($post->share_token)) {
            $post->share_token = Str::random(32);
        }

        $post->is_public = true;
        $post->save();

        return back()->with('share_url', url('/p/' . $post->share_token));
    }

    public function unshare(TripPost $post): RedirectResponse
    {
        if ((int) $post->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $post->share_token = null;
        $post->save();

        return back()->with('success', 'Post link removed.');
    }
}

function route_has(string $name): bool
{
    return \Illuminate\Support\Facades\Route::has($name);
}
