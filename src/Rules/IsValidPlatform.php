<?php

namespace Imageplus\Sns\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsValidPlatform implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array(
            $value,
            array_keys(config('sns.platform_arns'))
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ":attribute is not a valid platform";
    }
}
