<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Projects\Projects\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        $url_project_id     = $request->project_id;
        $session_project_id = Session::get('project_id');

        if ($url_project_id || $session_project_id) {
            /* @var Project|null $tenant */
            $project_by_url = (clone $projects)
                ->where('id', '=', $url_project_id)
                ->first();

            $project_by_session = (clone $projects)
                ->where('id', '=', $session_project_id)
                ->first();

            if (! empty($project_by_url)) {
                $request->attributes->add(['project' => $project_by_url]);

                Session::put('project_id', $project_by_url->id);
            } else {
                if (! empty($project_by_session)) {
                    $request->attributes->add(['project' => $project_by_session]);
                }
            }
        }

        return $next($request);
    }
}
