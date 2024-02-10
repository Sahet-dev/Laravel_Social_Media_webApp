<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function index(User $user, Request $request){
        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
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

        return Redirect::route('profile.edit');
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
            'cover'=> ['nullable', 'image', 'mimes:pdf'],
            'avatar'=> ['nullable', 'image']
        ]);
        $user = $request->user();
        $avatar = $data['avatar'] ?? null;
        /**@var UploadedFile $cover*/
        $cover = $data['cover'] ?? null;

        if ($cover){
            //$path = $cover->storeAs('user-'.$user->id, null, 'public');
            $path = $cover->store('avatars/'.$user->id, 'public');
            $user->update(['cover_path'=> $path]);
        }
        session('success', 'Cover Image saved');
        return back()->with('status', 'cover-image-updated');
    }
}
