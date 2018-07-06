<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Channel;
use App\Tier;
use App\StripePlan;
use Validator, DB, Hash, Mail, Session, Input;
class StripePlanController extends Controller
{

    private $stripe_Sec_key;
    public function __construct()
    {

        /** Set stripe secret key **/
        $this->stripe_Sec_key = env("STRIPE_SECRET", '');
        \Stripe\Stripe::setApiKey($this->stripe_Sec_key);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects;
        $plans = StripePlan::where('status', false)->get();
        return view('stripePlan.index', ['plans' => $plans, 'projects' => $projects]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stripePlan._modal_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nick_name' => 'required|unique:stripe_plan',
            'price' => 'required',
            'tax' => 'required',
            'net_price' => 'required',
            'trial_expiry' => 'required',
            'product_name' => 'required|unique:stripe_plan',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }


        $stripe_plan = $this->_createPlan($request->nick_name, $request->cent_price, $request->trial_expiry, $request->product_name);
        $plan = new StripePlan();
        $plan->plan_id = $stripe_plan->id;
        $plan->nick_name = $request->nick_name;
        $plan->price = $request->price;
        $plan->tax = $request->tax;
        $plan->net_price = $request->net_price;
        $plan->currency = 'eur'; // default currency set. Now just for Italy
        $plan->trial_expiry = $request->trial_expiry;
        $plan->product_name = $request->product_name;
        $plan->status = false;
        $plan->save();

        $user = Auth::user();
        $user->notify(new \App\Notifications\Plan($plan, 'add_plan'));

        Session::flash('success-message', "New Plan added successfully." );
        return redirect()->route('plan.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $projects = $user->projects;
        $plan= StripePlan::where('id', $id)->where('status', false)->get();
        return view('stripePlan.index', ['plan' => $plan, 'projects' => $projects]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plan= StripePlan::find($id);
        return view('stripePlan._modal_edit', ['plan' => $plan]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|unique:stripe_plan',
            'nick_name' => 'required|unique:stripe_plan',
            'price' => 'required',
            'tax' => 'required',
            'net_price' => 'required',
            'trial_expiry' => 'required',
            'product_name' => 'required|unique:stripe_plan',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $plan= StripePlan::find($id);
        $plan->plan_id = $request->plan_id;
        $plan->nick_name = $request->nick_name;
        $plan->price = $request->price;
        $plan->tax = $request->tax;
        $plan->net_price = $request->net_euro_price;
        $plan->currency = $request->currency;
        $plan->trial_expiry = $request->trial_expiry;
        $plan->product_name = $request->product_name;
        $plan->save();

        $user = Auth::user();
        $user->notify(new \App\Notifications\Plan($plan, 'update_plan'));
        return redirect()->route('plan.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plan= StripePlan::find($id);
        if($plan){
            $plan->status = true;
            $plan->save();

            $user = Auth::user();
            $user->notify(new \App\Notifications\Plan($plan, 'delete_plan'));
        }
        Session::flash('success-message', "Plan is deleted successfully." );
        return redirect()->route('plan.index');
    }


    //Create Plan at stripe
    public function _createPlan($nick_name, $price, $trial_expiry, $product_name)
    {
        $plan = \Stripe\Plan::create(array(
            "nickname" => $nick_name,
            "interval" => "month", // Now currently Cyrano subscription only handle monthly based subscription as was discussed in meting
            "amount" => $price,
            "product" => array(
                "name" => $product_name,
            ),
            "trial_period_days" => $trial_expiry,
            "currency" => "eur" // Based on admin bank account in which country it is. So for Italy it is 'EUR'
        ));
        return $plan;
    }
}
