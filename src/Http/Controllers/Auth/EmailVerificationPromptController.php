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

        // 送信は Registered（登録時）と verification.send（再送）のみ。GET で送ると登録直後に二重になる。

        return view('kaikon::auth.verify-email');
    }
}
