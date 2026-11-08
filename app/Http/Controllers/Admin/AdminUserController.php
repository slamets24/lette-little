<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    use PasswordValidationRules;

    /**
     * Display admin accounts managed by superadmin.
     */
    public function index(): Response
    {
        $users = User::query()
            ->where('is_admin', true)
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'is_admin', 'is_super_admin', 'email_verified_at', 'created_at']);

        return Inertia::render('admin/users/Index', [
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->isSuperAdmin() ? 'superadmin' : 'admin',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
            ])->values(),
            'stats' => [
                'admins' => $users->where('is_super_admin', false)->count(),
                'superadmins' => $users->where('is_super_admin', true)->count(),
            ],
        ]);
    }

    /**
     * Show the create admin account form.
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/Form', [
            'passwordRules' => $this->passwordRulesAttribute(),
        ]);
    }

    /**
     * Store a newly created admin account.
     */
    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => $validated['password'],
            'is_admin' => true,
            'is_super_admin' => false,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Admin account created.');
    }
}
