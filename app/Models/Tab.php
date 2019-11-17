<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tab extends Model
{
    function requests(){
        return $this->hasMany(HTTPRequest::class);
    }
    function request(){
        return $this->hasOne(HTTPRequest::class,'tab_id','request_id');
    }
}
