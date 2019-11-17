<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HTTPRequest extends Model
{
    const TYPE_GET = "GET",
        TYPE_POST="POST";
    protected $table = "requests";

    function table(){
        return $this->belongsTo(Tab::class);
    }

    function getNextAttribute(){
        return HTTPRequest::find($this->front_id);
    }

    function getPreviousAttribute(){
        return HTTPRequest::find($this->back_id);
    }
}
