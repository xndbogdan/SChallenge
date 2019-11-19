<?php

namespace Tests\Unit;

use App\Models\HTTPRequest;
use App\Models\Tab;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class BrowserPOSTTest extends TestCase
{
    /**
     * A basic post test case.
     *
     * @return void
     */
    public function testBrowserTest()
    {
        $websites = collect(['https://httpbin.org/post','https://reqres.in/api/users']);
        //^Websites with 100% uptime
        $tab = new Tab();
        $tab->name="UNIT TESTING";
        $tab->save();
        //GET TESTING
        foreach ($websites as $website){
            $request = $tab->submitForm(new Uri($website), ['user_id' => 5]);
            $this->assertTrue($request->status_code == "200" || $request->status_code == "201");
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
