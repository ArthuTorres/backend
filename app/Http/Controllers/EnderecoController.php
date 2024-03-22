<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ApiController;
use App\Models\Endereco;
use Exception;
use Illuminate\Http\Request;

class EnderecoController extends ApiController
{
    public function __construct(Endereco $model)
    {
        $this->anonymous = ['lookup', 'buscarCep'];
        parent::__construct($model);
    }

    public function buscarCep(Request $request, $cep)
    {
        try {
            $url = 'http://viacep.com.br/ws/' . $cep . '/json/';
            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);

            if ($response === false)
                throw new Exception($response);

            curl_close($curl);
            return response($response);
        } catch (Exception $e) {
            return response()->json(['erro' => 'Erro ao buscar o cep. Detalhes: ' . $e->getMessage()], 500);
        }
    }
}
