<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    /** Account statuses an admin may assign (matches the users.account_status enum). */
    private const ACCOUNT_STATUSES = ['pending', 'approved', 'rejected'];

    /** Roles an admin may assign here. admin/superadmin are intentionally excluded
     *  so this UI can never escalate privileges. */
    private const EDITABLE_ROLES = ['alumni', 'student'];

    /** Program/course options (mirrors the registration whitelist). */
    private const PROGRAM_COURSES = [
        'Diploma in Civil Engineering Technology (DCvET)',
        'Diploma in Computer Engineering Technology (DCET)',
        'Diploma in Electrical Engineering Technology (DEET)',
        'Diploma in Electronics Engineering Technology (DECET)',
        'Diploma in Information Communication Technology (DICT)',
        'Diploma in Mechanical Engineering Technology (DMET)',
        'Diploma in Office Management Technology (DOMT)',
        'Diploma in Railway Engineering Technology (DRET)',
    ];

    public function index(Request $request): View
    {
        return view('admin.users.index', self::listData($request));
    }

    /**
     * Build the data the user-management table needs (list + filters). Shared by
     * the standalone admin.users.index page and the inline table on the admin
     * dashboard home, so both stay in sync.
     *
     * @return array<string, mixed>
     */
    public static function listData(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role', '');
        $status = $request->query('status', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, ['alumni', 'student', 'admin', 'superadmin'], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when(in_array($status, self::ACCOUNT_STATUSES, true), function ($query) use ($status) {
                $query->where('account_status', $status);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'accountStatuses' => self::ACCOUNT_STATUSES,
            'editableRoles' => self::EDITABLE_ROLES,
            'programCourses' => self::PROGRAM_COURSES,
            'minYear' => 2000,
            'maxYear' => (int) now()->format('Y'),
            'userStats' => self::userStats(),
            'communityActivity' => self::communityActivity(),
        ];
    }

    /**
     * Top communities by (non-trashed) post count, for the activity bar graph.
     *
     * @return array<int, array{name: string, count: int}>
     */
    public static function communityActivity(int $limit = 6): array
    {
        return \App\Models\Community::query()
            ->withCount(['posts' => function ($query) {
                $query->whereNull('trashed_at');
            }])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($c) => ['name' => $c->name, 'count' => (int) $c->posts_count])
            ->all();
    }

    /**
     * Aggregate user counts for the analytics cards. "Online" uses the same
     * last_seen_at window as User::isOnline().
     *
     * @return array<string, int>
     */
    public static function userStats(): array
    {
        $byStatus = User::query()
            ->selectRaw('account_status, COUNT(*) as aggregate')
            ->groupBy('account_status')
            ->pluck('aggregate', 'account_status');

        $online = User::query()
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>', now()->subMinutes(User::ONLINE_THRESHOLD_MINUTES))
            ->count();

        $all = (int) $byStatus->sum();

        return [
            'all' => $all,
            'online' => $online,
            'offline' => max($all - $online, 0),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'rejected' => (int) ($byStatus['rejected'] ?? 0),
        ];
    }

    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if(in_array($user->role, ['admin', 'superadmin'], true), 403, 'This account cannot be edited here.');

        $validated = $request->validate([
            // Role is editable only between non-privileged roles. admin/superadmin
            // are never selectable here, so this UI can't escalate privileges.
            'role' => ['required', Rule::in(self::EDITABLE_ROLES)],
            'account_status' => ['required', Rule::in(self::ACCOUNT_STATUSES)],
            'batch_year' => ['required', 'integer', 'between:2000,' . (int) now()->format('Y')],
            'program_course' => ['required', Rule::in(self::PROGRAM_COURSES)],
        ]);

        // Only the four whitelisted fields are ever written.
        $user->update([
            'role' => $validated['role'],
            'account_status' => $validated['account_status'],
            'batch_year' => $validated['batch_year'],
            'program_course' => $validated['program_course'],
        ]);

        // Inline editing on the dashboard expects JSON; the standalone page redirects.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'account_status' => $user->account_status,
                    'batch_year' => $user->batch_year,
                    'program_course' => $user->program_course,
                ],
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'user-updated');
    }
}
