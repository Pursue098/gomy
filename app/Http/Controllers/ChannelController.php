<?php

namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Channel;

class ChannelController extends Controller
{


    protected $channels = [
        'facebook'    => 'Facebook',
        'WooCommerce' => 'Woocommerce',
        'captive'     => 'Captive',
        'zepto'       => 'Zepto',
        'poynt'       => 'Poynt',
    ];

    public function get_configure(Request $request, Project $project, Channel $channel) {
        if ($channel->status == 'assigned') {
            return back()->with('message', ['type' => 'warning', 'text' => $channel->name . ' is already configured.']);
        }

        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function after_configure(Request $request, Project $project, Channel $channel) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function post_configure(Request $request, Project $project, Channel $channel) {
        if ($channel->status == 'assigned') {
            return back()->with('message', ['type' => 'warning', 'text' => $channel->name . ' is already configured.']);
        }

        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function dashboard(Request $request, Project $project, Channel $channel) {

        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

}
