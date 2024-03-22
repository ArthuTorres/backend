<?php

namespace App\Models;

use App\Models\Base\BaseModel;

class Cliente extends BaseModel
{
    protected $fillable = [
        "user_id"
    ];

    public $belongsTo = ['user'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
