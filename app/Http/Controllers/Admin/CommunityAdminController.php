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

        return back()->with('status', 'community-created');
    }

    public function update(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('communities', 'slug')->ignore($community->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $community->update($validated);

        return back()->with('status', 'community-updated');
    }

    public function destroy(Community $community): RedirectResponse
    {
        $community->delete();

        return back()->with('status', 'community-deleted');
    }
}
