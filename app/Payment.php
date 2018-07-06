<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * Model attached to the table
     */
    protected $table = 'payment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['channel_id', 'user_id', 'gateway', 'gateway_mode', 'amount', 'tax', 'type', 'description', 'transaction_id', 'status'];

    /**
     * One-to-one: Payment relate with channel.
     */
    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    /**
     * One-to-many: Payment performed by one user.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
