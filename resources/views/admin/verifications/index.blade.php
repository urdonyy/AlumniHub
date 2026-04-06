<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verification Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'verification-approved')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ __('Verification approved successfully.') }}
                </div>
            @endif

            @if (session('status') === 'verification-rejected')
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ __('Verification rejected successfully.') }}
                </div>
            @endif

            @if (session('status') === 'verification-already-reviewed')
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    {{ __('This verification document was already reviewed.') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6">
                <form method="get" class="flex items-end gap-3">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select id="status" name="status"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="pending" @selected($status === 'pending')>{{ __('Pending') }}</option>
                            <option value="approved" @selected($status === 'approved')>{{ __('Approved') }}</option>
                            <option value="rejected" @selected($status === 'rejected')>{{ __('Rejected') }}</option>
                        </select>
                    </div>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('User') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Email') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Submitted') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Notes') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($documents as $document)
                                <tr>
                                    <td class="px-4 py-3">{{ $document->user->name }}</td>
                                    <td class="px-4 py-3">{{ $document->user->email }}</td>
                                    <td class="px-4 py-3">{{ $document->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $document->status }}</td>
                                    <td class="px-4 py-3 max-w-xs">{{ $document->admin_notes ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="mb-2">
                                            <a href="{{ route('admin.verifications.document', $document) }}" target="_blank"
                                                rel="noopener"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                {{ __('View File') }}
                                            </a>
                                        </div>

                                        @if ($document->status === 'pending')
                                            <div class="flex flex-col gap-2">
                                                <form method="post"
                                                    action="{{ route('admin.verifications.approve', $document) }}"
                                                    class="space-y-2">
                                                    @csrf
                                                    @method('patch')
                                                    <textarea name="admin_notes" rows="2"
                                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        placeholder="Optional approval note"></textarea>
                                                    <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                                </form>

                                                <form method="post"
                                                    action="{{ route('admin.verifications.reject', $document) }}"
                                                    class="space-y-2">
                                                    @csrf
                                                    @method('patch')
                                                    <textarea name="admin_notes" rows="2"
                                                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                                                        placeholder="Reason for rejection" required></textarea>
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-md border border-transparent bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 focus:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-25">{{ __('Reject') }}</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-500">{{ __('Reviewed') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        {{ __('No verification documents found for this status.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>