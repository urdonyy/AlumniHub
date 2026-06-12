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
use Illuminate\Http\Response;

class VerificationAdminController extends Controller
{
    public function index(Request $request): View
    {
        // 'all' (or any unknown value) shows every document; otherwise filter by status.
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

    public function viewDocument(Request $request, VerificationDocument $verificationDocument): Response
    {
        abort_unless(Storage::exists($verificationDocument->document_path), 404, 'Document not found.');

        $path = $verificationDocument->document_path;
        $filename = basename($path);

        // Resolve a correct MIME type. Storage::mimeType can be unreliable for
        // remote disks, so fall back to the file extension for the common types.
        $mimeType = Storage::mimeType($path) ?: null;
        if (! $mimeType || $mimeType === 'application/octet-stream') {
            $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default => 'application/octet-stream',
            };
        }

        // inline (default) powers the in-queue preview; ?download=1 forces a download.
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        // A plain response with explicit headers renders inline reliably (PDFs in
        // an <iframe>, images in <img>), unlike streamDownload() which tends to
        // force a download regardless of the disposition.
        return response(Storage::get($path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
