<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller.
 *
 * This class is the base controller for all controllers.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
