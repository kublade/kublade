<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

/**
 * Class MagicLinkController.
 *
 * This class is the controller for the magic link actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class MagicLinkController extends Controller
{
    public function handle(string $token, Request $request)
    {
        $validator = Validator::make([
            'token' => $token,
        ], [
            'token' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('auth.login')->withErrors($validator);
        }

        if (! $request->hasValidSignature()) {
            return redirect()->route('login')->with('error', 'Invalid token.');
        }

        $userId = Crypt::decryptString($token);

        if (
            ! $userId ||
            Cache::get('magic_link_' . $userId) !== $request->fullUrl()
        ) {
            return redirect()->route('login')->with('error', 'Invalid token.');
        }

        $user = User::where('id', $userId)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        Auth::login($user);

        Cache::forget('magic_link_' . $userId);

        return redirect()->route('home');
    }
}
