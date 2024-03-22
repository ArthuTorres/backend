<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends ApiController
{
    protected $anonymous = ['lookup', 'signup', "ping"];
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function signup(Request $request){
        return $this->store($request);
    }
}
