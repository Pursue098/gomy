<?php

namespace App\Channels;

use App\Project;
use App\Channel;

interface Channable {

    public function channel();

    public function picture();

}