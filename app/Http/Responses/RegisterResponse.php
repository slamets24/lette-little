<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $redirectTo = route('orders.index', absolute: false);

        return $request->wantsJson()
            ? response()->json(['redirect_to' => $redirectTo], 201)
            : redirect()->intended($redirectTo);
    }
}
