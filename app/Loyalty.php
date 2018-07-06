<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class Loyalty extends Model
{
    use FakeIdTrait;

    protected $fillable = [
        'name', 'start_at'
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function channels()
    {
        return $this->belongsToMany('App\Channel')->withPivot('settings');
    }

    public function toApi() {
        $array = $this->toArray();

        array_set(
            $array,
            $this->getKeyName(),
            $this->getRouteKey()
        );

        $array['project_id'] = $this->project->getRouteKey();

        unset($array['project']);

        return $array;
    }
}