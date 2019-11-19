<?php

namespace Tests\Unit;

use App\Models\HTTPRequest;
use App\Models\Tab;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class BrowserGETTest extends TestCase
{
    /**
     * A basic get test case.
     *
     * @return void
     */
    public function testBrowserTest()
    {
        $websites = collect(['https://yahoo.com','https://google.com','https://ebay.com','https://amazon.com','https://cnn.com','https://go.com']);
        //^Websites with 100% uptime
        $tab = new Tab();
        $tab->name="UNIT TESTING";
        $tab->save();
        //GET TESTING
        foreach ($websites as $website){
            $request = $tab->accessUrl(new Uri($website));
            $this->assertTrue($request->status_code == "200");
        }
        $tab->request_id = null;
        $tab->save();
        $requests = HTTPRequest::where('tab_id','=',$tab->id)->get();
        foreach($requests as $request){
            $request->delete();
        }
        $request = HTTPRequest::where('tab_id','=',$tab->id)->first();
        $this->assertNull($request);
    }
}
