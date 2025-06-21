<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;

/**
 * Class ActivityController.
 *
 * This class is the controller for the activity actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ActivityController extends Controller
{
    /**
     * Show the activity index page.
     *
     * @return \Illuminate\View\View
     */
    public function page_index()
    {
        return view('activity.index', [
            'activities' => ActivityLog::query()
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
