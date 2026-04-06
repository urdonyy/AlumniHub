<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationUploadRequest;
use App\Models\VerificationDocument;
use Illuminate\Http\RedirectResponse;

class VerificationController extends Controller
{
    public function store(VerificationUploadRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        // Store the document
        $documentPath = $validated['document']->store(
            "verifications/user_{$user->id}",
            'local'
        );

        // Create verification document record
        VerificationDocument::create([
            'user_id' => $user->id,
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('profile.edit')->with('status', 'verification-uploaded');
    }
}
