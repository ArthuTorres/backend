<?php

namespace App\Models;

use App\Models\Base\BaseModel;

class Endereco extends BaseModel
{
    protected $fillable = [
        "user_id",
        "cep",
        "logradouro",
        "numero",
        "bairro",
        "cidade",
        "estado"
    ];
}
