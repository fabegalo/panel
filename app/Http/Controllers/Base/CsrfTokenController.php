<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;

class CsrfTokenController extends Controller
{
    /**
     * Return the token that belongs to the server-side session selected for this request.
     *
     * This endpoint intentionally does not rely on the XSRF-TOKEN cookie. Browsers can retain
     * cookies from an older Panel hostname or deployment, leaving multiple cookies with the same
     * name. Returning the session token directly lets the first failed mutation recover safely.
     */
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['token' => csrf_token()], 200, [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ]);
    }
}
