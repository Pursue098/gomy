<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use App\Notifications\UserRegistered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use App\User;
use Input;
use Validator, DB, Hash, Mail;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class UserController extends Controller
{

    use RegistersUsers, VerifiesUsers;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    private function getcurl($url='', $params, $headers){
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
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
     * call back url for pointos.
     *
     * @return \Illuminate\Http\Response
     */
    public function pointos_jwt_access(Request $request)
    {
        $data = $request->all(); 
        return json_encode($data);

    }
    /**
     * call back url for pointos.
     *
     * @return \Illuminate\Http\Response
     */
    public function pointos(Request $request)
    {
        
        $data = $request->all();   
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

        $poynt_response_token = $this->getcurl($url,$params, $headers);

        $user = User::find($request->context);
        if(is_object($user)) {
            $user->poynt_response = json_encode($data);
            $user->self_signed_token = $selfSignedToken;
            $user->poynt_response_token = $poynt_response_token;
            $user->save();
        }
        return $poynt_response_token;
    
    }

    private function getSelfSignedToken(){ 
        $privateKeyFile = storage_path().'/private.ppk';
        $signer = new Sha256();
        $token = (new Builder())->setIssuer(env("ISS","urn:aid:675f2671-06ce-4989-9766-c08c0d0dfe02")) // Configures the issuer (iss claim)
                                ->setAudience(env('AUD','https://services.poynt.net')) // Configures the audience (aud claim)
                                ->setId(env('JTI','e0d392ba-b5c8-4cee-b6e3-a9ce668307e8'), true) // Configures the id (jti claim), replicating as a header item
                                ->setSubject(env("ISS","urn:aid:675f2671-06ce-4989-9766-c08c0d0dfe02"))
                                ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                                ->setNotBefore(time() + 10) // Configures the time that the token can be used (nbf claim)
                                ->setExpiration(time() + 36900) // Configures the expiration time of the token (exp claim)
                                //->set('uid', 'e0d392ba-b5c8-4cee-b6e3-a9ce668307e8') // Configures a new claim, called "uid"
                                ->sign($signer, file_get_contents($privateKeyFile)) // creates a signature using "testing" as key
                                ->getToken(); // Retrieves the generated token

        return $token;
    }

    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $user = User::find($id);
        return view('auth.profile', ['user' => $user]);

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

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required|numeric',
            'company' => 'required',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user= User::find($id);
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->company = $request->company;
        $user->save();

        $user->notify(new \App\Notifications\ProfileUpdate($user));
        \Session::flash('successMessage', 'Your profile has been updated successfully !');
        return redirect()->route('projects.index');
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
}
