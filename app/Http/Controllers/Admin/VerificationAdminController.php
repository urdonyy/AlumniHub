<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationDocument;
use App\Notifications\VerificationStatusChanged;
use App\Services\CommunityAutoJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VerificationAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $documents = VerificationDocument::query()
            ->with(['user', 'reviewer'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.verifications.index', [
            'documents' => $documents,
            'status' => $status,
        ]);
    }

    public function approve(
        Request $request,
        VerificationDocument $verificationDocument,
        CommunityAutoJoinService $communityAutoJoinService
    ): RedirectResponse {
        if ($verificationDocument->status !== 'pending') {
            return back()->with('status', 'verification-already-reviewed');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $verificationDocument->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $verificationDocument->user()->update([
            'account_status' => 'approved',
        ]);

        $approvedUser = $verificationDocument->user()->first();

        if ($approvedUser !== null) {
            $communityAutoJoinService->attachMatchingCommunities($approvedUser);
        }

        $verificationDocument->user->notify(new VerificationStatusChanged($verificationDocument));

        return back()->with('status', 'verification-approved');
    }

    public function viewDocument(VerificationDocument $verificationDocument): BinaryFileResponse
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($verificationDocument->document_path), 404, 'Document not found.');

        return response()->file(
            $disk->path($verificationDocument->document_path),
            [
                'Content-Disposition' => 'inline; filename="' . basename($verificationDocument->document_path) . '"',
            ]
        );
    }

    public function reject(Request $request, VerificationDocument $verificationDocument): RedirectResponse
    {
        if ($verificationDocument->status !== 'pending') {
            return back()->with('status', 'verification-already-reviewed');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $verificationDocument->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        $verificationDocument->user()->update([
            'account_status' => 'rejected',
        ]);

        $verificationDocument->user->notify(new VerificationStatusChanged($verificationDocument));

        return back()->with('status', 'verification-rejected');
    }
}
