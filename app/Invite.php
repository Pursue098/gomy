<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class Invite extends Model
{
    use Notifiable, FakeIdTrait;

    protected $guarded = [];

    protected $hidden = [
        'code',
    ];

    protected $dates = [
        'valid_till'
    ];

    public function project() {
        return $this->belongsTo('App\Project');
    }

    public function author() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function expired() {
        return $this->valid_till->isPast();
    }
}
