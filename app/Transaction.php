<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    /**
     * Model attached to the table
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['transaction_id', 'data', 'uuid', 'project_id'];


}
