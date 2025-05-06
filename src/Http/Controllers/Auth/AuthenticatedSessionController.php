<?php

namespace Kaikon2\Kaikondb\Http\Controllers\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Kaikon2\Kaikondb\Events\UserLoggedIn;
use Kaikon2\Kaikondb\Events\UserLoggedOut;
use Kaikon2\Kaikondb\Listeners\LogUserLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogout;

use Kaikon2\Kaikondb\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('kaikon::auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // ログイン成功時にログインイベントを発行
        $user = User::fromAppUser($request->user());
        $email = $request->input('email');
        event(new UserLoggedIn($user, true, $email)); // ログイン成功

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = User::fromAppUser($request->user());
        $email = $user->email;

        Auth::guard('web')->logout();

        // ユーザーがログアウトしたことをイベントとして発火
        event(new UserLoggedOut($user, $email));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
