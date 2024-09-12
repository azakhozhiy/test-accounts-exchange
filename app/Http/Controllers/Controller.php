<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\JWTGuard;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getJwtGuard(): JWTGuard
    {
        /** @var JWTGuard $auth */
        $auth = auth()->guard('api');

        return $auth;
    }
}
