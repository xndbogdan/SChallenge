<?php

namespace App\Commands;

use App\Models\HTTPRequest;
use App\Models\Tab;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Run extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start the browser';
    private $broken = false;
    private $broken_msg = null;
    private $bg_color = 'green';
    private $fg_color = 'blue';
    private function break_browser(){
        $this->broken=true;
        $tmp = $this->bg_color;
        $this->bg_color=$this->fg_color;
        $this->fg_color=$tmp;
        $this->broken_msg="Browser: Bricked";
        $this->notify("⚡Achievement Unlocked⚡", "Break the browser 💥💥💥", null);
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //^ Check if we have any open tabs. If not, create one with a blank page.
        if(!Storage::exists('initial_run')){
            $this->notify("⚡Achievement Unlocked⚡", "Run the challenge app", null);
            //echo asset('storage/init');
            touch('storage/initial_run');
        }

        $current_tab = Tab::all()->first();
        $current_url=null;

        $kill = false;
        while (!$kill){


            if($current_tab) {
                $current_url = HTTPRequest::find($current_tab->request_id);
            }

            if(!$current_tab){
                $current_tab = Tab::all()->first();
            }

            if(!$current_tab){
                $current_tab = new Tab();
                $current_tab->name = "Blank page";
                $current_tab->save();
            }

            if($current_url!=null){
                $current_url = $current_url->url;
            } else {
                $current_url = "Blank page";
            }

            $option = $this
                ->menu('Softia Challenge - Main menu', [
                    'Open page',
                    'Select Tab',
                    'Add Tab',
                    'Delete Tab',
                    'See Current Request',
                    'Go Back',
                    'Forwards'
                ])
                ->setBackgroundColour($this->bg_color)
                ->setForegroundColour($this->fg_color)
                ->addLineBreak('-', 1)
                ->setSelectedMarker('▶ ')
                ->addStaticItem('Tabs: '.Tab::count()."\nCurrent Tab: $current_tab->id - $current_tab->name\nURL: $current_url\n$this->broken_msg")
                ->open();
            switch ($option){
                case "0":
                    $url = $this->ask('URL:');
                    if(!filter_var($url, FILTER_VALIDATE_URL)){
                        if(!$this->broken){
                         $this->break_browser();
                        }
                        $this->ask("Wait...you can't do that!");

                    } else {
                        $type = $this->ask('Choose request type (get/post)');
                        while(strtoupper($type)!=HTTPRequest::TYPE_POST && strtoupper($type)!=HTTPRequest::TYPE_GET){
                            if($type=="") $type="NULL";
                            $this->error("We are sorry, but we don't currently support $type requests.");
                            $type = $this->ask('Choose request type (get/post)');
                        }
                        if(strtoupper($type)==HTTPRequest::TYPE_GET){
                            $current_tab->accessUrl(new Uri($url));
                            $current_url = $url;
                            $current_tab->name = $url;
                            $current_tab->save();
                        } else {
                            $nr_param = $this->ask("How many parameters will you use?");
                            while(!is_numeric($nr_param) && intval($nr_param>=0)){
                                $this->error("Please use a valid number...");
                                $nr_param = $this->ask("How many parameters will you use?");
                            }
                            $params = [];
                            for($i = 0; $i<intval($nr_param);$i++){
                                $param_name = $this->ask("Parameter [$i] name");
                                $param_value = $this->ask("Parameter [$i] value");
                                $params[$param_name] = $param_value;
                            }
                            $current_tab->submitForm(new Uri($url),$params);
                            $current_url = $url;
                            $current_tab->name = $url;
                            $current_tab->save();
                        }
                    }

                    break;
                case "1":
                    foreach(Tab::all() as $tab){
                        if($tab->id == $current_tab->id){
                            $this->info("$tab->id - $tab->name (SELECTED)");
                        } else {
                            $this->info("$tab->id - $tab->name");
                        }
                    }
                    $tab_id = $this->ask("Type in id, or 'X' to cancel");
                    $new_tab = Tab::find($tab_id);
                    if($new_tab!=null){
                        $current_tab = $new_tab;
                        $current_url = $current_tab->name;
                    }
                    break;
                case "2":
                        $tab = new Tab();
                        $tab->name = "Blank page";
                        $tab->save();
                        $this->ask("Tab added. Press enter to continue.");
                    break;
                case "3":
                    $tabs = Tab::count();
                    if($tabs < 2){
                        $this->ask("You only have one tab. It cannot be deleted. Press enter to continue.");
                    } else {
                        foreach(Tab::all() as $tab){
                            if($tab->id == $current_tab->id){
                                $this->info("$tab->id - $tab->name (SELECTED)");
                            } else {
                                $this->info("$tab->id - $tab->name");
                            }
                        }
                        $tab_id = $this->ask("Type in id, or 'X' to cancel");
                        $req = HTTPRequest::where('tab_id',$tab_id)->first();
                        $tab = Tab::find($tab_id);
                        $tab->request_id = null;
                        $tab->save();
                        while($req){
                            $req->delete();
                            $req = HTTPRequest::where('tab_id',$tab_id)->first();

                        }

                        $cti = $current_tab->id;
                        $tab->delete();
                        if($tab_id==$cti){
                            $current_tab=null;
                            $current_url=null;
                        }
                    }
                    //delete tab
                    break;
                case "4":
                    $request = HTTPRequest::find($current_tab->request_id);
                    if (!$request) {
                        $this->info("Nothing to show...");
                        $this->ask("Press enter to continue");
                    } else {
                        $detailed = $this->ask('You want to see a detailed view, or a simple one? (d/s)');
                        if($detailed=="d"){
                            $request = HTTPRequest::find($current_tab->request_id);
                            $this->info("Method: $request->request_method");
                            $this->info("URL: $request->url");
                            $this->info("Parameters $request->parameters");
                            $this->info("Status Code: $request->status_code");
                            $this->info("Response Header: $request->response_header");
                            $this->info("Response Body: $request->response_body");
                            $this->ask("Press enter to continue");
                        } else if($detailed=="s") {
                            $this->info("Method: $request->request_method");
                            $this->info("URL: $request->url");
                            $this->info("Status Code: $request->status_code");
                            $this->ask("Press enter to continue");
                        }
                        else {
                            $this->ask('This is not a correct option. Press enter to continue');
                        }
                    }

                    //see current request
                    break;
                case "5":
                    //back
                    $req = HTTPRequest::find($current_tab->request_id);
                    if(!$req){
                        $this->info("This tab never made any requests.");
                        $this->ask("Press enter to continue");
                    }
                    else if(!$req->back_id){
                        $this->notify("⚡Achievement Unlocked⚡", "You have reached the end, friend.", null);
                        $this->info("There are no prior requests...");
                        $this->ask("Press enter to continue");
                    } else {
                        $current_tab->request_id = $req->back_id;
                        $current_url = HTTPRequest::find($current_tab->request_id)->url;
                        $current_tab->name = $current_url;
                        $current_tab->save();
                    }
                    break;
                case "6":
                    //forwards
                    $req = HTTPRequest::find($current_tab->request_id);
                    if(!$req){
                        $this->info("This tab never made any requests.");
                        $this->ask("Press enter to continue");
                    }
                    else if(!$req->front_id){
                        $this->info("There are no further requests...");
                        $this->ask("Press enter to continue");
                    } else {
                        $current_tab->request_id = $req->front_id;
                        $current_url = HTTPRequest::find($current_tab->request_id)->url;
                        $current_tab->name = $current_url;
                        $current_tab->save();
                    }
                    break;
                default:
                    $kill=true;
                    break;
            }
        }
        exit();


    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
