<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Kaikon2\Kaikondb\Models\SectionMaintenance;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Support\SectionMaintenanceGate;

class SectionMaintenanceController extends Controller
{
    public function index(): View
    {
        $sections = SectionMaintenance::query()->get()->sortBy(
            fn (SectionMaintenance $row) => array_search($row->section, SectionMaintenanceGate::SECTIONS, true)
        )->values();

        if ($sections->isEmpty()) {
            $sections = collect(SectionMaintenanceGate::SECTIONS)->map(
                fn (string $section) => new SectionMaintenance([
                    'section' => $section,
                    'enabled' => false,
                ])
            );
        }

        return view('kaikon::admin.section-maintenance', [
            'sections' => $sections,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => 'required|in:'.implode(',', SectionMaintenanceGate::SECTIONS),
            'message_ja' => 'nullable|string|max:500',
            'message_en' => 'nullable|string|max:500',
        ]);

        $record = SectionMaintenance::query()
            ->where('section', $validated['section'])
            ->firstOrFail();

        $record->update([
            'enabled' => $request->boolean('enabled'),
            'message_ja' => $validated['message_ja'] ?? null,
            'message_en' => $validated['message_en'] ?? null,
            'updated_by' => User::fromAppUser(Auth::user())->id,
        ]);

        SectionMaintenanceGate::clearCache();

        return redirect()
            ->route('admin.section_maintenance')
            ->with('status', __('messages.section_maintenance_updated'));
    }
}
