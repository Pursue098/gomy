@extends('layouts.app')

@section('htmlheader_title')
    Users
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>
            <a href="/">Home</a>
        </li>
        <li>
            <a href="#">Admin</a>
        </li>
        <li class="active">
            <strong>Users</strong>
        </li>
    </ol>
@endsection

@section('main-content')
    @if(Session::has('message'))
        <?php $message = Session::get('message'); ?>
        <div class="alert alert-{{ $message['type'] }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ $message['text'] }}
        </div>
    @endif

    <div class="ibox">
        <div class="ibox-title">
            <h5>All users</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:30px;"></th>
                        <th>User</th>
                        <th>E-mail</th>
                        <th class="text-center">Verified</th>
                        <th>Projects</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><img class="img-circle" style="border: 1px solid #d2d2d2; width: 30px;" src="{{ $user->picture }}" /></td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">{{ $user->verified }}</td>
                            <td>
                                @foreach($user->projects->sortBy(function($project, $key) {
                                        $return = ['owner' => 0, 'admin' => 1, 'user' => 2];
                                        return $return[$project->pivot->role];
                                    }) as $project)
                                    <a href="{{ route('project.dashboard', [$project->getRouteKey()]) }}"><span title="{{ $project->pivot->role }}" class="label label-{{ $project->pivot->role }}">{{ $project->name }}</span></a>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection