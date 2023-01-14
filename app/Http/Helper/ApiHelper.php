<?php

namespace App\Http\Helper;

use Illuminate\Http\JsonResponse;

class ApiHelper
{
    /**
     * Create json response
     *
     * @param int $code
     * @param array|null $data
     * @param string|null $message
     * @return JsonResponse
     */
    public static function response(int $code, array $data = null, string $message = null) : JsonResponse
    {

        return response()->json(array_filter([
            'message' => $message,
            'data' => $data
        ]), $code);
    }
}
