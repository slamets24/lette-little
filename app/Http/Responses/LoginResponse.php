<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $redirectTo = $user?->isAdmin()
            ? route('dashboard', absolute: false)
            : route('orders.index', absolute: false);

        return $request->wantsJson()
            ? response()->json(['redirect_to' => $redirectTo])
            : redirect()->intended($redirectTo);
    }
}
