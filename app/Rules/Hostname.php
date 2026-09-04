<?php

namespace Pterodactyl\Rules;

use Illuminate\Contracts\Validation\Rule;

class Hostname implements Rule
{
    /**
     * Validate a hostname or IP address without requiring it to resolve during
     * configuration. Connectivity is verified separately by the operator.
     *
     * @param string $attribute
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_IP) !== false
            || filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid hostname or IP address.';
    }
}
