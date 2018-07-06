<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;
use App\Permission;
use Session;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $permissions = Permission::paginate(10);
        $user = Auth::user();
        $projects = $user->projects;

        $params = [
            'title' => 'Permissions Listing',
            'permissions' => $permissions,
            'projects' => $projects,
        ];

        return view('admin.permission.perm_list')->with($params);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $params = [
            'title' => 'Create Permission',
        ];

        return view('admin.permission.perm_create')->with($params);
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
        $this->validate($request, [
            'name' => 'required|unique:permissions',
            'display_name' => 'required',
            'description' => 'required',
        ]);

        $permission = Permission::create([
            'name' => $request->input('name'),
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('permission.index');
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
        try {
            $permission = Permission::findOrFail($id);
            $user = Auth::user();
            $projects = $user->projects;

            $params = [
                'title' => 'Edit Permission',
                'permission' => $permission,
                'projects' => $projects,
            ];

            //dd($role_permissions);

            return view('admin.permission.perm_edit')->with($params);
        } catch (ModelNotFoundException $ex) {
            if ($ex instanceof ModelNotFoundException) {
                return response()->view('errors.' . '404');
            }
        }
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
        try {
            $permission = Permission::findOrFail($id);

            $this->validate($request, [
                'display_name' => 'required',
                'description' => 'required',
            ]);

            $permission->name = $request->input('name');
            $permission->display_name = $request->input('display_name');
            $permission->description = $request->input('description');

            $permission->save();
            \Session::flash('success', 'Information Updated Successfully');
            return redirect()->route('permission.index');
        } catch (ModelNotFoundException $ex) {
            if ($ex instanceof ModelNotFoundException) {
                return response()->view('errors.' . '404');
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            DB::table("permission_role")->where('permission_id', $id)->delete();
            $permission->delete();
            \Session::flash('permission-deleted', 'Permission Deleted Successfully');
            return redirect()->route('permission.index');
        } catch (ModelNotFoundException $ex) {
            if ($ex instanceof ModelNotFoundException) {
                return response()->view('errors.' . '404');
            }
        }
    }
}
