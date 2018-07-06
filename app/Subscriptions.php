<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    /**
     * Model attached to the table
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'name', 'stripe_id', 'stripe_plan', 'quantity', 'trial_ends_at', 'ends_at'];

}
