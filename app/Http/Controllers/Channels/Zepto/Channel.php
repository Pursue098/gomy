<?php

namespace App\Http\Controllers\Channels\Zepto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channels\Zepto;
use App\Tier;
use App\Payment;
use App\Project;
use App\StripePlan;
use App\Subscriptions;
use Illuminate\Support\Facades\Auth;

class Channel extends Controller
{
    public function get_configure(Request $request, Project $project, \App\Channel $channel) {
        $projects = $request->user()->projects()->with('users', 'channels.channable')->get();

        return view('channels.zepto.configure', [
            'projects' => $projects,
            'project'  => $project,
            'channel'  => $channel,
        ]);
    }

    public function post_configure(Request $request, Project $project, \App\Channel $channel) {

        $this->validate($request, [
            'complexity'      => 'required'
        ]);

        // Here, before creating the channel the subscription flow should begin

        $zepto = Zepto::create([
            'complexity' => $request->get('complexity'),
            'status'     => 'grabbed', // generally the status is "grabbing" and we run a job that grab historical data. As this is a fake channel there is no data to grab, so we set it as grabbed
        ]);

        $channel->status = 'assigned';

        $zepto->channel()->save($channel);

        return redirect('/')->with('message', ['type' => 'success', 'text' => 'Zepto channel assigned.']);
    }

     public function dashboard(Request $request, Project $project, \App\Channel $channel)
     {
         $user = Auth::user();
         $channable = $channel->channable;
         $subscription_id = $plan_id = $left_days = false;
         $subscription = $general = $plan = $enterprise = null;
         if ($channable->status == 'grabbing') {
             return back()->with('message', ['type' => 'warning', 'text' => 'Channel is not ready.']);
         }
         $tiers = Tier::where('channel_type',$channel->type)->where('status', false)->get();
         $payment = Payment::where('channel_id', $channel->id)->where('user_id', $user->id)->latest('id')->take(2)->get();
         if (count($payment) > 0){
             if(count($payment) == 2){

                 if($payment[0]->gateway == 'Stripe'){
                     $subscription_id = $payment[0]->transaction_id;
                     $general = $payment[0];
                 }elseif($payment[1]->gateway == 'Stripe'){
                     $subscription_id = $payment[1]->transaction_id;
                     $general = $payment[1];
                 }

                 if($payment[0]->gateway == 'Enterprise'){
                     $enterprise = $payment[0];
                 }elseif($payment[1]->gateway == 'Enterprise'){
                     $enterprise = $payment[1];
                 }
             }elseif(count($payment) == 1){
                 if($payment[0]->gateway == 'Stripe'){
                     $subscription_id = $payment[0]->transaction_id;
                     $general = $payment[0];
                 }else{
                     $enterprise = $payment[0];
                 }
             }
         }

         if($subscription_id){
             $subscription = Subscriptions::where('stripe_id', $subscription_id)->get();
             if(isset($subscription) && count($subscription) > 0){
                 $plan_id = $subscription[0]->stripe_plan;
                 $plan = StripePlan::where('plan_id', $plan_id)->get();
                 $plan = $plan[0];
                 $trail_days = $plan->trial_expiry;
                 $created_at = $subscription[0]->created_at;
                 $sub_date = date_format($created_at,"Y/m/d");

                 $sub_date = new \DateTime($sub_date);
                 $now   = new \DateTime(); // Current date time
                 $diff  = $sub_date->diff($now);
                 $passed = $diff->format("%a");

                 $left_days = $trail_days - $passed;
                 if($left_days < 0){
                     $left_days = 0 ;
                 }
             }
         }

         return view('channels.zepto.dashboard', [
             'project' => $project,
             'channel' => $channel,
             'channable' => $channable,
             'payment' => $payment,
             'tiers' => $tiers,
             'plan' => $plan,
             'subscription' => $subscription,
             'general' => $general,
             'enterprise' => $enterprise,
             'left_days' => $left_days,
             'user' => $user,
         ]);
     }

}