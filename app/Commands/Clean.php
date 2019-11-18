<?php

namespace App\Commands;

use App\Models\Cookie;
use App\Models\HTTPRequest;
use App\Models\Tab;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Clean extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'clean';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete history and cookies';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $tabs = Tab::all();
        $this->info('Clearing tab requests');
        $bar = $this->output->createProgressBar(count($tabs));
        $bar->start();
        foreach($tabs as $tab){
            $tab->request_id = null;
            $tab->save();
            $bar->advance();
        }
        $bar->finish();
        $requests = HTTPRequest::all();
        $this->info(PHP_EOL.'Deleting requests');
        $bar = $this->output->createProgressBar(count($requests));
        $bar->start();
        foreach($requests as $request){
            $request->delete();
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL.'nDeleting tabs');
        $bar = $this->output->createProgressBar(count($tabs));
        $bar->start();
        foreach($tabs as $tab){
            $tab->delete();
            $bar->advance();
        }
        $bar->finish();
        $cookies = Cookie::all();
        $this->info(PHP_EOL.'Deleting cookies');
        $bar = $this->output->createProgressBar(count($cookies));
        $bar->start();
        foreach($cookies as $cookie){
            $cookie->delete();
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL.PHP_EOL.'Cleanup sucessful.');
        $this->ask("Press enter to continue");
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
