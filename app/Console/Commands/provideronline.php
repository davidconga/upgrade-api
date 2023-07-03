<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Services\SendPushNotification;
use Carbon\Carbon;
use App\Models\Common\Provider;

class provideronline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:providerstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the provider online offline status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $Providers = Provider::where('is_online',1)->where('updated_at','<=',\Carbon\Carbon::now()->subMinutes(29))->get();  
        \Log::info($Providers);
          if(!empty($Providers)){
            foreach($Providers as $Provider){                
                // DB::table('Provider')->where('provider_id',$Provider->id)->update(['is_online' =>'0']);
                //send push to provider
                $updateprovider =Provider::where('id',$Provider->id)->first();
                $updateprovider->is_online=0;
                 $updateprovider->save();
                (new SendPushNotification)->provider_offline($Provider->id);
             }
        }           
                
    }
}
