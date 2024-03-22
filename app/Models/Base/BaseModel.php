<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BaseModel extends Model
{
    use HasFactory;

    public function transformData(Request $request)
    {
    }

    public function beforeSave()
    {
    }

    public function afterSave()
    {
    }

    public function getRules()
    {
        return [];
    }

    public function with_includes()
    {
        return array_merge($this->belongsTo, $this->hasOne, $this->hasMany);
    }

    public $belongsTo = [];
    public $hasOne = [];
    public $hasMany = [];
}
