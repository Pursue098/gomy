<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Jrean\UserVerification\Traits\VerifiesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Subscriptions;
use App\Payment;
use App\User;
use App\Role;
use App\Permission;
use Validator, DB, Hash, Mail, Input, Session;

class UsersController extends Controller
{

    use RegistersUsers, VerifiesUsers;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects;

        $users = User::paginate(10);
        $params = [
            'title' => 'Users Listing',
            'users' => $users,
            'projects' => $projects,
        ];
        return view('admin.users.users_list')->with($params);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function plan_subscribed()
    {
        $user = Auth::user(); 
        $projects = $user->projects;
         
        $data = array();
        $users = User::all();
        
        $usersList = User::pluck('name', 'id');
        $subscriptions_enterprize = Payment::where('gateway', 'Enterprise')->paginate(10);
        $subscriptions_general = Payment::where('gateway', 'Stripe')->paginate(10);
        
        
        // echo '<pre>';  var_dump($users); exit;
        $params = [
            'title' => 'Users with Subscription Plans',
            'userslist' => $usersList,
            'users' => $users,
            'projects' => $projects,
            'subscriptions_enterprize' => $subscriptions_enterprize,
            'subscriptions_general' => $subscriptions_general
            
        ];
        
        return view('planSubscribed.users_list')->with($params);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $roles = Role::all();
        $projects = $user->projects;
        $params = [
            'title' => 'Create User',
            'roles' => $roles,
            'projects' => $projects
        ];

        return view('admin.users.users_create')->with($params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        $role = Role::find($request->input('role_id'));

        $user->attachRole($role);

        return redirect()->route('user.index');
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

        $user = User::findOrFail($id);

        $params = [
            'title' => 'Confirm Delete Record',
            'user' => $user,
            'projects' => $projects,
        ];

        return view('admin.users.users_delete')->with($params);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit( $user)
    {
        $loggedin_user = Auth::user();
        $projects = $loggedin_user->projects;

        $user = User::findOrFail($user->id);
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        $params = [
            'title' => 'Edit User',
            'projects' => $projects,
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
        return view('admin.users.users_edit')->with($params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $user = User::findOrFail($user->id);

        $this->validate($request, [
            'name' => 'required',
        ]); 
        
        $role = Role::find($request->input('role_id')); 
        
        if(empty( $user->business_id) && $role->name=='Merchant'){
            \Session::flash('error', 'User do not have business or device id');
            return redirect()->route('user.index');
        }
        
        $user->name = $request->input('name');

        $user->save();

        // Update role of the user
        $roles = $user->roles;

        foreach ($roles as $key => $value) {
            $user->detachRole($value);
        }

        $user->attachRole($role);

        // Update permission of the user
        //$permission = Permission::find($request->input('permission_id'));
        //$user->attachPermission($permission);
        \Session::flash('success', 'Information Updated Successfully');
        return redirect()->route('user.index');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user)
    {
        $user = User::findOrFail($user->id);

        // Detach from Role
        $roles = $user->roles;

        foreach ($roles as $key => $value) {
            $user->detachRole($value);
        }

        //$user->delete();
        \Session::flash('user-deleted', 'User detach with all roles');
        return redirect()->route('user.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getUserProfile($user)
    {
        $user = User::find($user->id);
        return view('admin.users.profile', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateUserProfile(Request $request, $user)
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

        $user= User::find($user->id);
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->company = $request->company;
        $user->save();

        $user->notify(new \App\Notifications\ProfileUpdate($user));
        \Session::flash('successMessage', 'Your profile has been updated successfully !');
        return redirect()->route('projects.index');
    }
}
