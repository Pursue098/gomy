<?php

namespace App\Channels\Poynt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Channels\Channable;
use App\Project;
use App\Channel;
use App\Channel\Poynt;

class Device extends Model
{
    public $incrementing = false;

    protected $table = 'ch_poynt_device';

    protected $guarded = [];

    protected $hidden = [

    ];

    public function channel() {
        return $this->belongsTo('App\Channels\Poynt');
    }
}