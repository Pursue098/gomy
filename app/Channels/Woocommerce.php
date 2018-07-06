<?php

namespace App\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Automattic\WooCommerce\Client;
use Propaganistas\LaravelFakeId\FakeIdTrait;
use App\Jobs\GrabChannelWoocommerce;
use App\Channels\Channable;
use App\Project;
use App\Channel;
use App\Channels\Woocommerce\Customer;
use App\Channels\Woocommerce\Order;

class Woocommerce extends Model implements Channable
{
    use FakeIdTrait;

    public $incrementing = true;

    public static $index = 'woocommerce';

    protected $table = 'ch_woocommerce';

    protected $guarded = [];

    protected $client;

    protected $hidden = [
        'consumer_key', 'consumer_secret'
    ];

    public function channel() {
        return $this->morphOne('App\Channel', 'channable');
    }

    public function getWoocommerceId() {
        return 'wc-' . $this->id;
    }


    public function picture() {
        return $this->picture;
    }

    /**************************************************************************************************
     *                                            GRABBING
     **************************************************************************************************/


    /**************************************************************************************************
     *                                             WEBHOOKS
     **************************************************************************************************/
}