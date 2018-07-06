<?php

namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Channel;
use App\StripePlan;
use App\Tier;
use Validator, DB, Hash, Mail, Session, Input;

class TierController extends Controller
{

    /**
     * Get all the tiers for a particular channel.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($channel_type)
    {
        $user = Auth::user();
        $projects = $user->projects;

        $tiers= Tier::where('channel_type', $channel_type)->where('status', false)->get();
        return view('tiers.index', ['tiers' => $tiers, 'projects' => $projects, 'channel_type' => $channel_type]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($channel_type)
    {
        $plans = StripePlan::all();
        $max = Tier::max('comp_end');
        return view('tiers._modal_create', ['plans' => $plans, 'channel_type' => $channel_type, 'max' => $max+1]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $channel_type)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'comp_start' => 'required|numeric',
            'comp_end' => 'required|numeric',
            'prod_plan_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $tier = new Tier();
        $tier->name = $request->name;
        $tier->channel_type = $channel_type;
        $tier->comp_start = $request->comp_start;
        $tier->comp_end = $request->comp_end;
        $tier->prod_plan_id = $request->prod_plan_id;
        $tier->status = false;
        $tier->save();

        $user = Auth::user();
        $user->notify(new \App\Notifications\Tier($user, $tier, 'add_tier'));

        Session::flash('success-message', "New Tier added successfully." );
        return redirect()->route('tier.index', ['channel_type' => $channel_type]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $channel_type)
    {
        $user = Auth::user();
        $projects = $user->projects;
        $tiers= Tier::where('channel_type', $channel_type)->where('status', false)->get();
        return view('tiers.index', ['projects' => $projects, 'tiers' => $tiers, 'channel_type' => $channel_type]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($channel_type, $id)
    {
        $tier= Tier::find($id);
        $plans = StripePlan::all();
        return view('tiers._modal_edit', ['tier' => $tier, 'plans' => $plans, 'channel_type' => $channel_type]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $channel_type)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'prod_plan_id' => 'required',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $tier= Tier::find($id);
        $tier->name = $request->name;
        $tier->prod_plan_id = $request->prod_plan_id;
        $tier->save();
        \Session::flash('success-message','Tiers Updated Successfully ');
        $user = Auth::user();
        $user->notify(new \App\Notifications\Tier($user, $tier, 'update_tier'));
        return redirect()->route('tier.index', ['channel_type' => $channel_type]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($channel_type, $id)
    {
        $tier= Tier::find($id);
        if($tier){
            $tier->status = true;
            $tier->save();

            $user = Auth::user();
            $user->notify(new \App\Notifications\Tier($user, $tier, 'delete_tier'));
        }
        Session::flash('success-message', "Tier is deleted successfully." );
        return redirect()->route('tier.index', ['channel_type' => $channel_type]);
    }
}
