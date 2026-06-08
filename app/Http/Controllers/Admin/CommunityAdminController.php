<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityAdminController extends Controller
{
    public function index(): View
    {
        $communities = Community::query()
            ->withCount('members')
            ->with('rules')
            ->orderByDesc('is_system')
            ->latest()
            ->get();

        return view('admin.communities.index', [
            'communities' => $communities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:communities,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rule_batch_year' => ['nullable', 'integer', 'between:2024,' . now()->format('Y')],
            'rule_program_course' => ['nullable', 'string', 'max:255'],
        ]);

        $community = Community::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        if (($validated['rule_batch_year'] ?? null) !== null || ! empty($validated['rule_program_course'])) {
            $community->rules()->create([
                'batch_year' => $validated['rule_batch_year'] ?? null,
                'program_course' => $validated['rule_program_course'] ?? null,
            ]);
        }

        // Admin-created communities are non-system (batch) communities, so the
        // PUP-ITECH Official account is not auto-joined here.

        return back()->with('status', 'community-created');
    }

    public function update(Request $request, Community $community): RedirectResponse
    {
        $slugRules = [
            'required',
            'string',
            'max:255',
            'alpha_dash',
            Rule::unique('communities', 'slug')->ignore($community->id),
        ];

        if ($community->is_system) {
            $slugRules[0] = 'nullable';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if (! $community->is_system) {
            $updateData['slug'] = $validated['slug'];
        }

        $community->update($updateData);

        if ($community->is_system) {
            return back()->with('status', 'community-updated-system');
        }

        return back()->with('status', 'community-updated');
    }

    public function destroy(Community $community): RedirectResponse
    {
        if ($community->is_system) {
            return back()->with('status', 'community-delete-blocked-system');
        }

        $community->delete();

        return back()->with('status', 'community-deleted');
    }
}
