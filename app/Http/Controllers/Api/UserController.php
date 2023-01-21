<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ivampiresp\Cocoa\Http\Controller;


class UserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->success(auth('api')->user());
    }
}
