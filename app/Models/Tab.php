<?php

namespace App\Models;

use App\Interfaces\BrowserInterface;
use App\Interfaces\PageInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SessionCookieJar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Http\Message\UriInterface;

class Tab extends Model implements BrowserInterface
{

    protected $client;
    protected $cookieJar;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    function requests(){
        return $this->hasMany(HTTPRequest::class);
    }
    function request(){
        return $this->hasOne(HTTPRequest::class,'tab_id','request_id');
    }

    public function getCurrentPage(): PageInterface
    {
        return HTTPRequest::find($this->request_id);
    }

    public function submitForm(UriInterface $url, array $params): PageInterface
    {
        $options = [
            'form_params' => [
                $params
            ]
        ];
        $url_info = parse_url($url);
        $this->client = new Client(['base_uri' => $url_info['host'], 'exceptions' => false]);
        $response = $this->client->request(HTTPRequest::TYPE_GET,$url_info['path'],$options);
        try {
            $httpRequest = new HTTPRequest();
            $httpRequest->back_id = $this->request_id;
            $httpRequest->front_id = null;
            $httpRequest->tab_id = $this->id;
            $httpRequest->request_method = HTTPRequest::TYPE_GET;
            $httpRequest->url = $url;
            $httpRequest->parameters = null;
            $httpRequest->status_code = $response->getStatusCode();
            $httpRequest->response_header = $response->getHeaders();
            $httpRequest->response_body = $response->getBody();
            $httpRequest->save();
            $oldRequest = HTTPRequest::find($this->request_id);
            if($oldRequest){
                $oldRequest->front_id = $httpRequest->id;
            }
            $oldRequest->save();
        }
        catch(\Exception $ex){
            Log::error($ex->getTrace()[0]);
        }
        return $httpRequest;
    }

    public function accessUrl(UriInterface $url): PageInterface
    {
        $url_info = parse_url($url);
        $this->client = new Client(['base_uri' => $url_info['host'], 'exceptions' => false]);
        $response = $this->client->request(HTTPRequest::TYPE_GET, isset($url_info['path'])?$url_info['path']:'');
        $headers = '';
        foreach ($response->getHeaders() as $k=>$v){
            $headers.="$k:$v[0]\n";
        }
        try {
            $httpRequest = new HTTPRequest();
            $httpRequest->back_id = $this->request_id;
            $httpRequest->front_id = null;
            $httpRequest->tab_id = $this->id;
            $httpRequest->request_method = HTTPRequest::TYPE_GET;
            $httpRequest->url = $url;
            $httpRequest->parameters = null;
            $httpRequest->status_code = $response->getStatusCode();
            $httpRequest->response_header = utf8_encode($headers);
            $httpRequest->response_body = utf8_encode($response->getBody());
            $httpRequest->save();
            $oldRequest = HTTPRequest::find($this->request_id);
            $this->request_id = $httpRequest->id;
            if($oldRequest){
                $oldRequest->front_id = $httpRequest->id;
                $oldRequest->save();
            }
            $this->save();
        }
        catch(\Exception $ex){
            echo ('#'.$ex->getLine().'- ['.$ex->getFile().']_'.$ex->getMessage());
            die();
        }
        return $httpRequest;
    }
    public function navigateBack(): Boolean
    {
        $currentPage = HTTPRequest::find($this->request_id);
        if(!$currentPage->back_id){
            return false;
        } else{
            $this->request_id = $currentPage->back_id;
            return true;
        }
    }

    public function navigateForward(): Boolean
    {
        $currentPage = HTTPRequest::find($this->request_id);
        if(!$currentPage->front_id){
            return false;
        } else{
            $this->request_id = $currentPage->front_id;

            return true;
        }
    }
}
