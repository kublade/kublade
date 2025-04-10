<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Projects\Projects\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class IdentifyProject.
 *
 * This class is the middleware for identifying the project.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class IdentifyProject
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $projects = Project::where(function ($query) {
            $query->where('user_id', '=', Auth::id())
                ->orWhereHas('invitations', function ($query) {
                    $query->where('user_id', '=', Auth::id())
                        ->where('invitation_accepted', '=', true);
                });
        })->get();

        if (! empty($projects)) {
            $request->attributes->add(['projects' => $projects]);
        }

        if ($request->project_id) {
            /* @var Project|null $tenant */
            $project = (clone $projects)
                ->where('id', '=', $request->project_id)
                ->first();

            if (! empty($project)) {
                $request->attributes->add(['project' => $project]);
            }
        }

        return $next($request);
    }
}
