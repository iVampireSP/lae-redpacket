<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->success(auth('api')->user());
    }
}
