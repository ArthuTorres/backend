<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ApiController;
use App\Models\PrestadorServico;
use Illuminate\Http\Request;

class PrestadorServicoController extends ApiController
{
    public function __construct(PrestadorServico $model)
    {
        $this->anonymous = ['lookup', 'signup'];
        parent::__construct($model);
    }

    public function signup(Request $request){
        return $this->store($request);
    }
}
