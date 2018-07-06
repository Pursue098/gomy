<?php

namespace App\Http\Controllers;

use App\Role;
use App\StripePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\URL;
use App\Project;
use App\Channel;
use App\Payment as Charge;
use App\Tier;
use App\User;
use App\Subscriptions;
use Validator, DB, Hash, Mail, URL, Session, DateTime, DatePeriod, DateIntercal;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Client;
use \Firebase\JWT\JWT;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{

    private $stripe_Sec_key;
    private $octobat_Sec_key;
    public function __construct()
    {
        /** Set Octobat secret key **/
        $this->octobat_Sec_key = env("OCTOBAT_SECRET", '');

        /** Set stripe secret key **/
         $this->stripe_Sec_key = env("STRIPE_SECRET", '');
        \Stripe\Stripe::setApiKey($this->stripe_Sec_key);

        /** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $project, $channel)
    {
        $user = Auth::user();
        $channel_type = $channel->type;
        $channable = $channel->channable;
        $complexity = $channable->complexity;
        $tax_amount = $planPrice = 0;

        $plan_id = $this->_getPlan($channel_type, $complexity);
        if ($plan_id != null){
            $plan = StripePlan::find($plan_id);
            if(isset($plan)){
                $planPrice = $plan->net_price;
                $price = $plan->price;
                $trial_period = $plan->trial_expiry;
                $tax_amount = $planPrice-$price;
            }
        }

        $currentDate = getdate();
        $dateNumber = $currentDate['mday'];
        if($dateNumber !== 1) {
            $totalNumberOfDaysInCurrentMonth = date("t");
            $leftDays = $totalNumberOfDaysInCurrentMonth - $dateNumber;
            //Calculating remaining time
            date_default_timezone_set("Asia/Karachi");
            $start = date_create('now');
            $end = date_create('tomorrow');
            $diff=date_diff($end,$start);
            $time = $diff->h.':'.$diff->i.':'.$diff->s;
            $parsed = date_parse($time);
            $hours = $parsed['hour'];
            $min = $parsed['minute'];

            $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
            $remainingTimeIssueChargeBalance = ($planPrice/$totalNumberOfDaysInCurrentMonth);
            $perDayChargeAccordingToSeconds = $remainingTimeIssueChargeBalance/86400;
            $addToNetPaymentToHandleRemainigHours = $perDayChargeAccordingToSeconds*$seconds;

            $trail_expiray_days_to_be_detuct = $plan->trial_expiry;

            if($leftDays < $trail_expiray_days_to_be_detuct )
            {
                date_default_timezone_set("Asia/Karachi");

                //Addding Trial Days
                $currentDate =  time();
                $date = strtotime(date("Y-m-d H:i:s", $currentDate) ." +".$trail_expiray_days_to_be_detuct." day");

                //Gettiing Month and date after adding trial days
                $year_number_of_next_month = date("Y", $date);
                $month_number_of_next_month = date("m", $date);

//                $lastDate_of_next_month = cal_days_in_month(CAL_GREGORIAN, $month_number_of_next_month, $year_number_of_next_month);
                $lastDate_of_next_month = $lastDate_of_next_month = date('t', mktime(0, 0, 0, $month_number_of_next_month, 0, $year_number_of_next_month));
                //Converting timestemps into datetime formate after ading trial days
                $epoch = $date;
                $startDateIfTrialGoToNextMonth =  date('r', $epoch);

                //getting time difference after adding trial days
                $start = date_create($startDateIfTrialGoToNextMonth);
                $end = date_create($year_number_of_next_month.'-'.$month_number_of_next_month.'-'.$lastDate_of_next_month);
                $diff=date_diff($end,$start);

//                 $time_difference_if_trial_goes_next_month = $diff->y.':'.$diff->m.':'.$diff->d.':'.$diff->h.':'.$diff->i.':'.$diff->s;
                $leftDaysOfCurrentMonth = $diff->d;

                $netPrice = (($planPrice/$lastDate_of_next_month ) * $leftDaysOfCurrentMonth ) + $addToNetPaymentToHandleRemainigHours;
                return view('payments._modal_create_new', ['plan' => $plan, 'leftDays' => $leftDaysOfCurrentMonth, 'leftHours' => $hours, 'leftMinutes' => $min, 'project' => $project, 'channel' => $channel, 'channable' => $channable, 'price' => round($netPrice, 2), 'tax_amount' => round($tax_amount, 2), 'trial_period' => $trial_period]);
            }

            $leftDays = $leftDays - $trail_expiray_days_to_be_detuct ;
            $netPrice = (($planPrice/$totalNumberOfDaysInCurrentMonth )*$leftDays ) +$addToNetPaymentToHandleRemainigHours;

        }else{
            $netPrice = $planPrice;
        }

        return view('payments._modal_create_new', ['plan' => $plan, 'leftDays' => $leftDays, 'leftHours' => $hours, 'leftMinutes' => $min, 'project' => $project, 'channel' => $channel, 'channable' => $channable, 'price' => round($netPrice, 2), 'tax_amount' => round($tax_amount, 2), 'trial_period' => $trial_period]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $project, $channel)
    {

        if($request->price){
            $price = $request->price;
        }
        if($request->subscription == 'paypal' ){

            Session::flash('success-message', 'Currently you could not perform the payment using Paypal !');
            return redirect()->back();

//            return view('payments.paypalPaymentForm', ['project' => $project, 'channel' => $channel, 'price' => $price]);
        }
        else if ($request->subscription == 'credit_card'){

            return view('payments.stripePaymentForm', ['project' => $project, 'channel' => $channel, 'price' => $price, 'card_type' => $request->subscription]);
        }
        else if ($request->subscription == 'debit_card'){

            return view('payments.stripePaymentForm', ['project' => $project, 'channel' => $channel, 'price' => $price, 'card_type' => $request->subscription]);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /**
     * Perform payment paypal.
     *
     * @param  project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function paymentExecution(Request $request, $project, $channel)
    {

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item_1 = new Item();
        $item_1->setName('Item 1') /** item name **/
        ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($request->get('amount')); /** unit price **/

        $item_list = new ItemList();
        $item_list->setItems(array($item_1));

        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($request->get('amount'));

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');

        $redirect_urls = new RedirectUrls();

        $redirect_urls->setReturnUrl(route('payment.paymentStatus', [$project, $channel, $request->get('amount')])) /** Specify return URL **/
        ->setCancelUrl(route('payment.paymentStatus', [$project, $channel, $request->get('amount')]));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {

            $payment->create($this->_api_context);

        } catch (\PayPal\Exception\PPConnectionException $ex) {

            if (\Config::get('app.debug')) {

                \Session::put('error', 'Connection timeout');
                return Redirect::route('paywithpaypal');

            } else {

                \Session::put('error', 'Some error occur, sorry for inconvenient');
                return Redirect::route('paywithpaypal');
            }
        }

        foreach ($payment->getLinks() as $link) {

            if ($link->getRel() == 'approval_url') {

                $redirect_url = $link->getHref();
                break;

            }
        }

        /** add payment ID to session **/
        Session::put('paypal_payment_id', $payment->getId());

        if (isset($redirect_url)) {

            /** redirect to paypal **/
            return Redirect::away($redirect_url);

        }

        \Session::put('error', 'Unknown error occurred');
        return Redirect::route('paywithpaypal');
    }


    /**
     * Get payment status paypal.
     *
     * @param  project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function paymentStatus($project, $channel, $amount)
    {
        $payment_id = Session::get('paypal_payment_id');
        Session::forget('paypal_payment_id');

        if(empty(Input::get('PayerID')) || empty(Input::get('token')))
        {

            \Session::put('transErrorMessage','Payment Failed');
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }

        $payment = Payment::get($payment_id, $this->_api_context);

        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));

        $result = $payment->execute($execution, $this->_api_context);
        if ($result->getState() == 'approved') {

            $transaction_id = $result->id;
            $this->_performPayment($project, $channel, $gateway = 'Paypal', $gateway_mode = 'Sandbox', $amount, $tax = 1, $type = 'monthly', $description = '', $transaction_id);

            \Session::flash('transSuccessMessage', 'Transcation succeess ');
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }

        \Session::put('transErrorMessage', 'Payment failed');
        return redirect()->route('channel.dashboard', [$project, $channel]);
    }


    /**
     *
     * Perform payment at paypal.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function paypalPayment(Request $request, $project, $channel)
    {
        try {
            $payload = $request->input('payload', false);
            $nonce = $payload['nonce'];
            $plan = 'k8f6';
            $user = Auth::user();

            $charge = $user->newSubscription('cyrano', $plan)->create($nonce);
            if(isset($charge) && isset($charge->stripe_id)){

                $transaction_id = $charge->stripe_id;
                $price = $request->price;
                $this->_performPayment($project, $channel, $gateway = 'Stripe', $gateway_mode = 'Test', $price, $tax = 1, $type = 'monthly', $description = '', $transaction_id);
                Session::flash('success-message', 'Payment done successfully !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            Session::flash('fail-message', 'Error! Please Try again !');
            return Redirect::back();

        }catch ( \Exception $e ) {

            Session::flash('fail-message', "Error! Please Try again." );
            return Redirect::back();
        }
    }


    /**
     *
     * Perform payment at stripe.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function stripPayment(Request $request, $project, $channel)
    {
        try {

            $user = Auth::user();
            $channel_type = $channel->type;
            $channable = $channel->channable;
            $complexity = $channable->complexity;
            $plan_id = $this->_getPlan($channel_type, $complexity);
            if ($plan_id != null){
                $plan = StripePlan::find($plan_id);
                if(isset($plan)){
                    $plan_id = $plan->plan_id;
                    $plan_name = $plan->nick_name;
                    $trial_period = $plan->trial_expiry;
                    $tax_rate = $plan->net_price - $plan->price;
                    $tax = explode("%",$plan->tax)[0];
                }
            }

            $user = Auth::user();
            $dateNumber = $this->_getUnixTimestemp('dayNumber', '');
            if($dateNumber !== 1 && $plan_id != null && $plan_name != null) {

                if(isset($user->stripe_id) && !empty($user->stripe_id)){
                    $user_id = $user->stripe_id;
                }else{

                    $customer = $this->_createCustomerAtGateway($request);
                    $this->_updateUser($customer->id);
                    $user_id = $customer->id;
                }

                $epochTime = $this->_getUnixTimestemp('timestemp', $trial_period);
                $charge = \Stripe\Subscription::create([
                    'customer' => $user_id,
                    'items' => [[
                        'plan' => $plan_id
                    ]],
                    'billing_cycle_anchor' => $epochTime,
                    'trial_period_days' => $trial_period,
                    'tax_percent' => $tax
                ]);
            }
            else {

                $charge = $user->newSubscription($plan_name, $plan_id)->create($request->stripeToken,['tax_percent' => $tax]);
            }

            if(isset($charge)){

                if(isset($charge->stripe_id)){
                    $transaction_id = $charge->stripe_id;
                }elseif(!empty($charge->id) || isset($charge->id)){
                    $transaction_id = $charge->id;
                    $this->_performSubscription($charge->id, $charge->plan->id, $charge->plan->nickname);
                }

                $price = $request->net_total;
                if($price < 0){ $price = 0;  }
                $this->_performPayment($project, $channel, $gateway = 'Stripe', $gateway_mode = 'Test', $price, $tax_rate, $type = 'monthly', $description = '', $transaction_id);

                $invoice = $user->invoices();

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'subscribe', $invoice[0]->id,  ''));

                Session::flash('success-message', 'Payment done successfully !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            Session::flash('errormessage', 'Error! Please Try again !');
            return redirect()->route('channel.dashboard', [$project, $channel]);

        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }


    /**
     *
     * Unsubscribe a channel.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function unsubscribe(Request $request, $project, $channel)
    {
        try {

            $user = Auth::user();
            $payment = Charge::where('channel_id', $channel->id)->where('user_id', $user->id)->where('gateway', 'Stripe')->get()->last();
            if(isset($payment) && !empty($payment)){

                $channel_type = $channel->type;
                $channable = $channel->channable;
                $complexity = $channable->complexity;
                $plan_id = $this->_getPlan($channel_type, $complexity);
                if ($plan_id != null){
                    $plan = StripePlan::find($plan_id);
                    if(isset($plan)){
                        $plan_name = $plan->nick_name;
                        $trial_expiry = $plan->trial_expiry;
                    }
                }
                $user->subscription($plan_name)->cancel();

                $payment->status =  false;
                $payment->save();

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'un_subscribe', '', ''));

                $request->session()->push('trial_expiry', $trial_expiry);
                Session::flash('success-message', "You have unsubscribe successfully. You can resume it within trail period " );
                return Redirect::back();
            }

            Session::flash('fail-message', "Error! Please Try again." );
            return Redirect::back();
        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }


    /**
     *
     * Resume the subscription for a particular channel.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function resumeSubscription(Request $request, $project, $channel)
    {

        try {

            $channel_type = $channel->type;
            $channable = $channel->channable;
            $complexity = $channable->complexity;
            $plan_id = $this->_getPlan($channel_type, $complexity);
            if ($plan_id != null){
                $plan = StripePlan::find($plan_id);
                if(isset($plan)){
                    $plan_name = $plan->nick_name;
                }
            }

            $user = Auth::user();
            if (!$user->subscription($plan_name)->onGracePeriod()) {
                Session::flash('success-message', 'Your trail period has been expired. So you cannot resume !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            $charge = $user->subscription($plan_name)->resume();
            if(isset($charge)){

                $payment = Charge::where('channel_id', $channel->id)->where('user_id', $user->id)->where('gateway', 'Stripe')->get()->last();
                if(isset($payment) && !empty($payment)) {

                    $payment->status = true;
                    $payment->save();
                }

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'resume', '', ''));

                Session::flash('success-message', 'Plan resumed successfully !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            Session::flash('errormessage', 'Error! Please Try again !');
            return redirect()->route('channel.dashboard', [$project, $channel]);

        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }



    /**
     *
     * Enterprise subscription .
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function enterpriseSubscription(Request $request, $project, $channel)
    {

        $this->_performSubscription('Enterprise', 'Enterprise', 'Enterprise', 0);
        $this->_performPayment($project, $channel, $gateway = 'Enterprise', $gateway_mode = '', 0, 0, $type = '', $description = '', 'Unapproved');

        $user = Auth::user();
        $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterprise-enduser', '', ''));

        $adminRole = Role::where('name', 'superadministrator')->orWhere('name', 'administrator')->get();
        
        foreach ($adminRole as $role){

            $users = $role->user;
            if(!empty($users)) {
                foreach ($users as $user){
    
                    $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterprise-admin', '', ''));
                }
            }
        }

        Session::flash('success-message', 'Request for Enterprise Plan is successful !');
        return redirect()->route('channel.dashboard', [$project, $channel]);

    }
    


    /**
     *
     * Unsubscribe a channel.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function unsubscribEnterprise(Request $request, $project, $channel)
    {
        try {

            $user = Auth::user();
            $payment = Charge::where('channel_id', $channel->id)->where('user_id', $user->id)->where('gateway', 'Enterprise')->get()->last();
            if(isset($payment) && !empty($payment)){
                $payment->status =  false;
                $payment->save();

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterprise_un_subscribe', '', ''));

                Session::flash('success-message', "You have unsubscribe successfully." );
                return Redirect::back();
            }

            Session::flash('fail-message', "Error! Please Try again." );
            return Redirect::back();
        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }



    /**
     *
     * Resume the subscription for a particular channel.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function resumeEnterprise(Request $request, $project, $channel)
    {
        try {

            $user = Auth::user();
            $payment = Charge::where('channel_id', $channel->id)->where('user_id', $user->id)->where('gateway', 'Enterprise')->get()->last();
            if(isset($payment) && !empty($payment)) {

                $payment->status = true;
                $payment->transaction_id = 'Unapproved';
                $payment->save();
            }

            $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterprise_resume', '', ''));

            Session::flash('success-message', 'Request for Enterprise Plan is successful !');
            return Redirect::back();

        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return Redirect::back();
        }
    }



    /**
     *
     * approve a enterprize plan.
     *
     * @param id
     * @return \Illuminate\Http\Response
     */
    public function subscriptionApproval(Request $request, $user, $project, $channel)
    {
        try {

            $general = Charge::where('user_id', $user->id)->where('channel_id', $channel->id)->where('gateway', 'Stripe')->get();
            if(isset($general) && !empty($general)){
                if($general[0]->status ==  1){
                    Session::flash('error', "User has already subscribed to general subscription" );
                    return Redirect::back();
                }
            }

            $payment = Charge::where('user_id', $user->id)->where('channel_id', $channel->id)->where('gateway', 'Enterprise')->get();
            if(isset($payment) && !empty($payment)){
                $payment[0]->status =  true;
                $payment[0]->transaction_id =  'Approved';
                $payment[0]->save();

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterpriseSubApproval', '', ''));

                Session::flash('success', "Enterprise subscription approved successfully." );
                return Redirect::back();
            }

            Session::flash('error', "Error! Please Try again." );
            return Redirect::back();
        }catch ( \Exception $e ) {

            Session::flash('error', $e->getMessage() );
            return Redirect::back();
        }
    }


    /**
     *
     * approve a enterprize plan.
     *
     * @param id
     * @return \Illuminate\Http\Response
     */
    public function subscriptionUnapproved(Request $request, $user, $project, $channel)
    {
        try {
            $payment = Charge::where('user_id', $user->id)->where('channel_id', $channel->id)->where('gateway', 'Enterprise')->get();
            if(isset($payment) && !empty($payment)){
                $payment[0]->status =  false;
                $payment[0]->transaction_id =  'Reject';
                $payment[0]->save();

                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'enterpriseSubUnApproval', '', ''));

                Session::flash('success', "Action is performed successfully." );
                return Redirect::back();
            }

            Session::flash('error', "Error! Please Try again." );
            return Redirect::back();
        }catch ( \Exception $e ) {

            Session::flash('error', $e->getMessage() );
            return Redirect::back();
        }
    }


    /**
     *
     * Get all User's subscriptions.
     *
     * @param project  $project, channel $channel
     * @return \Illuminate\Http\Response
     */
    public function getAllSubscriptions(Request $request, $project, $channel)
    {
        try {

            $user = Auth::user();
            $subscriptions = $user->subscription();

            $channel_type = $channel->type;
            $channable = $channel->channable;
            $complexity = $channable->complexity;
            $tier = $this->_getPlan($channel_type, $complexity);

            $plan_name = $tier[0]->plan_name;
            $price = $request->price;
            if($price < 0){ $price = 0;  }

            $charge = $user->subscription($plan_name)->resume();
            if(isset($charge)){

                $payment = Charge::where('channel_id', $channel->id)->where('user_id', $user->id)->get()->last();
                if(isset($payment) && !empty($payment)) {

                    $payment->status = true;
                    $payment->save();
                }
                $user->notify(new \App\Notifications\Subscription($user, $channel, $project, 'resume', '', ''));

                Session::flash('success-message', 'Plan resumed successfully !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            Session::flash('errormessage', 'Error! Please Try again !');
            return redirect()->route('channel.dashboard', [$project, $channel]);

        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }


    /**
     * Get trial period status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getTrialStatusUsingGatway(Request $request, $project, $channel)
    {
        $channel_type = $channel->type;
        $channable = $channel->channable;
        $complexity = $channable->complexity;
        $tier = $this->_getPlan($channel_type, $complexity);

        $plan_name = $tier[0]->plan_name;
        $user = Auth::user();

        if ($user->subscription($plan_name)->onTrial()) {

            return true;
        }

        return false;
    }


    /**
     * End the trial period for a subscription to a user
     *
     * @param  int  $channel, $project, $complexity
     * @return \Illuminate\Http\Response
     */
    public function endTrialUsingGatway($channel, $project)
    {
        try {

            $channel_type = $channel->type;
            $channable = $channel->channable;
            $complexity = $channable->complexity;
            $tier = $this->_getPlan($channel_type, $complexity);

            $plan_name = $tier[0]->plan_name;
            $user = Auth::user();


            if ($user->subscription($plan_name)->onTrial()) {

                $user->subscription($plan_name)->skipTrial();

                $this->_endTrial($project, $channel, $gateway = 'Stripe', $gateway_mode = 'Test');

                Session::flash('success-message', 'trial period ended successfully !');
                return redirect()->route('channel.dashboard', [$project, $channel]);
            }

            Session::flash('errormessage', 'Error! Please Try again !');
            return redirect()->route('channel.dashboard', [$project, $channel]);

        }catch ( \Exception $e ) {

            Session::flash('errormessage1', $e->getMessage() );
            return redirect()->route('channel.dashboard', [$project, $channel]);
        }
    }


    /**
     * Download subscription invoices for a user
     *
     * @param  int  $channel, $project, $invoice
     * @return \Illuminate\Http\Response
     */
    public function getInvoice($project, $invoice, $user)
    {

        try{
            return $user->downloadInvoice($invoice, [
                'vendor'  => 'Cyrano',
                'product' => $project->name,
            ]);
        }catch (\Exception $e){

            return $e->getMessage();
        }
    }


    //Save payment transaction in database. public method
    public function _performPayment($project, $channel, $gateway, $gateway_mode, $amount, $tax, $type, $description, $transaction_id)
    {
        $user = Auth::user();
        $chargeObj = new Charge();
        $chargeObj->channel_id = $channel->id;
        $chargeObj->user_id = $user->id;
        $chargeObj->gateway = $gateway;
        $chargeObj->gateway_mode = $gateway_mode;
        $chargeObj->amount = number_format((float)$amount, 2, '.', ''); 
        $chargeObj->tax = number_format((float)$tax, 2, '.', ''); 
        $chargeObj->type = $type;
        $chargeObj->description = $description;
        $chargeObj->transaction_id = $transaction_id;
        $chargeObj->status = true;
        $chargeObj->save();
        return;
    }

    //Save payment transaction in database. public method
    public function _performSubscription($stripe_id, $stripe_plan_id, $stripe_plan_name, $quantity=1)
    {

        $user = Auth::user();
        $sub = new Subscriptions();
        $sub->name = $stripe_plan_name;
        $sub->user_id = $user->id;
        $sub->stripe_id = $stripe_id;
        $sub->stripe_plan = $stripe_plan_id;
        $sub->quantity = $quantity;
        $sub->save();

        return;
    }

    //get price according to complexity value. public method
    public function _getTierPrice($channel_type, $complexity)
    {
        $tiers = Tier::where('channel_type', $channel_type)->where('status', false)->get();
        if(isset($tiers) && count($tiers) > 0) {
            $price = $greaterComp = $lessComp = 0;
            foreach ($tiers as $tier){

                if($complexity >= $tier->comp_start && $complexity <= $tier->comp_end) {
                    $price = $tier->prod_plan_id;
                    break;
                }elseif(($complexity > $tier->comp_end) && ($tier->comp_end > $greaterComp)){

                    $price = $tier->prod_plan_id;
                    $greaterComp = $tier->comp_end;

                }elseif($lessComp == 0 && ($complexity < $tier->comp_start) && ($tier->comp_start < $lessComp)){

                    $price = $tier->prod_plan_id;
                    $lessComp = $tier->comp_start;
                }
            }

            return $price;
        }
        return 0;
    }

    //get price according to complexity value. public method
    public function _getPlan($channel_type, $complexity)
    {
        $tiers = Tier::where('channel_type', $channel_type)->where('status', false)->get();
        if(isset($tiers) && count($tiers) > 0) {
            $greaterComp = $lessComp = 0;
            $prod_plan_id = null;
            foreach ($tiers as $tier){

                if($complexity >= $tier->comp_start && $complexity <= $tier->comp_end) {
                    $prod_plan_id = $tier->prod_plan_id;
                    break;
                }elseif(($complexity > $tier->comp_end) && ($tier->comp_end > $greaterComp)){

                    $prod_plan_id = $tier->prod_plan_id;
                    $greaterComp = $tier->comp_end;

                }elseif($lessComp == 0 && ($complexity < $tier->comp_start) && ($tier->comp_start < $lessComp)){

                    $prod_plan_id = $tier->prod_plan_id;
                    $lessComp = $tier->comp_start;
                }
            }

            return $prod_plan_id;
        }
        return null;
    }

    //End trial period, update database
    public function _endTrial($channel_type, $complexity)
    {
        $tiers = Tier::where('channel_type', $channel_type)->where('status', false)->get();

        if(isset($tiers) && count($tiers) > 0) {
            $tier = $tiers->map(function ($tier) use($complexity) {
                if($complexity >= $tier->comp_start && $complexity <= $tier->comp_end){
                    return $tier;
                }
            });

            return $tier;
        }
        return false;
    }

    //End trial period, update database
    public function _createCustomerAtGateway($request)
    {
        try{
            $customer = \Stripe\Customer::create(array(
                'source'   =>$request->stripeToken,
                'description' => "Charge with one time setup"
            ));

            return $customer;
        }catch (\Exception $e){

        }

    }

    //Attach user's payment source
    public function _attachPaymentSource($request)
    {

        try{
            $user = Auth::user();
            $user_id = $user->stripe_id;
            $customer = \Stripe\Customer::retrieve($user_id);
            $card = $customer->sources->create(array("source" => $request->stripeToken));
            return $card;
        }catch (\Exception $e){

        }

    }

    //End trial period, update database
    public function _updateUser($stripe_id)
    {
        $user = Auth::user();
        $user->stripe_id = $stripe_id;
        $user->save();

        return;
    }

    //End trial period, update database
    public function _getUnixTimestemp($type, $trial_period)
    {
        date_default_timezone_set("Asia/Karachi");
        $startSubscriptionDate = new DateTime('now');

        //GETTING CURRENT MONTH AND CURRENT DAY NUMBER
        $currentDate = getdate();
        if($type == 'dayNumber'){

            //GETTING CURRENT DAY NUMBER
            $dateNumber = $currentDate['mday'];
            return $dateNumber;
        }else if ($type == 'timestemp'){

            $dateNumber = $currentDate['mday'];
            $totalNumberOfDaysInCurrentMonth = date("t");
            $leftDays = $totalNumberOfDaysInCurrentMonth - $dateNumber;

            if($leftDays < $trial_period)
            {

                $currentDate =  time();
                $date = strtotime(date("Y-m-d H:i:s", $currentDate) ." +".$trial_period." day");

                $year_number_of_next_month = date("Y", $date);
                $month_number_of_next_month = date("m", $date);
//                $lastDate_of_next_month = cal_days_in_month(CAL_GREGORIAN, $month_number_of_next_month, $year_number_of_next_month);
                $lastDate_of_next_month = $lastDate_of_next_month = date('t', mktime(0, 0, 0, $month_number_of_next_month, 50, $year_number_of_next_month));

                $epochTime = strtotime($year_number_of_next_month.'/'.$month_number_of_next_month.'/'.$lastDate_of_next_month);

            }else{

                $haveCurrentMonth = $currentDate['mon'];
                $haveCurrentHours  = $currentDate['hours'];
                $haveCurrentMinutes  = $currentDate['minutes'];
                $haveCurrentSeconds  = $currentDate['seconds'];

                //GETTING LAST DAY NUMBER OF THE CURRENT MONTH
                $totalNumberOfDaysInCurrentMonth = date("t");

                //GETTING DIFFERICE FROM START_SUBSCRIPTION_DATE TO END SUBSCRIPTION_DATE
                $endSubscriptionDate = '2018-'.$haveCurrentMonth.'-'.$totalNumberOfDaysInCurrentMonth. ' '. '24:00:00 ';
                $date = new DateTime($endSubscriptionDate);
                $diff = date_diff($date,$startSubscriptionDate);
                //GETTING TIME_STAMPS PASS TO BE BILLING_CYCLE_ANCHOR_TAG_IN_STRIPE

                $currentDate =  time(); // get current date
                $epochTime = strtotime(date("Y-m-d H:i:s", $currentDate) . " + " .$diff->y. " year" . " + " .$diff->m. " month" . " + " .$diff->d. " days" . " + " .$diff->h. " hours" . " + " .$diff->i. " minutes" );
            }

            return $epochTime;
        }
    }
}
