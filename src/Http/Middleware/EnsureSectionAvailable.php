<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Support\SectionMaintenanceGate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSectionAvailable
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {
        if (! SectionMaintenanceGate::isEnabled($section)) {
            return $next($request);
        }

        $user = Auth::check() ? User::fromAppUser(Auth::user()) : null;
        if (SectionMaintenanceGate::canBypass($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => SectionMaintenanceGate::message($section)
                    ?? __('messages.section_maintenance_default', ['section' => SectionMaintenanceGate::label($section)]),
            ], 503);
        }

        return response()->view('kaikon::errors.section-maintenance', [
            'section' => $section,
            'sectionLabel' => SectionMaintenanceGate::label($section),
            'message' => SectionMaintenanceGate::message($section),
        ], 503);
    }
}
