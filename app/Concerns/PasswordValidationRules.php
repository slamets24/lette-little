<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Get browser password manager rules that mirror Laravel's default password rule.
     */
    protected function passwordRulesAttribute(): string
    {
        $rules = Password::default()->appliedRules();

        $passwordRules = ['minlength: '.$rules['min']];

        if ($rules['max'] !== null) {
            $passwordRules[] = 'maxlength: '.$rules['max'];
        }

        if ($rules['mixedCase']) {
            $passwordRules[] = 'required: lower';
            $passwordRules[] = 'required: upper';
        }

        if ($rules['numbers']) {
            $passwordRules[] = 'required: digit';
        }

        if ($rules['symbols']) {
            $passwordRules[] = 'required: special';
        }

        return implode('; ', $passwordRules).';';
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
