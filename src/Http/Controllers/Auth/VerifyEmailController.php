<?php

namespace Kaikon2\Kaikondb\Http\Controllers\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller as BaseController;
use Kaikon2\Kaikondb\Http\Controllers\UserController;
use Kaikon2\Kaikondb\Models\User;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends BaseController
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            
            event(new Verified($request->user()));
    
            // 初回認証時に 'user' ロールを付与
            $user = new UserController;
            $user->assignRoleIfNotExists(User::fromAppUser($request->user()), 'user');
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
