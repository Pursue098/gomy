<?php

namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
use Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Channel;
use App\User;
use App\Invite;

class ProjectController extends Controller
{

    public function index(Request $request)
    {


        $projects = $request->user()->projects()->with('users', 'channels.channable')->get();
        return view('projects.index', ['projects' => $projects]);
    }

    public function show(Project $project)
    {
        $projects = collect([$project->load('channels.channable')]);

        return view('projects.index', ['projects' => $projects]);
    }

    public function get_create(Request $request)
    {
        $teammates = $request->user()->teammates();

        return view('projects._modal_create', ['teammates' => $teammates]);
    }

    public function post_create(Request $request)
    {
        $this->validate($request, [
            'new-project'     => 'required|max:191|regex:/^[-\' \p{L}\d]+$/u'
        ]);

        $project = Project::create([
            'name' => $request->get('new-project'),
        ]);

        $request->user()->projects()->attach($project, ['role' => 'owner']);

        return back();
//        return back()->with('message', ['type' => 'success', 'text' => 'Project ' . $project->name . ' created.']);
    }

    public function get_roles(Request $request, Project $project) {
        $users = $project->users;

        return view('projects._modal_roles', [
            'project'   => $project,
            'teammates' => $users,
        ]);
    }

    public function post_roles(Request $request, Project $project) {

        $this->validate($request, [
            'mod_role.*'      => 'required|in:admin,user,delete',
        ]);

        $roles = $request->get('mod_role');

        $attach = [];

        foreach($project->users as $user) {

            if (! isset($roles[$user->id])) {
                $attach[$user->id] = ['role' => $user->pivot->role];
            } elseif (isset($roles[$user->id]) && $roles[$user->id] != 'delete') {
                $attach[$user->id] = ['role' => $roles[$user->id]];
            }
        }

        $project->users()->sync($attach);

        return back()->with('message', ['type' => 'success', 'text' => 'Roles of project ' . $project->name . ' updated.']);
    }

    public function get_invite(Request $request, Project $project) {
        $teammates = $request->user()->teammates($project);

        return view('projects._modal_invite', [
            'project'   => $project,
            'teammates' => $teammates,
        ]);
    }

    public function get_invitations(Request $request, Project $project) {

        $project = $project->load('invites');

        return view('projects._modal_invitations', [
            'project' => $project
        ]);
    }

    public function delete_invite(Request $request, Project $project, $code) {
        $invite = Invite::where('code', $code)->first();

        if (! $invite) {
            return back()->with('message', ['type' => 'danger', 'text' => 'Invite not found.']);
        }

        $invite->status = 'canceled';
        $invite->save();

        return back()->with('message', ['type' => 'success', 'text' => 'Invitation removed.']);
    }

    public function post_invite(Request $request, Project $project) {

        $people = [];

        foreach($request->get('invitations') as $email => $role) {
            $people[] = [
                'email' => $email,
                'role'  => $role,
            ];
        }

        $validator = \Validator::make($people, [
            '*.email' => 'required|email',
            '*.role'  => 'required|in:user,admin'
        ])->validate();

        $attach = [];

        foreach($people as $person) {
            $user = User::where('email', $person['email'])->first();

            if (! $user) {
                $invite = Invite::create([
                    'project_id' => $project->id,
                    'user_id'    => $request->user()->id,
                    'email'      => $person['email'],
                    'role'       => $person['role'],
                    'status'     => 'pending',
                    'valid_till' => \Carbon\Carbon::now()->addHour(48),
                    'code'       => str_random(32),
                ]);

                $invite->notify(new \App\Notifications\UserInvited($request->user(), $invite, $project));
                //return $person;
            } else {
                $user->notify(new \App\Notifications\UserInvited($request->user(), $user, $project));

                $attach[$user->id] = ['role' => $person['role']];
            }
        }

        if (count($attach) > 0) {
            $project->users()->syncWithoutDetaching($attach);
        }

        return back()->with('message', ['type' => 'success', 'text' => count($people) . ' people invited to ' . $project->name . '.']);
    }

    public function add_channel(Request $request, Project $project, $type)
    {

          if (!collect(Channel::$supported)->contains('name', $type)) {
                return back()->with('message', ['type' => 'danger', 'text' => 'Channel ' . $type . ' not supported yet.']);
            }


            $haveAddedChannelTypeName = $type;

            $haveCurrentProjectInfo = Project::find($project->id);
            $haveProjectAndItsChannelInformaion =  $haveCurrentProjectInfo->channels;

            foreach($haveProjectAndItsChannelInformaion as $haveProjectAndItsChannelInformaion) {
                if($haveProjectAndItsChannelInformaion['type'] == $haveAddedChannelTypeName  )
                {
                    \Session::flash('warning-message', $haveAddedChannelTypeName. ' Channel is already added in ' . $haveCurrentProjectInfo['name'] . ' project . You can not add it twice');
                    return back();
                }
            }

            if ($type == 'Poynt') {
                $type = 'poynt';
            }
            $name = $request->get('name');
            $project->channels()->create([

                'name' => $name,
                'type' => $type,
            ]);

            return back()->with('message', ['type' => 'success', 'text' => 'Channel ' . $type . ' added to project ' . $project->name . '.']); 
    }
}
