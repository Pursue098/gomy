<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Channel;

class SettingsController extends Controller
{
    protected $channels = [
        'facebook' => 'Facebook',
    ];

    public function index(Request $request, Project $project, Channel $channel) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function save_fb_app(Request $request, Project $project, Channel $channel) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function save_coefficients(Request $request, Project $project, Channel $channel) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function add_tab(Request $request, Project $project, Channel $channel) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function remove_tab(Request $request, Project $project, Channel $channel, $tab) {
        return $this->invoke($channel, __FUNCTION__, func_get_args());
    }

    public function api_settings(Request $request) {
        $projects = $request->user()->projects()->with('users', 'channels.channable')->get();

        return view('api', ['projects' => $projects]);
    }
}
