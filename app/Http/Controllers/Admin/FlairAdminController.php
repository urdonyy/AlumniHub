<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flair;
use Illuminate\Http\Request;

class FlairAdminController extends Controller
{
    /**
     * Constructor - require admin authentication.
     */
    public function __construct()
    {
        // Middleware is applied via routes
    }

    /**
     * Display a listing of global flairs.
     */
    public function index()
    {
        $flairs = Flair::global()->orderBy('name')->paginate(20);
        return view('admin.flairs.index', compact('flairs'));
    }

    /**
     * Show the form for creating a new flair.
     */
    public function create()
    {
        return view('admin.flairs.create');
    }

    /**
     * Store a newly created flair in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:flairs,name',
            'slug' => 'required|string|max:100|unique:flairs,slug',
            'color' => 'nullable|string|max:7|regex:/#[0-9a-fA-F]{6}/',
            'icon' => 'nullable|string|max:100',
            'is_sticky' => 'boolean',
        ]);

        // Set community_id to null for global flairs
        $validated['community_id'] = null;

        Flair::create($validated);

        return redirect()->route('admin.flairs.index')
            ->with('success', 'Flair created successfully!');
    }

    /**
     * Show the form for editing the specified flair.
     */
    public function edit(Flair $flair)
    {
        return view('admin.flairs.edit', compact('flair'));
    }

    /**
     * Update the specified flair in storage.
     */
    public function update(Request $request, Flair $flair)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:flairs,name,' . $flair->id,
            'slug' => 'required|string|max:100|unique:flairs,slug,' . $flair->id,
            'color' => 'nullable|string|max:7|regex:/#[0-9a-fA-F]{6}/',
            'icon' => 'nullable|string|max:100',
            'is_sticky' => 'boolean',
        ]);

        $flair->update($validated);

        return redirect()->route('admin.flairs.index')
            ->with('success', 'Flair updated successfully!');
    }

    /**
     * Remove the specified flair from storage.
     */
    public function destroy(Flair $flair)
    {
        // Check if flair has associated posts
        if ($flair->posts()->exists()) {
            return redirect()->route('admin.flairs.index')
                ->with('error', 'Cannot delete flair with associated posts');
        }

        $flair->delete();

        return redirect()->route('admin.flairs.index')
            ->with('success', 'Flair deleted successfully!');
    }
}
