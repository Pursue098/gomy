<?php

namespace App\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use App\Channels\Captive\User;
use App\Channels\Channable;
use App\Project;
use App\Channel;

class Captive extends Model implements Channable
{
    use FakeIdTrait;

    public $incrementing = true;

    public static $index = 'captive';

    protected $table = 'ch_captive';

    protected $guarded = [];

    protected $client;

    protected $hidden = [

    ];

    public function channel() {
        return $this->morphOne('App\Channel', 'channable');
    }

    // public function providers()
    // {
    //     return $this->hasMany('App\Channel');
    // }

    public function picture() {
        return '/img/white-wi-fi-100.png';
    }
}