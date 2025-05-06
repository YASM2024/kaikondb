<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
// use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function showEdit(Request $request)
    {
        if (Auth::check() && !Auth::user()->hasVerifiedEmail()){
            return redirect()->route('verification.notice');
        }

        $user = Auth::user()->load('profile', 'roles');
        $user->last_login = $user->last_login();

        $profile = Auth::user()->profile;
        if($profile == null){
            $profile->icon = 'anonymousIcon.svg';
            $profile->show_name = '未設定';
            $profile->discription = '自己紹介文がありません';
        }
        return view('kaikon::profile.edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
