<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\User;
use App\Project;
use App\Role;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use GuzzleHttp;

use DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use App\Notifications\UserRegistered;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Symfony\Component\HttpFoundation\Response;
 
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class UserController extends Controller
{

    use SendsPasswordResetEmails;
    protected function ValidationResponse( array $errors)
    {
        return response()->json([
            'error' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }
     /**
     * Check the login credentials and get the access token
     * @return \Illuminate\Http\Response
     */

     /**
     * @SWG\Post(
     *   path="/api/v1/login",
     *   description= "Check the login credentials and get the access token",
     *   summary="Check the login credentials and get the access token",
     *   operationId="login",
     * @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *  @SWG\Parameter(
     *          name="password",
     *          description="User password",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function login(Request $request)
    {

        /**
     * Get a validator for an incoming login request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    $valid = validator($request->only( 'email', 'password' ), [
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:6',
    ]);

    if ($valid->fails()) {
       return $this->ValidationResponse($valid->errors()->all());
    }
    
    $user = User::where('email', $request->email)->first();
    
    if(!is_object($user)){
        return $this->ValidationResponse(array('Email is not registered!'));
    }
    
    if($user->verified!=1) {
        return $this->ValidationResponse(array('Email not verified yet!'));
    }
    
    $client = DB::table('oauth_clients')->where('password_client', 1)->first();
    // Is this $request the same request? I mean Request $request? Then wouldn't it mess the other $request stuff? Also how did you pass it on the $request in $proxy? Wouldn't Request::create() just create a new thing?
    
    $authParams  = [
        'grant_type'    => 'password',
        'client_id'     => $client->id,
        'client_secret' => $client->secret,
        'username'      => $request->email,
        'password'      => $request->password,
        'scope'         => ''
     ];
     $returnData = $data =  array();

       if(!$user->hasRole('Merchant')){
        return $this->ValidationResponse(array('You are not a merchant user!'));
       } 
       
        $http = new GuzzleHttp\Client;
        try {
            $response = $http->request('post',
            url('/') . '/oauth/token',
                ['form_params' => $authParams]
            );
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
             return $this->ValidationResponse(array("Email o password errate. Riprova"));
        }

        $data['user_id'] = $user->id;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['phone_number'] = $user->phone_number;
        $data['address'] = $user->address;
        $data['company'] = $user->company;
        $data['vat_number'] = $user->vat_number;
        $data['sale_points'] = $user->sale_points;
        $data['company_phone'] = $user->company_phone;
        $data['company_address'] = $user->company_address;
        $data['note'] = $user->note;
        $data['skip_tutorial'] = $user->skip_tutorial; 
        $data['business_id'] = $user->business_id;
        $data['device_id'] = $user->device_id;

        $returnData  = json_decode((string) $response->getBody(), true);

        unset($data['password']);
        $returnData['user'] = $data;
        return response()->json([
            'data' => $returnData,
            'status' => 200
        ]);

    }

    /**
     * Create a new user and get the access token
     * @return \Illuminate\Http\Response
     */

     /**
     * @SWG\Post(
     *   path="/api/v1/register",
     *   description= "Create a new user and get the access token",
     *   summary="Create a new user and get the access token",
     *   operationId="signup",
     *   consumes={"multipart/form-data"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *          name="name",
     *          description="User name",
     *          required=false,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="password",
     *          description="User password",
     *          required=true,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="company",
     *          description="User company",
     *          required=true,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="address",
     *          description="Company address",
     *          required=true,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="vat_number",
     *          description="Company vat_number",
     *          required=false,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="sale_points",
     *          description="How many sale points?",
     *          required=false,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="device_id",
     *          description="deviceId of poynt os app",
     *          required=false,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Parameter(
     *          name="business_id",
     *          description="businessId of poynt os app",
     *          required=false,
     *          type="string",
     *          in="formData"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

    public function register(Request $request)
    {
        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $request
         * @return \Illuminate\Contracts\Validation\Validator
         */
        $valid = validator($request->only('email', 'password','company','company_address', 'business_id', 'device_id' ), [
            'email' => 'required|string|email|max:255 ',
            'password' => 'required|string|min:6',
            'company' => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'vat_number' => 'string|max:255',
            'sale_points' => 'string|max:255',
            'business_id' => 'uuid', // |exists:ch_poynt,business_id
            'device_id'   => 'tid'

        ]);

        if ($valid->fails()) {
            return $this->ValidationResponse($valid->errors()->all());
        }
        
        $user = User::where('email', $request->email)->first();
        
        if(is_object($user)){
            return $this->ValidationResponse(array('Email is already registered!'));
        }
        
        if(isset($request->device_id) && !empty($request->device_id)) { 
            $userWithDevice = User::where('device_id', $request->device_id)->first();
            
            if(is_object($userWithDevice)) {
                return $this->ValidationResponse(array('This device is already associated with another merchant')); 
            }
        }

        $data = request()->only('email','name','phone_number','password','company','address','company_address', 'vat_number','sale_points', 'business_id', 'device_id' );
        
        $user = User::create([
            'name' => isset($data['name']) ? $data['name'] : $data['company'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'address' => $data['address'],
            'company' => $data['company'],
            'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : 000,
            'vat_number' => isset($data['vat_number']) ? $data['vat_number'] : '',
            'sale_points' => isset($data['sale_points']) ? $data['sale_points'] : '',
            'business_id' => isset($data['business_id']) ? $data['business_id'] : '',
            'device_id' => isset($data['device_id']) ? $data['device_id'] : '',
            'company_address' => isset($data['company_address']) ? $data['company_address'] : ''
        ]);

        $merchant = Role::where('name','Merchant')->first();
        //Merchant user
        $user->attachRole($merchant);

        $data['user_id'] = $user->id;
        $data['skip_tutorial'] = $user->skip_tutorial; 

        event(new Registered($user));
        UserVerification::generate($user);
        $user->notify(new \App\Notifications\UserRegistered($user));
        event(new \Jrean\UserVerification\Events\VerificationEmailSent($user));
        // And created user until here.

        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        $authParams  = [
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $data['email'],
            'password'      => $data['password'],
            'scope'         => null,
         ];

        $http = new GuzzleHttp\Client;
        try {
            $response = $http->request('post',
            url('/') . '/oauth/token',
                ['form_params' => $authParams]
            );
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            return $this->ValidationResponse(['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }

        $returnData = array();
        $returnData  = json_decode((string) $response->getBody(), true);
        unset($data['password']);
        $returnData['user'] = $data;
        return response()->json([
            'data' => $returnData,
            'status' => 200
        ]);

    }

    /**
     * Send a reset link to the given user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

     /**
     * @SWG\Post(
     *   path="/api/v1/forget_password",
     *   description= "Check the email and Send a reset link to the given user ",
     *   summary="Check the email and Send a reset link to the given user  ",
     *   operationId="forget_password",
     * @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

    public function forgetpass(Request $request)
    {
        $valid = validator($request->only('email'), [
            'email' => 'required|string|email|max:255'
        ]);

        if ($valid->fails()) {
            return $this->ValidationResponse($valid->errors()->all());
        }
        

        $user = User::where('email', $request->email)->first();
        
        if(!is_object($user)){
            return $this->ValidationResponse(array('Mail non trovata!'));
        } 
        
        $this->sendResetLinkEmail($request);
        return response()->json([
            'data' => 'We have sent an email with password reset link. Check email',
            'status' => Response::HTTP_OK
        ]);
    }
    
    /**
     * Verify user exist or not
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

     /**
     * @SWG\Post(
     *   path="/api/v1/user/verify",
     *   description= "User verification ",
     *   summary="User verification ",
     *   operationId="verifyUser",
     * @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

    public function verify(Request $request)
    {
        $valid = validator($request->only('email'), [
            'email' => 'required|string|email|max:255'
        ]);

        if ($valid->fails()) {
            return $this->ValidationResponse($valid->errors()->all());
        }
        

        $user = User::where('email', $request->email)->first();
        
        if(!is_object($user)){
            return response()->json([
    			'data' => false,
    			'status' => Response::HTTP_OK
    		]);
        }
        
        if(!$user->hasRole('Merchant')){
            return response()->json([
    			'data' => false,
    			'status' => Response::HTTP_OK
    		]);
        }
        
        return response()->json([
            'data' => true,
            'status' => Response::HTTP_OK
        ]);
		  
         
    }

    /**
     * Update user profile
     * @return \Illuminate\Http\Response
     */

     /**
     * @SWG\Put(
     *   path="/api/v1/update",
     *   description= "Update a user profile",
     *   summary="Update a user profile",
     *   operationId="updateUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *          name="user_id",
     *          description="User Id",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="company",
     *          description="User company",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="address",
     *          description="Company address",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

    public function update(Request $request)
    {
    /**
     * Get a validator for an incoming update request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    $valid = validator($request->only('user_id','company','address'), [
        'user_id' => 'required',
        'company' => 'required|string|max:255',
        'address' => 'required|string|max:255'

    ]);

    if ($valid->fails()) {
        return $this->ValidationResponse($valid->errors()->all());
    }

    $data = request()->only('user_id', 'name','phone_number','company_phone','company','address','company_address','vat_number','sale_points','note');

    $user = User::find($request->user_id);

    if(isset($request->name))
        $user->name = $request->name;

    if(isset($request->address))
        $user->address = $request->address;

    if(isset($request->company))
        $user->company = $request->company;

    if(isset($request->phone_number))
        $user->phone_number = $request->phone_number;

    if(isset($request->vat_number))
        $user->vat_number = $request->vat_number;

    if(isset($request->sale_points))
        $user->sale_points = $request->sale_points;

    if(isset($request->company_phone))
        $user->company_phone = $request->company_phone;

    if(isset($request->company_address))
        $user->company_address = $request->company_address;

    if(isset($request->note))
        $user->note = $request->note;

    $user->save();

    $data['user_id'] = $user->id;
    $data['name'] = $user->name;
    $data['email'] = $user->email;
    $data['phone_number'] = $user->phone_number;
    $data['address'] = $user->address;
    $data['company'] = $user->company;
    $data['vat_number'] = $user->vat_number;
    $data['sale_points'] = $user->sale_points;
    $data['company_phone'] = $user->company_phone;
    $data['company_address'] = $user->company_address;
    $data['note'] = $user->note;
    $data['skip_tutorial'] = $user->skip_tutorial;

    $returnData = array();
    unset($data['password']);
    $returnData['user'] = $data;
    return response()->json([
        'data' => $returnData,
        'status' => Response::HTTP_OK
    ]);
 }


    /**
     * Update user profile for skip tutorial or not
     * @return \Illuminate\Http\Response
     */

     /**
     * @SWG\Put(
     *   path="/api/v1/skip_tutorial",
     *   description= "Update user profile for skip tutorial or not",
     *   summary="Update user profile for skip tutorial or not",
     *   operationId="skip_tutorial",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *          name="user_id",
     *          description="User Id",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Parameter(
     *          name="skip_tutorial ",
     *          description="User skip_tutorial",
     *          required=true,
     *          type="string",
     *          in="path"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

    public function skip_tutorial(Request $request)
    {
    /**
     * Get a validator for an incoming update request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    $valid = validator($request->only('user_id','skip_tutorial'), [
        'user_id' => 'required',
        'skip_tutorial' => 'required'
    ]);

    if ($valid->fails()) {
        return $this->ValidationResponse($valid->errors()->all());
    }

    $data = request()->only('user_id', 'skip_tutorial' );

    $user = User::find($request->user_id);

    if(isset($request->skip_tutorial))
        $user->skip_tutorial = $request->skip_tutorial;

    $user->save();

    $returnData = array();
    $returnData['user'] = $data;
    return response()->json([
        'data' => $returnData,
        'status' => Response::HTTP_OK
    ]);
 }

 /**
  * @SWG\Delete(
  *   path="/api/v1/delete",
  *   description= "Hard delete user profile with all relational data",
  *   summary="Hard delete user profile with all relational data",
  *   operationId="deleteUser",
  *   produces={"application/json"},
  *   @SWG\Parameter(
  *          name="email",
  *          description="User email for cross verify",
  *          required=true,
  *          type="string",
  *          in="path"
  *   ),
  *   @SWG\Response(response=200, description="successful operation"),
  *   @SWG\Response(response=406, description="not acceptable"),
  *   @SWG\Response(response=500, description="internal server error")
  * )
  *
  *
  */
 
 public function delete(Request $request)
 {
     /**
      * Get a validator for an incoming update request.
      *
      * @param  array  $request
      * @return \Illuminate\Contracts\Validation\Validator
      */
     $valid = validator($request->only('email'), [
         'email' => 'required|string|email|max:255' 
     ]);
 
     if ($valid->fails()) {
         return $this->ValidationResponse($valid->errors()->all());
     }
 
     $user = $request->user();
     
     if(!is_object($user)) {
         return $this->ValidationResponse(['Not a valid user!']);
     }
     
     $data = request()->only('email' );
     
     if($user->email != $data['email'] ){
         return $this->ValidationResponse(['Token and email not matched!']);
     }
     
     $returnData = $channels = $loyalties = array();

     $projects = DB::table('project_user')->where('user_id', $user->id)->get() ; 

     foreach($projects as $p){
        $channels  = DB::table('channel_project')->where('project_id', $p->project_id)->get()->toArray() ; 
        DB::table('loyalties')->where('project_id', $p->project_id)->delete(); 
     }
     
      
     foreach($channels as $c){ 
          DB::table('channel_project')->where('channel_id', $c->channel_id)->delete();
          DB::table('channels')->where('id', $c->channel_id)->delete(); 
     }
     
     foreach($projects as $p){ 
         DB::table('project_user')->where('project_id', $p->project_id)->delete();
         DB::table('projects')->where('id', $p->project_id)->delete();
          
     }
     DB::table('ch_poynt')->where('business_id', $user->business_id)->delete();
     DB::table('ch_poynt_device')->where('id', $user->device_id)->delete();
     DB::table('role_user')->where('user_id', $user->id)->delete();
     DB::table('users')->where('id', $user->id)->delete();
      
    /*/* */
     $returnData['msg'] = 'User hard deleted from db with all relations';
     $returnData['projects'] = $projects;
     $returnData['channels'] = $channels;
     $returnData['loyalties'] = $loyalties; 
     
     $data['id'] = $user->id;
     $data['business_id'] = $user->business_id;
     $data['device_id'] = $user->device_id;
     
     $returnData['user'] = $data;
     return response()->json([
         'data' => $returnData,
         'status' => Response::HTTP_OK
     ]);
 }
 

    /**
     * Get transactions detail
     * @return \Illuminate\Http\Response
     */

     /**
     * @SWG\Get(
     *   path="/api/v1/transactions",
     *   description= "Get all transacctions detail or enter send transaction id in url for single transaction /api/v1/transactions/{transaction_id}",
     *   summary="Get all transacctions detail",
     *   operationId="skip_tutorial",
     *   produces={"application/json"},
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     *
     */

 public function transactions(Request $request, $id='')
 {
    $user = $request->user();
      
    if(!is_object($user)) {
        return $this->ValidationResponse(['Not a valid user!']);
    }

    $token = (array)json_decode($user->poynt_response_token);
    $businessDetail = (array)json_decode($user->poynt_response) ;
   
    $url = env("TRANSACTION_URI","https://services.poynt.net/businesses/");
    
    $business_id = '';

    if(isset( $businessDetail['businessId']))
        $business_id = $businessDetail['businessId'];
    else
        return $this->ValidationResponse(['Business id is not valid!']);

        
    if($id)
        $url .= $business_id.'/transactions/'.$id; 
    else
        $url .= $business_id.'/transactions';

    $params = $headers = $returnData = array();
    \Log::useDailyFiles(storage_path().'/logs/token.log');

    if(!isset($token['accessToken'])) {
        
        \Log::info('accessToken for pointos is not available and generting new one' );
        $token = (array)json_decode($this->poynt_response_token($user));
        \Log::info($token);

        if(!isset($token['accessToken'])) {
            return $this->ValidationResponse($token);
        }
    } 
    $returnData = array();

    if(isset($token['accessToken'])) {
        $returnData = '';
        $headers[] =  "Authorization: Bearer ". $token['accessToken'];
 
        $returnData = json_decode($this->getcurl($url, $params ,$headers ),true); 
        
    }

    // pointos access data token is expired or invalid then we need to regenerate dynamically for request
    if( isset($returnData['httpStatus']) ) {
        if($returnData['httpStatus'] != 200) {
                    $tokenNew = array();
                    \Log::info('accessToken is expired or invalid and generting new one'); 
                    $tokenNew = (array)json_decode($this->getRefreshedToken($user));
                    \Log::info(json_encode( $token));
                
                    usleep(2000);
                    if( isset($tokenNew['accessToken'])) {
                        $client = new GuzzleHttp\Client;

                        try {
                            usleep(20000);
                            $response = $client->get($url, [
                                'headers' => [
                                    'Authorization' => 'Bearer '.$tokenNew['accessToken'],
                                ]
                            ]);

                            $returnData = json_decode((string) $response->getBody(), true);
                            
                        

                        } catch (GuzzleHttp\Exception\BadResponseException $e) {
                            return response()->json([
                                'data' => true,
                                'status' => Response::HTTP_OK
                            ]);
                        }

                       // $headers[] =  "Authorization: Bearer ". $tokenNew['accessToken'] ; 
                       // $returnData = json_decode($this->getcurl($url, $params ,$headers ),true);  
                        \Log::info('new data');
                        \Log::info(json_encode($returnData));
                    } 
                  
        }
    }
 

    $transaction = Transaction::create([
        'transaction_id' =>  $id,
        'data' => json_encode($returnData)
        ]);
    
    return response()->json([
        'data' => true,
        'status' => Response::HTTP_OK
    ]);
 }

 private function getcurl($url='', $params, $headers, $request_type='GET'){
        
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30000,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $request_type,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => $headers, 
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response ;
    }
}
   
/**
     * Refresh token in case of expired
     *
     **/
    private function getRefreshedToken($user){ 
        if(!is_object($user)) {
            return $this->ValidationResponse('Not a valid user!');
        }
        $data = (array)json_decode($user->poynt_response_token,true);   
        //$selfSignedToken = $this->getSelfSignedToken();

        //getting pointos jwt token access for transactions
        $params = $headers = array();
        $params['grant_type'] = 'REFRESH_TOKEN';
        $params['refreshToken'] = $data['refreshToken'] ;
        
 
        // Set here requred headers
        //$headers[] =  "accept: */*";
        $headers[] =  "accept: application/json";
        $headers[] =  "content-type: application/x-www-form-urlencoded; charset=UTF-8"; 
        //$headers[] =  "Authorization: Bearer ".$selfSignedToken;
        $url = env("TOKEN_URL","https://services.poynt.net/token");

        $poynt_response_token = $this->getcurl($url,$params, $headers, 'POST');
        $user = User::find($user->id);
        if(is_object($user)) { 
            //$user->self_signed_token = $selfSignedToken;
            $user->poynt_response_token = $poynt_response_token;
            $user->save();
        }

        return $poynt_response_token;
        
    }

    /**
     * selfsigned token in case of expired
     *
     **/
    private function getSelfSignedToken(){ 
        $privateKeyFile = storage_path().'/private.ppk';
        $signer = new Sha256();
        $token = (new Builder())->setIssuer(env("ISS","urn:aid:675f2671-06ce-4989-9766-c08c0d0dfe02")) // Configures the issuer (iss claim)
                                ->setAudience(env('AUD','https://services.poynt.net')) // Configures the audience (aud claim)
                                ->setId(env('JTI','e0d392ba-b5c8-4cee-b6e3-a9ce668307e8'), true) // Configures the id (jti claim), replicating as a header item
                                ->setSubject(env("ISS","urn:aid:675f2671-06ce-4989-9766-c08c0d0dfe02"))
                                ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                                ->setNotBefore(time() + 10) // Configures the time that the token can be used (nbf claim)
                                ->setExpiration(time() + 360000) // Configures the expiration time of the token (exp claim)
                                //->set('uid', 'e0d392ba-b5c8-4cee-b6e3-a9ce668307e8') // Configures a new claim, called "uid"
                                ->sign($signer, file_get_contents($privateKeyFile)) // creates a signature using "testing" as key
                                ->getToken(); // Retrieves the generated token

        return $token;
    }

     /**
     * token regenration in case of expired
     *
     * @return \Illuminate\Http\Response
     */
    private function poynt_response_token($user )
    {
        if(!is_object($user)) {
            return $this->ValidationResponse('Not a valid user!');
        }
        $data = json_decode($user->poynt_response,true);   
        $selfSignedToken = $this->getSelfSignedToken();

        //getting pointos jwt token access for transactions
        $params = $headers = array();
        $params['grant_type'] = 'authorization_code';
        $params['redirect_uri'] =  env("REDIRECT_URI_JWT",'https://cyrano-dev.teia.company/pointos_jwt_access');
        $params['code'] = $data['code'];
        $params['client_id'] = env("CLIENT_ID","urn:aid:675f2671-06ce-4989-9766-c08c0d0dfe02") ;
        $params['businessId'] = $data['businessId'];
 
        // Set here requred headers
        //$headers[] =  "accept: */*";
        $headers[] =  "accept: application/json";
        $headers[] =  "content-type: application/x-www-form-urlencoded"; 
        $headers[] =  "Authorization: Bearer ".$selfSignedToken;
        $url = env("TOKEN_URL","https://services.poynt.net/token");

        $poynt_response_token = $this->getcurl($url,$params, $headers, 'POST');
        $user = User::find($user->id);
        if(is_object($user)) { 
            $user->self_signed_token = $selfSignedToken;
            $user->poynt_response_token = $poynt_response_token;
            $user->save();
        }

        return $poynt_response_token;
    
    }


}
