<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable;

    private $touched_at = 0;

    public function touch()
    {
        if (method_exists($this->job, 'getPheanstalk')) {

            $now = time();

            if ($now - $this->touched_at >= 30) {
                $this->touched_at = $now;
                $this->job->getPheanstalk()->touch($this->job->getPheanstalkJob());
            }
        }
    }
}
