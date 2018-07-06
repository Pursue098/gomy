<?php

namespace App\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use App\Channels\Channable;
use App\Project;
use App\Channel;

class Zepto extends Model implements Channable
{
    use FakeIdTrait;

    public $incrementing = true;

    protected $table = 'ch_zepto';

    protected $guarded = [];

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
        return '/img/z.png';
    }
}