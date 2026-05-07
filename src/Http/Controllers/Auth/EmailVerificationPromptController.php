<?php

namespace Kaikon2\Kaikondb\Http\Controllers\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();
        $request->session()->flash('status', 'verification-link-sent');

        return view('kaikon::auth.verify-email');
    }
}
