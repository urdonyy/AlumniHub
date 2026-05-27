<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Flair Management') }}
            </h2>
            <a href="{{ route('admin.flairs.create') }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                + New Flair
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($flairs->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
                    <p class="text-gray-600">No flairs created yet.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">
                                        Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">
                                        Slug
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">
                                        Color
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">
                                        Sticky
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($flairs as $flair)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            @if ($flair->icon)
                                                <span class="mr-2">{{ $flair->icon }}</span>
                                            @endif
                                            {{ $flair->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $flair->slug }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if ($flair->color)
                                                <div class="flex items-center gap-2">
                                                    <div class="h-6 w-6 rounded border border-gray-300"
                                                        style="background-color: {{ $flair->color }};"></div>
                                                    <span class="text-gray-600">{{ $flair->color }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if ($flair->is_sticky)
                                                <span
                                                    class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="text-gray-400">No</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <a href="{{ route('admin.flairs.edit', $flair) }}"
                                                    class="text-indigo-600 hover:text-indigo-700 font-semibold">
                                                    Edit
                                                </a>
                                                <form method="post" action="{{ route('admin.flairs.destroy', $flair) }}"
                                                    class="inline" onsubmit="return confirm('Delete this flair?');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $flairs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>