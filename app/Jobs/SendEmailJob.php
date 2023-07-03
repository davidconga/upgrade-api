<?php
  
namespace App\Jobs;
   
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Foundation\Bus\DispatchesJobs;

use App\Helpers\Helper;
use Mail;
   
class SendEmailJob implements ShouldQueue
{
    use  InteractsWithQueue, Queueable, SerializesModels;
  
    protected $error, $company, $emails;
  
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($error, $company, $emails)
    {
        $this->error = $error;
        $this->company = $company;
        $this->emails = $emails;
    }
   
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject='500 Error in '.$this->company;
        $templateFile='mails/errormail';
        $data = ['body' => $this->error];

        if(count($this->emails) > 0) {
            Helper::send_emails_job($templateFile, $this->emails, $subject, $data);
        }
      
    }
}