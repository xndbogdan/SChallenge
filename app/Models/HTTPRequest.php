<?php

namespace App\Models;

use App\Interfaces\PageInterface;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HTTPRequest extends Model implements PageInterface
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

    public function getUrl(): UriInterface
    {
        $uri = new Uri($this->url);
        return $uri;
    }
}
