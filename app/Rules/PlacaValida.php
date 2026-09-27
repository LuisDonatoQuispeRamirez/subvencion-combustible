<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class PlacaValida implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalizada = strtoupper(str_replace([' ', '-'], '', $value));

        if (!preg_match('/^[0-9]{3,4}[A-Z]{3}$/', $normalizada)) {
            $fail('La placa no tiene un formato válido (ejemplo: 1234ABC).');
        }
    }
}