<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Upload Verification Document') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload an alumni verification document (diploma, transcript, or ID). Maximum file size is 5MB.') }}
        </p>
    </header>

    <form method="post" action="{{ route('verification.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="document" :value="__('Select Document')" />
            <input id="document" name="document" type="file"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
</section>