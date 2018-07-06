<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/pointos_auth', 'Auth\UserController@pointos');
Route::post('/pointos_auth', 'Auth\UserController@pointos');

Route::get('/pointos_jwt_access', 'Auth\UserController@pointos_jwt_access');
Route::post('/pointos_jwt_access', 'Auth\UserController@pointos_jwt_access');

Route::get('/logout', 'Auth\LoginController@logout')->name('logout')->middleware('auth');

Route::get('/auth/invitation/{code}', 'Auth\RegisterController@invitation')->name('auth.invitation');

Route::get('/auth/resend', 'Auth\RegisterController@resend_verification')->middleware('auth')->name('auth.resend');


//Route::group(['middleware' => ['auth', 'isVerified']], function() {
Route::group(['middleware' => ['auth', 'isVerified']], function() {

    Route::get('/admin/users', function() {
        $users = App\User::with('projects')->get();
        return view('admin.users', ['users' => $users]);
    })
    ->middleware('role:superadministrator|administrator, teia')
    ->name('admin.users');

    Route::get('/settings', 'SettingsController@api_settings')->name('settings');

    Route::get('/notifications/read', function(\Illuminate\Http\Request $request) {
        $time = \Carbon\Carbon::createFromTimestamp($request->get('time'));

        $request->user()
            ->unreadNotifications()
//            ->where('created_at', '<', $time)
            ->update(['read_at' => \Carbon\Carbon::now()]);

        return redirect()->back();
    })
    ->name('notifications.mark_as_read');


    /**
     * Laratrust Role and permissions
     */
    Route::get('/user/{user}/profile', 'Admin\UsersController@getUserProfile')
        ->name('user.getProfile');

    Route::post('/user/{user}', 'Admin\UsersController@updateUserProfile')
        ->name('user.updateProfile');
        Route::group(['middleware' => ['role:superadministrator|administrator']], function() {
            Route::resource('user', 'Admin\UsersController') ;
            Route::resource('permission', 'Admin\PermissionController');
            Route::resource('roles', 'Admin\RolesController');
            Route::get('subscribed', 'Admin\UsersController@plan_subscribed');
        });
            
    
    /**
     * FACEBOOK OAUTH
     */
//    Route::get('/auth/facebook', 'Channels\Facebook\Channel@oauth_login');
//    Route::get('/facebook/callback', 'Channels\Facebook\Channel@oauth_callback');

    Route::get('/', 'ProjectController@index')->name('projects.index');

    Route::get('/projects', 'ProjectController@get_create')
        ->name('projects.create');

    Route::post('/projects', 'ProjectController@post_create')
        ->name('projects.create');

    Route::get('/project/{project}', 'ProjectController@show')
        ->middleware('project_role:user')
        ->name('project.dashboard');

    Route::post('/project/{project}/channel/{type}', 'ProjectController@add_channel')
        ->middleware('project_role:admin')
        ->name('channel.add');

    Route::post('/project/{project}/channel', 'ProjectController@add_channel')
        ->middleware('project_role:admin');

    /**
     * ROLES
     */
    Route::get('/project/{project}/roles', 'ProjectController@get_roles')
        ->middleware('project_role:admin')
        ->name('project.roles');

    Route::post('/project/{project}/roles', 'ProjectController@post_roles')
        ->middleware('project_role:admin')
        ->name('project.roles');


    /**
     * INVITATION SYSTEM
     */
    Route::get('/project/{project}/invite', 'ProjectController@get_invite')
        ->middleware('project_role:admin')
        ->name('project.invite');

    Route::post('/project/{project}/invite', 'ProjectController@post_invite')
        ->middleware('project_role:admin')
        ->name('project.invite');

    Route::get('/project/{project}/invitations', 'ProjectController@get_invitations')
        ->middleware('project_role:admin')
        ->name('project.invitations');

    Route::get('/project/{project}/invite/{code}', 'ProjectController@delete_invite')
        ->middleware('project_role:admin')
        ->name('project.invite.delete');


    /**
     * REWARDS
     */
    Route::get('/project/{project}/rewards', function() {
        return back()->with('message', ['type' => 'warning', 'text' => 'Disabled.']);
    })
    ->middleware('project_role:user')
    ->name('project.rewards');

    /**
     * CRM
     */
    Route::group(['prefix' => '/project/{project}/crm', 'middleware' => ['project_role:user']], function() {

        Route::get('/dashboard', function() {
            return back()->with('message', ['type' => 'warning', 'text' => 'Disabled.']);
        })->name('crm.dashboard');

        Route::get('/users', function() {
            return back()->with('message', ['type' => 'warning', 'text' => 'Disabled.']);
        })->name('crm.users');


    });




    /**
     * CHANNEL
     */
    Route::group(['prefix' => '/project/{project}/channel/{channel}', 'middleware' => ['project_role:user']], function() {
//    Route::group(['prefix' => '/project/{project}/channel/{channel}', 'middleware' => ['role:superadministrator|administrator']], function() {

        /**
         * CONFIGURE CHANNEL
         */
        Route::get('/configure', 'ChannelController@get_configure')
            ->name('channel.configure');

        Route::get('/configure/after', 'ChannelController@after_configure')
            ->name('channel.after_configure');

        Route::post('/configure', 'ChannelController@post_configure')
            ->name('channel.configure');

        /**
         * CHANNEL DASHBOARD
         */
        Route::get('/', 'ChannelController@dashboard')
            ->name('channel.dashboard');

        Route::get('/leaderboard', 'ChannelController@leaderboard')
            ->name('channel.leaderboard');


        /**
         * CHANNEL USER PROFILE
         */
        Route::group(['prefix' => '/leaderboard'], function() {

        });

        /**
         * CHANNEL SETTINGS
         */
        Route::group(['prefix' => '/settings'], function() {

        });

        /**
         * CHANNEL CONTEST
         */
        Route::group(['prefix' => '/contests'], function() {

        });


        /**
         * ANALYTICS
         */
        Route::group(['prefix' => '/analytics'], function() {

        });


         /**
         * Payments Routes
         */


        // Routes for get Trial status -> api call at payment gateway (stripe)
        Route::get('/payment/trial', 'PaymentController@getTrialStatusUsingGatway')
            ->name('payment.getTrialStatusUsingGatway');

        // Routes for get Trial status -> api call at payment gateway (stripe)
        Route::get('/payment/get-subscriptions', 'PaymentController@getAllSubscriptions')
            ->name('payment.getAllSubscriptions');

        // Routes for approve enterprize plan
        Route::post('/payment/approve/enterpirze/{id}', 'PaymentController@endTrialPeriodGateway')
        ->name('payment.approve');
            
        // Routes for end Trial -> api call at payment gateway (stripe)
        Route::post('/payment/trial/end', 'PaymentController@endTrialPeriodGateway')
            ->name('payment.endTrialPeriodGateway');

        // Routes for paypal payment
        Route::get('payment/type','PaymentController@paymentExecution')->name('payment.paymentExecution');

        // Routes for paypal payment status
        Route::get('status/{amount}','PaymentController@paymentStatus')->name('payment.paymentStatus');

        // Routes for Credit card (stripe) payment gateway
        Route::post('payment/strip','PaymentController@stripPayment')->name('payment.stripPayment');

        // Routes for Credit card (stripe) payment gateway
        Route::post('payment/paypal','PaymentController@paypalPayment')->name('payment.paypalPayment');

        // Routes for Unsubscribe channel, alter status fro payment table to false
        Route::post('payment/unsubscribe','PaymentController@unsubscribe')->name('payment.unsubscribe');

        // Routes for resume subscription for a channel
        Route::get('payment/resume','PaymentController@resumeSubscription')->name('payment.resumeSubscription');
         
        // Routes for enterprise subscription
        Route::get('payment/enterprise','PaymentController@enterpriseSubscription')->name('payment.enterprise');
        
        // Routes for enterprise un-subscription
        Route::post('payment/unsubscribe-enterprise','PaymentController@unsubscribEnterprise')->name('payment.unsubscribeEnterprise');
        
        // Routes for enterprise resume subscription
        Route::get('payment/resume-enterprise','PaymentController@resumeEnterprise')->name('payment.resumeEnterprise');

        // Routes for payment rest module
        Route::resource('payment', 'PaymentController');

    });

    /**
     * Tiers RestfulRoutes
     */
    Route::group(['prefix' => '/channel/{type}', 'middleware' => ['role:superadministrator|administrator, project_role:admin']], function() {

        Route::resource('tier', 'TierController');
    });
    
    /**
     * Enterprize approval  
     */
    Route::group(['prefix' => '/user/{user}/project/{project}/channel/{channel}', 'middleware' => ['role:superadministrator|administrator, project_role:admin']], function() {
        // Routes for enterprise subscription approve
        Route::get('payment/subscription-approval','PaymentController@subscriptionApproval')->name('payment.subscriptionApproval');
    
        // Routes for enterprise subscription un-approve
        Route::get('payment/subscription-un-approved','PaymentController@subscriptionUnapproved')->name('payment.subscriptionUnapproved');
    });

    /**
     * Plan Restful Routes
     */
    Route::group(['middleware' => 'role:superadministrator|administrator, project_role:admin'], function()
    {
        Route::resource('plan', 'StripePlanController');
    });

});

// Get subscription invoice and download pdf. No authentication middleware should be otherwise user will be redirect to /login
Route::get('/project/{project}/invoice/{invoice}/user/{user}', 'PaymentController@getInvoice')
    ->name('payment.subscription-invoice');

//Resume subscription
Route::post(
    'stripe/webhook/resume',
    'WebhookController@handleCustomerSubscriptionUpdated'
);
Route::post(
    'stripe/webhook/endtrial',
    'WebhookController@handleCustomerSubscriptionTrial_will_end'
);

Route::get('/address/enrich', function(Illuminate\Http\Request $request) {
    return geocode($request->input('value', null));
});

Route::get('/phone/format', function(Illuminate\Http\Request $request) {
    return formatPhone(
        $request->input('phone', null),
        $request->input('dialCode', null),
        $request->input('country', 'en')
    );
});