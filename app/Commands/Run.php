<?php

namespace App\Commands;

use App\Models\HTTPRequest;
use App\Models\Tab;
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
        $current_tab = Tab::first();
        $current_url = $current_tab->request;

        if($current_url){
            $current_url = $current_tab->url;
        } else {
            $current_url = "Blank page";
        }

        if(!$current_tab){
            $current_tab = new Tab();
            $current_tab->name = "New Tab";
            $current_tab->save();
        }
        //^ Check if we have any open tabs. If not, create one with a blank page.
        if(!Storage::exists('initial_run')){
            $this->notify("⚡Achievement Unlocked⚡", "Run the challenge app", null);
            //echo asset('storage/init');
            touch('storage/initial_run');
        }

        $kill = false;
        while (!$kill){
            $option = $this
                ->menu('Softia Challenge - Main menu', [
                    'Open page',
                    'See Tabs',
                    'Select Tab',
                    'Delete Tab',
                    'See Current Request',
                    'Go Back',
                    'Forwards'
                ])
                ->setBackgroundColour($this->bg_color)
                ->setForegroundColour($this->fg_color)
                ->addLineBreak('-', 1)
                ->setSelectedMarker('▶ ')
                ->addStaticItem('Tabs: '.Tab::count()."\nCurrent Tab: $current_tab->id - $current_tab->name\nURL:$current_url\n$this->broken_msg")
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
                        $nr_param = $this->ask("How many parameters will you use?");
                        while(!is_numeric($nr_param) && intval($nr_param>=0)){
                            $this->error("Please use a valid number...");
                            $nr_param = $this->ask("How many parameters will you use?");
                        }
                        $params = [];
                        for($i = 0; $i<intval($nr_param);$i++){
                            $param_name = $this->ask("Parameter [$i] name:");
                            $param_value = $this->ask("Parameter [$i] value:");
                            $params[$param_name] = $param_value;
                        }

                    }

                    break;
                case "1":
                    break;
                case "2":
                    break;
                case "3":
                    break;
                case "4":
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
