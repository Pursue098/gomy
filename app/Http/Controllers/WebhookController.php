<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Payment;
use App\Subscriptions;
use App\Payment as Charge;
use App\User;
use Laravel\Cashier\Http\Controllers\WebhookController as StripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\Controllers;

class WebhookController extends StripeController
{


    private $stripe_Sec_key;
    public function __construct()
    {
        /** Set stripe secret key **/
        $this->stripe_Sec_key = env("STRIPE_SECRET", '');
        \Stripe\Stripe::setApiKey($this->stripe_Sec_key);
    }

    /**
     *
     * Handle a Stripe webhook for resume subscription
     *
     * @param Request  $payload
     * @return \Illuminate\Http\Response
     */
    public function handleCustomerSubscriptionUpdated(Request $payload)
    {
        $payload = @file_get_contents('php://input');
        $webhook = json_decode( $payload, true);

        $cus_id        = @$webhook['data']['object']['customer'];
        $invoice_id    = @$webhook['data']['object']['id'];
        $amount        = @$webhook['data']['object']['amount_paid'];
        $new_sub_id    = @$webhook['data']['object']['subscription'];
	    $old_plan_id = @$webhook['data']['object']['lines']['data'][0]['plan']['id'];
        $old_plan_name = @$webhook['data']['object']['lines']['data'][0]['plan']['nickname'];
        $type = @$webhook['data']['object']['lines']['data'][0]['plan']['interval'];
	
        $old_sub_id = @$webhook['data']['object']['subscription'];
//        these hard code is only for because
        $tax = 1;
        $gateway = 'Stripe'; // Must be hard code in this method becuase we are using stripe in this method. This field value is used payment table
        $description = 'Subscription is active'; //This will also be hard code. because it is just description and stored in payment table.
        $gateway_mode = 'Test'; //This is stripe test mode, will be dynamic soon


        if(isset($old_sub_id) && isset($cus_id)){
            $old_transaction = Payment::where('transaction_id', $old_sub_id)->get();
	        if(isset($old_transaction[0])){
                $channel_id = $old_transaction[0]->channel_id;
                $channel = Channel::find($channel_id);
                $project = $channel->project;
                $user = User::where('stripe_id', $cus_id)->get();
                $this->_performSubscription($new_sub_id, $old_plan_id, $old_plan_name, $user);
                $this->_performPayment($project, $channel, $gateway, $gateway_mode, $amount, $tax, $type, $description, $new_sub_id, $user);

                    $user[0]->notify(new \App\Notifications\WebhookNotifications($project[0], $channel, $user[0], $invoice_id, 'subscription'));

	        }
        }

        return response('Webhook subscription active is Handled ', 200);
    }

    /**
     *
     * Handle a Stripe webhook for end trial period
     *
     * @param Request  $payload
     * @return \Illuminate\Http\Response
     */
    public function handleCustomerSubscriptionTrial_will_end(Request $payload)
    {
        $payload = @file_get_contents('php://input');
        $webhook = json_decode( $payload, true);

        $cus_id = $webhook['data']['object']['customer'];
        $user = User::where('stripe_id', $cus_id)->latest()->first();
        $user->note = $payload;
        $user->save();
        $old_sub_id = $webhook['data']['object']['id'];
	    if(isset($old_sub_id) && isset($cus_id)){

		
            $subscriptions = Subscriptions::where('stripe_id', $old_sub_id)->latest()->first();
            $subscriptions->trial_ends_at = date("Y-m-d H:i:s");

            $subscriptions->save();

            $old_transaction = Payment::where('transaction_id', $old_sub_id)->get();
	        if(isset($old_transaction[0])){
		        $channel_id = $old_transaction[0]->channel_id;
                $channel = Channel::find($channel_id);
                $project = $channel->project;
		
		        $user = User::where('stripe_id', $cus_id)->latest()->first();

	
                $user->notify(new \App\Notifications\WebhookNotifications($project, $channel, $user, '', 'end_trial'));
            }
        }
        return response('Webhook Trail period Handled ', 200);
    }



    //Save payment transaction in database
    public function _performSubscription($stripe_id, $stripe_plan_id, $stripe_plan_name, $user)
    {

        $sub = new Subscriptions();
        $sub->name = $stripe_plan_name;
        $sub->user_id = $user[0]->id;
        $sub->stripe_id = $stripe_id;
        $sub->stripe_plan = $stripe_plan_id;
        $sub->quantity = 1;
        $sub->save();

        return;
    }



    //Save payment transaction in database
    public function _performPayment($project, $channel, $gateway, $gateway_mode, $amount, $tax, $type, $description, $transaction_id, $user)
    {
        $chargeObj = new Charge();
        $chargeObj->channel_id = $channel->id;
        $chargeObj->user_id = $user[0]->id;
        $chargeObj->gateway = $gateway;
        $chargeObj->gateway_mode = $gateway_mode;
        $chargeObj->amount = $amount;
        $chargeObj->tax = $tax;
        $chargeObj->type = $type;
        $chargeObj->description = $description;
        $chargeObj->transaction_id = $transaction_id;
        $chargeObj->status = true;
        $chargeObj->save();
        return;
    }
}
