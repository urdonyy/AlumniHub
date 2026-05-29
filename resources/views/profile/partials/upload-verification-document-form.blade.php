<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Upload Verification Document') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload an alumni verification document (diploma, transcript, or ID). Maximum file size is 5MB.') }}
        </p>
    </header>

    @php
        $currentUser = auth()->user();
        $isApproved  = $currentUser->account_status === 'approved';
        $hasPending  = ! $isApproved && $currentUser->hasPendingVerificationDocument();
        $isRejected  = ! $isApproved && ! $hasPending && $currentUser->account_status === 'rejected';
        $latestRejected = $isRejected
            ? $currentUser->verificationDocuments()->where('status', 'rejected')->latest()->first()
            : null;
    @endphp

    @if ($isApproved)
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-6">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-emerald-900">{{ __('Your account is verified') }}</p>
                    <p class="mt-1 text-sm text-emerald-800">
                        {{ __('Your alumni status has been confirmed. You have full access to AlumniHub.') }}
                    </p>
                </div>
            </div>
        </div>
    @elseif ($hasPending)
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-6">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900">{{ __('Your document is under review') }}</p>
                    <p class="mt-1 text-sm text-amber-800">
                        {{ __("We've received your verification document and it's currently being reviewed by our admin team. You'll be notified once a decision is made.") }}
                    </p>
                </div>
            </div>
        </div>
    @else
        @if ($latestRejected)
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-rose-900">{{ __('Your previous document was rejected') }}</p>
                        @if ($latestRejected->admin_notes)
                            <p class="mt-1 text-sm text-rose-800">
                                <span class="font-medium">{{ __('Admin note:') }}</span> {{ $latestRejected->admin_notes }}
                            </p>
                        @endif
                        <p class="mt-1.5 text-sm text-rose-700">{{ __('Please upload a new document to re-submit your verification.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form method="post" action="{{ route('verification.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="document" :value="__('Select Document')" />
                <input id="document" name="document" type="file"
                    class="mt-3 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-700 file:cursor-pointer"
                    accept=".pdf,.jpg,.jpeg,.png" required />
                <p class="mt-2 text-sm text-gray-500">{{ __('Supported formats: PDF, JPG, JPEG, PNG (Max 5MB)') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('document')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Upload') }}</x-primary-button>

                @if (session('status') === 'verification-uploaded')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                        class="text-sm text-green-600">{{ __('Document uploaded successfully. Awaiting admin review.') }}</p>
                @endif
            </div>
        </form>
    @endif
</section>
