<?php

namespace App\Rules;

use App\Http\Helper\FileHelper;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NotPHP implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail) : void
    {
        if (FileHelper::isPhp($value->getClientOriginalName())) {
            $fail('Extension of the :attribute mustn\'t be .php');
        }
    }
}
