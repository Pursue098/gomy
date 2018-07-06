<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;
use App\Notifications\UserRegistered;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, VerifiesUsers;


    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['getVerification', 'getVerificationError', 'invitation', 'resend_verification']]);
    }

    public function invitation($code) {
        $redirect = '/register';

        if (\Auth::check()) {
            $redirect = '/';
        }

        $invite = \App\Invite::where('code', $code)->first();

        if (! $invite) {
            return redirect($redirect)->with('message', ['type' => 'danger', 'text' => 'Invitation not found.']);
        }

        if ($invite->status == 'successful') {
            return redirect($redirect)->with('message', ['type' => 'danger', 'text' => 'Invitation already accepted.']);
        }

        if ($invite->status == 'canceled') {
            return redirect($redirect)->with('message', ['type' => 'danger', 'text' => 'Invitation revocated.']);
        }

        if ($invite->expired() || $invite->status == 'expired') {
            return redirect($redirect)->with('message', ['type' => 'danger', 'text' => 'Invitation is expired.']);
        }

        if (\Auth::check()) {
            if ($invite->email == \Auth::user()->email) {
                $invite->project->users()->syncWithoutDetaching([\Auth::user()->id => ['role' => $invite->role]]);

                $invite->status = 'successful';
                $invite->save();

                $invite->author->notify(new \App\Notifications\InviteAccepted(\Auth::user(), $invite));

                return redirect($redirect)->with('message', ['type' => 'success', 'text' => 'Invitation accepted.']);
            } else {
                return redirect($redirect)->with('message', ['type' => 'warning', 'text' => 'Invitation rejected: you\'re logged as ' . \Auth::user()->name . ' but the invitation is for ' . $invite->email]);
            }
        }

        return view('auth.register', ['invite' => $invite, 'refer' => User::find($invite->user_id)]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'          => 'required|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'password'      => 'required|min:6|confirmed',
            'invite'        => 'nullable|alpha_num|size:32|exists:invites,code',
            'phone_number'  => 'required|alpha_num',
            'company'       => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone_number' => $data['phone_number'],
            'company' => $data['company']
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function attachRole($user)
    {

        $role = Role::where('name', 'user')->get();
        if(isset($role) && count($role) > 0){
            $user->attachRole($role[0]->id);
        }

        return;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->attachRole($user);

        event(new Registered($user));

        $this->guard()->login($user);

        if ($request->has('invite')) {

            $invite = \App\Invite::where('code', $request->get('invite'))->first();
            if ($invite != null && $invite->status == 'pending' && ! $invite->expired() && $user->email == $invite->email) {
                $user->verified = true;
                $user->save();

                $invite->project->users()->syncWithoutDetaching([\Auth::user()->id => ['role' => $invite->role]]);

                $invite->status = 'successful';
                $invite->save();

                $invite->author->notify(new \App\Notifications\InviteAccepted($user, $invite));
            } else {
                \Session::flash('message', ['type' => 'warning', 'text' => 'There was a problem with the invitation.']);
            }
        } else {

            UserVerification::generate($user);
//            UserVerification::send($user, 'Verify account');
            $user->notify(new \App\Notifications\UserRegistered($user));
            event(new \Jrean\UserVerification\Events\VerificationEmailSent($user));
        }

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    public function resend_verification(Request $request)
    {
        $user = $request->user();

        if ($user->verified) {
            return redirect('/');
        }

        $user->notify(new \App\Notifications\UserRegistered($user));

        return redirect('/')->with('message', ['type' => 'success', 'text' => 'Verification email sent.']);
    }

    public function getVerification (Request $request, $token)
    {
        if(!empty($token)){

            User::where('verification_token', $token)->update(['verified'=>1]);

            $user = User::where('verification_token', $token)->get();
            if(count($user) > 0){

                $user = $user[0];
                $user->notify(new \App\Notifications\Congratulation($user));
                event(new \Jrean\UserVerification\Events\VerificationEmailSent($user));

                return view('user.confirmation');

//                return redirect('/');
            }
        }
    }
}
