<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StripePlan extends Model
{
    /**
     * Model attached to the table
     */
    protected $table = 'stripe_plan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['plan_id', 'nick_name', 'price', 'tax', 'net_price', 'currency', 'trial_expiry', 'product_name', 'status'];


    /**
     * Get all tier that use a specific plan
     */
    public function tier()
    {
        return $this->hasMany('App\Tier', 'prod_plan_id');
    }
}
