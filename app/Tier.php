<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{

    /**
     * Model attached to the table
     */
    protected $table = 'tiers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'channel_type', 'status', 'comp_start', 'comp_end', 'prod_plan_id'];

    /**
     * Get the channel for a tier.
     */
    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    /**
     * Get associated plan for a tier.
     */
    public function plan()
    {
        return $this->belongsTo('App\StripePlan', 'prod_plan_id');
    }
}
