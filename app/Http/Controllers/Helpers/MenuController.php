<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getMenu()
    {
        $menu = config('menu.items');
        return response()->json($menu);
    }
}
