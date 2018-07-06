<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class Channel extends Model
{
    use FakeIdTrait;

    protected $fillable = [
        'name', 'type'
    ];

    protected $with = ['channable'];

    public static $supported = [
        'facebook'    => ['name' => 'facebook',    'icon' => 'facebook-official', 'enabled' => false],
        'WooCommerce' => ['name' => 'WooCommerce', 'icon' => 'shopping-cart',     'enabled' => false],
        'captive'     => ['name' => 'captive',     'icon' => 'wifi',              'enabled' => false],
        'zepto'       => ['name' => 'zepto',       'icon' => 'cube',              'enabled' => true],
        'poynt'       => ['name' => 'Poynt',       'icon' => 'cc-visa',           'enabled' => false],
        'twitter'     => ['name' => 'twitter',     'icon' => 'twitter',           'enabled' => false],
        'wordpress'   => ['name' => 'wordpress',   'icon' => 'wordpress',         'enabled' => false],
        'instagram'   => ['name' => 'instagram',   'icon' => 'instagram',         'enabled' => false],
        'google+'     => ['name' => 'google+',     'icon' => 'google-plus',       'enabled' => false],
        'amazon'      => ['name' => 'amazon',      'icon' => 'google-plus',       'enabled' => false],
        'LinkedIn'    => ['name' => 'LinkedIn',    'icon' => 'linkedin',          'enabled' => false],
        'YouTube'     => ['name' => 'YouTube',     'icon' => 'youtube',           'enabled' => false],
        'telegram'    => ['name' => 'telegram',    'icon' => 'telegram',          'enabled' => false],
    ];

    // TODO: refactor and remove (should be plural)
    public function project()
    {
        return $this->belongsToMany('App\Project');
    }

    public function projects()
    {
        return $this->belongsToMany('App\Project');
    }

    public function loyalties()
    {
        return $this->belongsToMany('App\Loyalty');
    }

    public function channable()
    {
        return $this->morphTo();
    }

    public function icon()
    {

        return static::$supported[$this->type]['icon'];
    }

    public function toApi() {
        $array = $this->toArray();

        array_set(
            $array,
            $this->getKeyName(),
            $this->getRouteKey()
        );

        $array['channable'] = $this->channable;
        unset($array['channable_id']);
        unset($array['channable_type']);

        return $array;
    }

    /**
     * Get the tiers for a channel.
     */
    public function tiers()
    {
        return $this->hasMany('App\Tier');
    }

    /**
     * One-to-many: Get the payments for a channel.
     */
    public function payments()
    {
        return $this->hasMany('App\Payment');
    }
}
