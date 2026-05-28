<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationUploadRequest;
use App\Models\VerificationDocument;
use Illuminate\Http\RedirectResponse;

class VerificationController extends Controller
{
    public function store(VerificationUploadRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasPendingVerificationDocument()) {
            return redirect()->route('profile.edit', ['section' => 'verification-document'])
                ->withErrors(['document' => 'Your document is already pending review. Please wait for admin approval.']);
        }

        $validated = $request->validated();

        $documentPath = $validated['document']->store(
            "verifications/user_{$user->id}",
            'local'
        );

        VerificationDocument::create([
            'user_id' => $user->id,
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        // Reset account_status so admin sees this as a new pending submission
        $user->update(['account_status' => 'pending']);

        return redirect()->route('profile.edit', ['section' => 'verification-document'])
            ->with('status', 'verification-uploaded');
    }
}
