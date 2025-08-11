<?php

namespace App\Jobs;

use CodeIgniter\Queue\BaseJob;
use CodeIgniter\I18n\Time;

class NumberLoggerJob extends BaseJob
{



    public function process()
    {
        //$number = $this->data['number'];
       for ($i=0; $i <10000000 ; $i++) { 
           log_message('error', "Processing number --:".$i);
       }
    }
}
