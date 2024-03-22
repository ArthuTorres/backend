<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ApiController;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends ApiController
{
    public function __construct(Cliente $model)
    {
        $this->anonymous = ['lookup', 'signup'];
        parent::__construct($model);
    }

    public function signup(Request $request){
        return $this->store($request);
    }
}
