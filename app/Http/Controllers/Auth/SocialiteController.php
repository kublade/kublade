<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class SocialiteController.
 *
 * This class is the controller for the socialite actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class SocialiteController extends Controller
{
    /**
     * Redirect the user to the social provider's authentication page.
     *
     * @param string $provider
     *
     * @return RedirectResponse
     */
    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social provider.
     *
     * @param string $provider
     *
     * @return RedirectResponse
     */
    public function handleCallback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Error logging in with "' . $provider . '".');
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        Auth::login($user);

        return redirect()->route('home');
    }
}
