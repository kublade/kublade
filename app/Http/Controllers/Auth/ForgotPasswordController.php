<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

/**
 * Class ForgotPasswordController.
 *
 * This class is the controller for the forgot password actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
}
