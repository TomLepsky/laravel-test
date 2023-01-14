<?php

namespace App\Rules;

use App\Http\Helper\FileHelper;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Translation\PotentiallyTranslatedString;

class FileLimit implements InvokableRule
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
        if (FileHelper::isExceededLimit(Auth::id(), $value->getSize())) {
            $fail('you exceeded the limit of 100 mb');
        }
    }
}
