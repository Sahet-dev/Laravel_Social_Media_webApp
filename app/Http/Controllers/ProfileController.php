<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function index(User $user, Request $request){
        $isCurrentUserFollower = false;
        if (!Auth::guest()) {
            $isCurrentUserFollower = Follower::where('user_id', $user->id)->where('follower_id', Auth::id())
                ->exists();
        }
        $followerCount = Follower::where('user_id', $user->id)->count();
        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'success' => session('success'),
            'isCurrentUserFollower' => $isCurrentUserFollower,
            'followerCount' => $followerCount,
            'user' => new UserResource($user),
        ]);
    }


    /**
     * Display the user's profile form.
     */
//    public function edit(Request $request): Response
//    {
//        //
//    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile', $request->user())->with('success', 'Your Profile details were Updated.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function updateImage(Request $request)
    {
        $data = $request->validate([
            'cover'=> ['nullable', 'image'],
            'avatar'=> ['nullable', 'image']
        ]);
        $user = $request->user();
        $avatar = $data['avatar'] ?? null;
        /**@var UploadedFile $cover*/
        $cover = $data['cover'] ?? null;

        $success = '';
        if ($cover){
            if ($user->cover_path){
                Storage::disk('public')->delete($user->cover_path);
            }
            $path = $cover->store('user-'.$user->id, 'public');
            $user->update(['cover_path'=> $path]);
            $success = 'Your new Cover Image has been saved.';
        }

        if ($avatar){
            if ($user->avatar_path){
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $avatar->store('user-'.$user->id, 'public');
            $user->update(['avatar_path'=> $path]);
            $success = 'Your new Avatar Image has been saved.';
        }


        session('success', 'Cover Image saved');
        return back()->with('success', $success);
    }
}
